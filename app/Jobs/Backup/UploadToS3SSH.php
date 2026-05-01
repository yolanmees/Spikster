<?php

namespace App\Jobs\Backup;

use App\Models\Backup;
use App\Models\BackupStorageLocation;
use App\Services\RemoteDaemonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Upload a backup to an S3 (or S3-compatible) bucket.
 *
 * Design choice: upload is delegated to the remote daemon (backup.upload-s3 action).
 * The backup file already lives on the remote server; having the daemon invoke the
 * AWS CLI directly avoids a double-transfer (remote → panel → S3) and keeps
 * credentials off the panel process entirely.
 */
class UploadToS3SSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public $tries = 2;

    protected Backup $backup;

    protected ?BackupStorageLocation $storageLocation;

    public function __construct(Backup $backup)
    {
        $this->backup = $backup;

        $this->storageLocation = BackupStorageLocation::where('name', $backup->storage_location)
            ->where('type', 's3')
            ->where('is_active', true)
            ->first();
    }

    public function handle(RemoteDaemonService $daemon): void
    {
        if (! $this->storageLocation) {
            throw new \Exception("No active S3 storage location found for '{$this->backup->storage_location}'");
        }

        try {
            Log::info('Starting S3 upload via daemon', [
                'backup_id' => $this->backup->id,
                'storage_location' => $this->storageLocation->name,
            ]);

            $credentials = $this->storageLocation->getCredentials();
            $path = trim($credentials['path'] ?? 'backups', '/');
            $s3Key = "{$path}/{$this->backup->site->domain}/{$this->backup->filename}";

            $result = $daemon->send($this->backup->site->server, 'backup.upload-s3', [
                'backup_id' => $this->backup->id,
                'filepath' => $this->backup->filepath,
                'bucket' => $credentials['bucket'],
                'region' => $credentials['region'] ?? 'us-east-1',
                'access_key' => $credentials['access_key'],
                'secret_key' => $credentials['secret_key'],
                'endpoint' => $credentials['endpoint'] ?? null,
                'storage_class' => $credentials['storage_class'] ?? null,
                's3_key' => $s3Key,
                'delete_local' => config('backup.delete_local_after_remote_upload', false),
            ]);

            if (empty($result['success'])) {
                throw new \Exception($result['error'] ?? 'Daemon returned failure for backup.upload-s3');
            }

            $metadata = $this->backup->metadata ?? [];
            $metadata['s3_upload'] = [
                'bucket' => $credentials['bucket'],
                'key' => $s3Key,
                'region' => $credentials['region'] ?? 'us-east-1',
                'uploaded_at' => now()->toIso8601String(),
            ];
            $this->backup->metadata = $metadata;
            $this->backup->save();

            Log::info('Backup uploaded to S3 successfully', [
                'backup_id' => $this->backup->id,
                's3_key' => $s3Key,
            ]);

        } catch (\Exception $e) {
            Log::error('S3 upload failed', [
                'backup_id' => $this->backup->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('UploadToS3SSH job failed', [
            'backup_id' => $this->backup->id,
            'storage_location' => $this->storageLocation?->name,
            'error' => $exception->getMessage(),
        ]);
    }
}

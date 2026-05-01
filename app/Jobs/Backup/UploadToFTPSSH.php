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
 * Upload a backup to an FTP/SFTP destination.
 *
 * Design choice: the upload is delegated to the remote daemon (backup.upload-ftp action)
 * rather than streaming the file through the PHP panel. The backup file already lives on
 * the remote server, so letting the daemon push it directly avoids a costly double-transfer
 * (remote → panel → FTP host). The daemon handles sshpass/lftp installation on its end.
 */
class UploadToFTPSSH implements ShouldQueue
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
            ->whereIn('type', ['ftp', 'sftp'])
            ->where('is_active', true)
            ->first();
    }

    public function handle(RemoteDaemonService $daemon): void
    {
        if (! $this->storageLocation) {
            throw new \Exception("No active FTP/SFTP storage location found for '{$this->backup->storage_location}'");
        }

        try {
            Log::info('Starting FTP/SFTP upload via daemon', [
                'backup_id' => $this->backup->id,
                'storage_location' => $this->storageLocation->name,
            ]);

            $credentials = $this->storageLocation->getCredentials();

            $result = $daemon->send($this->backup->site->server, 'backup.upload-ftp', [
                'backup_id' => $this->backup->id,
                'filepath' => $this->backup->filepath,
                'filename' => $this->backup->filename,
                'domain' => $this->backup->site->domain,
                'type' => $this->storageLocation->type,
                'host' => $credentials['host'],
                'port' => $credentials['port'] ?? ($this->storageLocation->type === 'sftp' ? 22 : 21),
                'username' => $credentials['username'],
                'password' => $credentials['password'],
                'remote_path' => rtrim($credentials['path'] ?? '/backups', '/')
                                  .'/'.$this->backup->site->domain,
                'passive' => $credentials['passive'] ?? true,
                'delete_local' => config('backup.delete_local_after_remote_upload', false),
            ]);

            if (empty($result['success'])) {
                throw new \Exception($result['error'] ?? 'Daemon returned failure for backup.upload-ftp');
            }

            $metadata = $this->backup->metadata ?? [];
            $metadata['remote_upload'] = [
                'type' => $this->storageLocation->type,
                'host' => $credentials['host'],
                'path' => $result['remote_path'] ?? null,
                'uploaded_at' => now()->toIso8601String(),
            ];
            $this->backup->metadata = $metadata;
            $this->backup->save();

            Log::info("Backup uploaded successfully via {$this->storageLocation->type}", [
                'backup_id' => $this->backup->id,
            ]);

        } catch (\Exception $e) {
            Log::error('FTP/SFTP upload failed', [
                'backup_id' => $this->backup->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('UploadToFTPSSH job failed', [
            'backup_id' => $this->backup->id,
            'storage_location' => $this->storageLocation?->name,
            'error' => $exception->getMessage(),
        ]);
    }
}

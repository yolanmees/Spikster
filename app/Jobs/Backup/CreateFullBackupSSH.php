<?php

namespace App\Jobs\Backup;

use App\Models\Backup;
use App\Models\Site;
use App\Services\RemoteDaemonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateFullBackupSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public $tries = 1;

    protected Backup $backup;

    protected Site $site;

    public function __construct(Backup $backup)
    {
        $this->backup = $backup;
        $this->site = $backup->site;
    }

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            $this->backup->markAsStarted();

            $server = $this->site->server;

            $result = $daemon->send($server, 'backup.create', [
                'type' => 'full',
                'domain' => $this->site->domain,
                'site_root' => $this->site->path ?? "/var/www/{$this->site->domain}",
                'includes_files' => $this->backup->includes_files,
                'includes_database' => $this->backup->includes_database,
                'includes_email' => $this->backup->includes_email,
                'database_name' => $this->site->database_name,
                'database_user' => $this->site->database_user,
                'database_password' => $this->site->database_password,
                'backup_id' => $this->backup->id,
            ]);

            if (empty($result['success'])) {
                throw new \Exception($result['error'] ?? 'Daemon returned failure for backup.create');
            }

            $this->backup->filename = $result['filename'];
            $this->backup->filepath = $result['filepath'];
            $this->backup->size = $result['size'] ?? 0;
            $this->backup->compressed_size = $result['compressed_size'] ?? 0;
            $this->backup->compression_ratio = $result['compression_ratio'] ?? null;
            $this->backup->checksum = $result['checksum'] ?? null;
            $this->backup->markAsCompleted();

            Log::info("Full backup completed for site {$this->site->domain}", [
                'backup_id' => $this->backup->id,
            ]);

            if ($this->backup->is_encrypted) {
                EncryptBackupSSH::dispatch($this->backup);
            }

            if ($this->backup->storage_location !== 'local') {
                $this->uploadToRemoteStorage();
            }

        } catch (\Exception $e) {
            Log::error("Full backup failed for site {$this->site->domain}", [
                'backup_id' => $this->backup->id,
                'error' => $e->getMessage(),
            ]);
            $this->backup->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    protected function uploadToRemoteStorage(): void
    {
        match ($this->backup->storage_location) {
            's3' => UploadToS3SSH::dispatch($this->backup),
            'ftp', 'sftp' => UploadToFTPSSH::dispatch($this->backup),
            default => null,
        };
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('CreateFullBackupSSH job failed', [
            'backup_id' => $this->backup->id,
            'error' => $exception->getMessage(),
        ]);
        $this->backup->markAsFailed($exception->getMessage());
    }
}

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

class RestoreBackupSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    protected Backup $backup;
    protected Site $site;
    protected array $options;

    public function __construct(Backup $backup, array $options = [])
    {
        $this->backup  = $backup;
        $this->site    = $backup->site;
        $this->options = $options;
    }

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            Log::info("Starting backup restore via daemon", [
                'backup_id'   => $this->backup->id,
                'backup_type' => $this->backup->type,
            ]);

            $server = $this->site->server;

            $params = [
                'backup_id'        => $this->backup->id,
                'filepath'         => $this->backup->filepath,
                'checksum'         => $this->backup->checksum,
                'is_encrypted'     => $this->backup->is_encrypted,
                'passphrase'       => $this->backup->is_encrypted
                    ? config('app.backup_encryption_key', 'spikster-backup-encryption-key')
                    : null,
                'type'             => $this->backup->type,
                'base_backup_id'   => $this->backup->metadata['base_backup_id'] ?? null,
                'domain'           => $this->site->domain,
                'site_root'        => $this->site->path ?? "/var/www/{$this->site->domain}",
                'database_name'    => $this->site->database_name,
                'database_user'    => $this->site->database_user,
                'database_password'=> $this->site->database_password,
                'restore_files'    => $this->options['restore_files'] ?? true,
                'restore_database' => $this->options['restore_database'] ?? true,
                'restore_email'    => $this->options['restore_email'] ?? false,
            ];

            $result = $daemon->send($server, 'backup.restore', $params);

            if (empty($result['success'])) {
                throw new \Exception($result['error'] ?? 'Daemon returned failure for backup.restore');
            }

            Log::info("Backup restore completed for site {$this->site->domain}", [
                'backup_id' => $this->backup->id,
            ]);

        } catch (\Exception $e) {
            Log::error("Backup restore failed for site {$this->site->domain}", [
                'backup_id' => $this->backup->id,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("RestoreBackupSSH job failed", [
            'backup_id' => $this->backup->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}

<?php

namespace App\Jobs\Backup;

use App\Models\Backup;
use App\Services\RemoteDaemonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EncryptBackupSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800;
    public $tries = 2;

    protected Backup $backup;

    public function __construct(Backup $backup)
    {
        $this->backup = $backup;
    }

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            Log::info("Starting backup encryption via daemon", ['backup_id' => $this->backup->id]);

            $server = $this->backup->site->server;

            $result = $daemon->send($server, 'backup.encrypt', [
                'filepath'   => $this->backup->filepath,
                'passphrase' => config('app.backup_encryption_key', 'spikster-backup-encryption-key'),
                'backup_id'  => $this->backup->id,
            ]);

            if (empty($result['success'])) {
                throw new \Exception($result['error'] ?? 'Daemon returned failure for backup.encrypt');
            }

            $this->backup->filepath          = $result['encrypted_path'];
            $this->backup->filename          = basename($result['encrypted_path']);
            $this->backup->compressed_size   = $result['encrypted_size'] ?? $this->backup->compressed_size;
            $this->backup->encryption_method = 'gpg-aes256';
            $this->backup->save();

            Log::info("Backup encrypted successfully via daemon", ['backup_id' => $this->backup->id]);

        } catch (\Exception $e) {
            Log::error("Backup encryption failed", [
                'backup_id' => $this->backup->id,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("EncryptBackupSSH job failed", [
            'backup_id' => $this->backup->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}

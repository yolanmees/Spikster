<?php

namespace App\Jobs\Backup;

use App\Models\Backup;
use App\Services\SSHService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EncryptBackupSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // 30 minutes
    public $tries = 2;

    protected Backup $backup;

    /**
     * Create a new job instance.
     */
    public function __construct(Backup $backup)
    {
        $this->backup = $backup;
    }

    /**
     * Execute the job.
     */
    public function handle(SSHService $ssh): void
    {
        try {
            Log::info("Starting backup encryption", [
                'backup_id' => $this->backup->id,
            ]);

            $server = $this->backup->site->server;
            $ssh->connect($server);

            // Check if GPG is installed
            $gpgInstalled = $ssh->execute("which gpg > /dev/null 2>&1 && echo 'yes' || echo 'no'");
            if (trim($gpgInstalled) !== 'yes') {
                Log::info("Installing GPG");
                $ssh->execute("apt-get update && apt-get install -y gnupg");
            }

            $backupPath = $this->backup->filepath;
            $encryptedPath = "{$backupPath}.gpg";

            // Generate encryption passphrase (should be stored securely)
            // For now, using a simple approach - in production, use Laravel's encryption
            $passphrase = config('app.backup_encryption_key', 'spikster-backup-encryption-key');

            // Encrypt the backup using GPG
            $encryptCmd = "gpg --batch --yes --passphrase '{$passphrase}' " .
                         "--symmetric --cipher-algo AES256 " .
                         "--output {$encryptedPath} {$backupPath}";

            $ssh->execute($encryptCmd);

            // Verify encrypted file was created
            $encryptedExists = $ssh->execute("[ -f {$encryptedPath} ] && echo 'yes' || echo 'no'");
            if (trim($encryptedExists) !== 'yes') {
                throw new \Exception("Encrypted backup file was not created");
            }

            // Get encrypted file size
            $encryptedSize = $ssh->execute("stat -f%z {$encryptedPath} 2>/dev/null || stat -c%s {$encryptedPath}");
            $encryptedSize = intval(trim($encryptedSize));

            // Remove original unencrypted backup
            $ssh->execute("rm -f {$backupPath}");

            // Update backup record
            $this->backup->filepath = $encryptedPath;
            $this->backup->filename = basename($encryptedPath);
            $this->backup->compressed_size = $encryptedSize;
            $this->backup->encryption_method = 'gpg-aes256';
            $this->backup->save();

            Log::info("Backup encrypted successfully", [
                'backup_id' => $this->backup->id,
                'encrypted_size' => $encryptedSize,
            ]);

        } catch (\Exception $e) {
            Log::error("Backup encryption failed", [
                'backup_id' => $this->backup->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("EncryptBackupSSH job failed", [
            'backup_id' => $this->backup->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

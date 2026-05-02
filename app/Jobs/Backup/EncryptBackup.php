<?php

namespace App\Jobs\Backup;

use App\Models\Backup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EncryptBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        protected Backup $backup,
        protected string $encryptionKey
    ) {}

    public function handle(): void
    {
        if (! $this->backup->filepath || ! file_exists($this->backup->filepath)) {
            throw new \RuntimeException("Backup file not found: {$this->backup->filepath}");
        }

        $encryptedPath = $this->backup->filepath . '.enc';

        // Encrypt using OpenSSL AES-256-CBC
        $command = sprintf(
            'openssl enc -aes-256-cbc -salt -pbkdf2 -in %s -out %s -pass pass:%s',
            escapeshellarg($this->backup->filepath),
            escapeshellarg($encryptedPath),
            escapeshellarg($this->encryptionKey)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException('Backup encryption failed');
        }

        unlink($this->backup->filepath);

        $this->backup->update([
            'filepath' => $encryptedPath,
            'filename' => $this->backup->filename . '.enc',
            'metadata' => array_merge($this->backup->metadata ?? [], [
                'encrypted' => true,
                'encryption_method' => 'aes-256-cbc-pbkdf2',
            ]),
        ]);
    }
}

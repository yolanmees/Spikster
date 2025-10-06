<?php

namespace App\Jobs\Backup;

use App\Models\Backup;
use App\Models\BackupStorageLocation;
use App\Services\SSHService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadToFTPSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour
    public $tries = 2;

    protected Backup $backup;
    protected BackupStorageLocation $storageLocation;

    /**
     * Create a new job instance.
     */
    public function __construct(Backup $backup)
    {
        $this->backup = $backup;

        // Find FTP/SFTP storage location
        $this->storageLocation = BackupStorageLocation::where('name', $backup->storage_location)
            ->whereIn('type', ['ftp', 'sftp'])
            ->where('is_active', true)
            ->firstOrFail();
    }

    /**
     * Execute the job.
     */
    public function handle(SSHService $ssh): void
    {
        try {
            Log::info("Starting FTP/SFTP upload for backup", [
                'backup_id' => $this->backup->id,
                'storage_location' => $this->storageLocation->name,
                'type' => $this->storageLocation->type,
            ]);

            $server = $this->backup->site->server;
            $ssh->connect($server);

            // Get credentials
            $credentials = $this->storageLocation->getCredentials();

            $host = $credentials['host'];
            $port = $credentials['port'] ?? ($this->storageLocation->type === 'sftp' ? 22 : 21);
            $username = $credentials['username'];
            $password = $credentials['password'];
            $path = $credentials['path'] ?? '/backups';
            $passive = $credentials['passive'] ?? true;

            // Build remote path
            $remotePath = rtrim($path, '/') . '/' . $this->backup->site->domain;
            $remoteFile = "{$remotePath}/{$this->backup->filename}";

            if ($this->storageLocation->type === 'sftp') {
                $this->uploadViaSFTP($ssh, $host, $port, $username, $password, $remotePath, $remoteFile);
            } else {
                $this->uploadViaFTP($ssh, $host, $port, $username, $password, $remotePath, $remoteFile, $passive);
            }

            // Update backup metadata
            $metadata = $this->backup->metadata ?? [];
            $metadata['remote_upload'] = [
                'type' => $this->storageLocation->type,
                'host' => $host,
                'path' => $remoteFile,
                'uploaded_at' => now()->toIso8601String(),
            ];
            $this->backup->metadata = $metadata;
            $this->backup->save();

            // Optionally delete local backup
            if (config('backup.delete_local_after_remote_upload', false)) {
                Log::info("Deleting local backup after successful upload");
                $ssh->execute("rm -f {$this->backup->filepath}");
            }

            Log::info("Backup uploaded successfully via {$this->storageLocation->type}", [
                'backup_id' => $this->backup->id,
                'remote_path' => $remoteFile,
            ]);

        } catch (\Exception $e) {
            Log::error("FTP/SFTP upload failed", [
                'backup_id' => $this->backup->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Upload via SFTP
     */
    protected function uploadViaSFTP(
        SSHService $ssh,
        string $host,
        int $port,
        string $username,
        string $password,
        string $remotePath,
        string $remoteFile
    ): void {
        // Check if sshpass is installed (needed for password-based SFTP)
        $sshpassInstalled = $ssh->execute("which sshpass > /dev/null 2>&1 && echo 'yes' || echo 'no'");

        if (trim($sshpassInstalled) !== 'yes') {
            Log::info("Installing sshpass");
            $ssh->execute("apt-get update && apt-get install -y sshpass");
        }

        // Create remote directory
        $mkdirCmd = "sshpass -p '{$password}' sftp -P {$port} " .
                   "-o StrictHostKeyChecking=no " .
                   "{$username}@{$host} <<EOF
mkdir -p {$remotePath}
bye
EOF";

        $ssh->execute($mkdirCmd);

        // Upload file via SFTP
        $uploadCmd = "sshpass -p '{$password}' sftp -P {$port} " .
                    "-o StrictHostKeyChecking=no " .
                    "{$username}@{$host} <<EOF
cd {$remotePath}
put {$this->backup->filepath}
bye
EOF";

        $output = $ssh->execute($uploadCmd);

        Log::info("SFTP upload completed", [
            'host' => $host,
            'remote_path' => $remoteFile,
        ]);
    }

    /**
     * Upload via FTP
     */
    protected function uploadViaFTP(
        SSHService $ssh,
        string $host,
        int $port,
        string $username,
        string $password,
        string $remotePath,
        string $remoteFile,
        bool $passive
    ): void {
        // Check if lftp is installed (best FTP client for scripting)
        $lftpInstalled = $ssh->execute("which lftp > /dev/null 2>&1 && echo 'yes' || echo 'no'");

        if (trim($lftpInstalled) !== 'yes') {
            Log::info("Installing lftp");
            $ssh->execute("apt-get update && apt-get install -y lftp");
        }

        // Build lftp script
        $passiveMode = $passive ? 'on' : 'off';

        $lftpScript = "set ftp:passive-mode {$passiveMode}\n" .
                     "open -u {$username},{$password} -p {$port} {$host}\n" .
                     "mkdir -p {$remotePath}\n" .
                     "cd {$remotePath}\n" .
                     "put {$this->backup->filepath}\n" .
                     "bye";

        // Create temporary lftp script file
        $scriptPath = "/tmp/lftp_upload_" . uniqid() . ".txt";
        $ssh->execute("echo '{$lftpScript}' > {$scriptPath}");

        // Execute lftp
        $uploadCmd = "lftp -f {$scriptPath}";
        $output = $ssh->execute($uploadCmd);

        // Clean up script file
        $ssh->execute("rm -f {$scriptPath}");

        Log::info("FTP upload completed", [
            'host' => $host,
            'remote_path' => $remoteFile,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("UploadToFTPSSH job failed", [
            'backup_id' => $this->backup->id,
            'storage_location' => $this->storageLocation->name,
            'error' => $exception->getMessage(),
        ]);
    }
}

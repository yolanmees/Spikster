<?php

namespace App\Jobs\Backup;

use App\Models\Backup;
use App\Models\Site;
use App\Services\SSHService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CreateFullBackupSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 1; // Don't retry failed backups automatically

    protected Backup $backup;
    protected Site $site;

    /**
     * Create a new job instance.
     */
    public function __construct(Backup $backup)
    {
        $this->backup = $backup;
        $this->site = $backup->site;
    }

    /**
     * Execute the job.
     */
    public function handle(SSHService $ssh): void
    {
        try {
            // Mark backup as started
            $this->backup->markAsStarted();

            $server = $this->site->server;
            $timestamp = Carbon::now()->format('Y-m-d_His');
            $backupName = "backup_{$this->site->domain}_{$timestamp}";
            $backupDir = "/var/spikster/backups/{$this->site->domain}";
            $backupPath = "{$backupDir}/{$backupName}";

            // Connect to server
            $ssh->connect($server);

            // Create backup directory structure
            $ssh->execute("mkdir -p {$backupDir}");
            $ssh->execute("mkdir -p {$backupPath}");

            // Backup files if included
            if ($this->backup->includes_files) {
                Log::info("Backing up files for site {$this->site->domain}");

                $siteRoot = $this->site->path ?? "/var/www/{$this->site->domain}";
                $filesBackup = "{$backupPath}/files.tar.gz";

                $excludePatterns = [
                    '--exclude=node_modules',
                    '--exclude=.git',
                    '--exclude=vendor',
                    '--exclude=storage/logs/*.log',
                    '--exclude=storage/framework/cache/*',
                    '--exclude=storage/framework/sessions/*',
                    '--exclude=storage/framework/views/*',
                ];

                $excludeArgs = implode(' ', $excludePatterns);
                $ssh->execute("tar -czf {$filesBackup} {$excludeArgs} -C {$siteRoot} .");

                // Get file size
                $fileSize = $ssh->execute("stat -f%z {$filesBackup} 2>/dev/null || stat -c%s {$filesBackup}");
                $this->backup->size = intval(trim($fileSize));
            }

            // Backup database if included
            if ($this->backup->includes_database) {
                Log::info("Backing up database for site {$this->site->domain}");

                $dbBackup = "{$backupPath}/database.sql.gz";
                $dbName = $this->site->database_name ?? str_replace(['.', '-'], '_', $this->site->domain);
                $dbUser = $this->site->database_user ?? 'root';
                $dbPass = $this->site->database_password ?? '';

                // Create mysqldump command
                $dumpCmd = "mysqldump -u{$dbUser}";
                if ($dbPass) {
                    $dumpCmd .= " -p'{$dbPass}'";
                }
                $dumpCmd .= " {$dbName} | gzip > {$dbBackup}";

                $ssh->execute($dumpCmd);

                // Add database size to total
                $dbSize = $ssh->execute("stat -f%z {$dbBackup} 2>/dev/null || stat -c%s {$dbBackup}");
                $this->backup->size += intval(trim($dbSize));
            }

            // Backup email if included
            if ($this->backup->includes_email) {
                Log::info("Backing up email for site {$this->site->domain}");

                $emailBackup = "{$backupPath}/email.tar.gz";
                $emailPath = "/var/vmail/{$this->site->domain}";

                // Check if email directory exists
                $emailExists = $ssh->execute("[ -d {$emailPath} ] && echo 'yes' || echo 'no'");

                if (trim($emailExists) === 'yes') {
                    $ssh->execute("tar -czf {$emailBackup} -C {$emailPath} .");

                    // Add email size to total
                    $emailSize = $ssh->execute("stat -f%z {$emailBackup} 2>/dev/null || stat -c%s {$emailBackup}");
                    $this->backup->size += intval(trim($emailSize));
                }
            }

            // Create backup metadata file
            $metadata = [
                'site_id' => $this->site->id,
                'domain' => $this->site->domain,
                'backup_id' => $this->backup->id,
                'type' => 'full',
                'timestamp' => $timestamp,
                'includes_files' => $this->backup->includes_files,
                'includes_database' => $this->backup->includes_database,
                'includes_email' => $this->backup->includes_email,
                'php_version' => $this->site->php_version ?? 'unknown',
                'created_at' => Carbon::now()->toIso8601String(),
            ];

            $metadataJson = json_encode($metadata, JSON_PRETTY_PRINT);
            $ssh->execute("echo '{$metadataJson}' > {$backupPath}/metadata.json");

            // Create final compressed archive
            $finalArchive = "{$backupDir}/{$backupName}.tar.gz";
            $ssh->execute("tar -czf {$finalArchive} -C {$backupDir} {$backupName}");

            // Get final archive size
            $compressedSize = $ssh->execute("stat -f%z {$finalArchive} 2>/dev/null || stat -c%s {$finalArchive}");
            $this->backup->compressed_size = intval(trim($compressedSize));

            // Calculate compression ratio
            if ($this->backup->size > 0) {
                $this->backup->compression_ratio = round(
                    ($this->backup->compressed_size / $this->backup->size) * 100,
                    2
                );
            }

            // Generate checksum
            $checksum = $ssh->execute("sha256sum {$finalArchive} | awk '{print $1}'");
            $this->backup->checksum = trim($checksum);

            // Clean up temporary files
            $ssh->execute("rm -rf {$backupPath}");

            // Update backup record
            $this->backup->filename = "{$backupName}.tar.gz";
            $this->backup->filepath = $finalArchive;
            $this->backup->markAsCompleted();

            Log::info("Full backup completed for site {$this->site->domain}", [
                'backup_id' => $this->backup->id,
                'size' => $this->backup->getFormattedSize(),
                'compressed_size' => $this->backup->compressed_size,
                'compression_ratio' => $this->backup->compression_ratio,
            ]);

            // If encryption is enabled, encrypt the backup
            if ($this->backup->is_encrypted) {
                EncryptBackupSSH::dispatch($this->backup);
            }

            // Upload to remote storage if needed
            if ($this->backup->storage_location !== 'local') {
                $this->uploadToRemoteStorage();
            }

        } catch (\Exception $e) {
            Log::error("Full backup failed for site {$this->site->domain}", [
                'backup_id' => $this->backup->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->backup->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Upload backup to remote storage
     */
    protected function uploadToRemoteStorage(): void
    {
        switch ($this->backup->storage_location) {
            case 's3':
                UploadToS3SSH::dispatch($this->backup);
                break;

            case 'ftp':
            case 'sftp':
                UploadToFTPSSH::dispatch($this->backup);
                break;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("CreateFullBackupSSH job failed", [
            'backup_id' => $this->backup->id,
            'site_id' => $this->site->id,
            'error' => $exception->getMessage(),
        ]);

        $this->backup->markAsFailed($exception->getMessage());
    }
}

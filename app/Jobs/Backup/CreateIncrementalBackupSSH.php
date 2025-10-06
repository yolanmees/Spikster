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

class CreateIncrementalBackupSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // 30 minutes timeout
    public $tries = 1;

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
            $backupName = "incremental_{$this->site->domain}_{$timestamp}";
            $backupDir = "/var/spikster/backups/{$this->site->domain}";
            $backupPath = "{$backupDir}/{$backupName}";

            // Get base backup info
            $baseBackupId = $this->backup->metadata['base_backup_id'] ?? null;
            if (!$baseBackupId) {
                throw new \Exception("No base backup found for incremental backup");
            }

            $baseBackup = Backup::find($baseBackupId);
            if (!$baseBackup) {
                throw new \Exception("Base backup not found: {$baseBackupId}");
            }

            // Connect to server
            $ssh->connect($server);

            // Create backup directory
            $ssh->execute("mkdir -p {$backupPath}");

            // Backup only changed files since base backup
            if ($this->backup->includes_files) {
                Log::info("Creating incremental file backup for site {$this->site->domain}");

                $siteRoot = $this->site->path ?? "/var/www/{$this->site->domain}";
                $filesBackup = "{$backupPath}/files.tar.gz";
                $baseDate = $baseBackup->created_at->format('Y-m-d H:i:s');

                // Find files modified since base backup
                $findCmd = "find {$siteRoot} -type f -newermt '{$baseDate}' " .
                           "-not -path '*/node_modules/*' " .
                           "-not -path '*/.git/*' " .
                           "-not -path '*/vendor/*'";

                $changedFiles = $ssh->execute("{$findCmd} | wc -l");
                $fileCount = intval(trim($changedFiles));

                Log::info("Found {$fileCount} changed files since base backup");

                // Create archive of changed files
                $ssh->execute("{$findCmd} | tar -czf {$filesBackup} -T -");

                // Get file size
                $fileSize = $ssh->execute("stat -f%z {$filesBackup} 2>/dev/null || stat -c%s {$filesBackup}");
                $this->backup->size = intval(trim($fileSize));

                // Store file count in metadata
                $metadata = $this->backup->metadata ?? [];
                $metadata['changed_files_count'] = $fileCount;
                $this->backup->metadata = $metadata;
            }

            // Always backup database (full dump, not incremental)
            if ($this->backup->includes_database) {
                Log::info("Backing up database for incremental backup");

                $dbBackup = "{$backupPath}/database.sql.gz";
                $dbName = $this->site->database_name ?? str_replace(['.', '-'], '_', $this->site->domain);
                $dbUser = $this->site->database_user ?? 'root';
                $dbPass = $this->site->database_password ?? '';

                $dumpCmd = "mysqldump -u{$dbUser}";
                if ($dbPass) {
                    $dumpCmd .= " -p'{$dbPass}'";
                }
                $dumpCmd .= " {$dbName} | gzip > {$dbBackup}";

                $ssh->execute($dumpCmd);

                $dbSize = $ssh->execute("stat -f%z {$dbBackup} 2>/dev/null || stat -c%s {$dbBackup}");
                $this->backup->size += intval(trim($dbSize));
            }

            // Create metadata file
            $metadata = [
                'site_id' => $this->site->id,
                'domain' => $this->site->domain,
                'backup_id' => $this->backup->id,
                'type' => 'incremental',
                'base_backup_id' => $baseBackupId,
                'base_backup_date' => $baseBackup->created_at->toIso8601String(),
                'timestamp' => $timestamp,
                'includes_files' => $this->backup->includes_files,
                'includes_database' => $this->backup->includes_database,
                'created_at' => Carbon::now()->toIso8601String(),
            ];

            $metadataJson = json_encode($metadata, JSON_PRETTY_PRINT);
            $ssh->execute("echo '{$metadataJson}' > {$backupPath}/metadata.json");

            // Create final archive
            $finalArchive = "{$backupDir}/{$backupName}.tar.gz";
            $ssh->execute("tar -czf {$finalArchive} -C {$backupDir} {$backupName}");

            $compressedSize = $ssh->execute("stat -f%z {$finalArchive} 2>/dev/null || stat -c%s {$finalArchive}");
            $this->backup->compressed_size = intval(trim($compressedSize));

            if ($this->backup->size > 0) {
                $this->backup->compression_ratio = round(
                    ($this->backup->compressed_size / $this->backup->size) * 100,
                    2
                );
            }

            // Generate checksum
            $checksum = $ssh->execute("sha256sum {$finalArchive} | awk '{print $1}'");
            $this->backup->checksum = trim($checksum);

            // Clean up
            $ssh->execute("rm -rf {$backupPath}");

            // Update backup record
            $this->backup->filename = "{$backupName}.tar.gz";
            $this->backup->filepath = $finalArchive;
            $this->backup->markAsCompleted();

            Log::info("Incremental backup completed for site {$this->site->domain}", [
                'backup_id' => $this->backup->id,
                'base_backup_id' => $baseBackupId,
                'size' => $this->backup->getFormattedSize(),
            ]);

            // Encryption and remote upload if needed
            if ($this->backup->is_encrypted) {
                EncryptBackupSSH::dispatch($this->backup);
            }

            if ($this->backup->storage_location !== 'local') {
                $this->uploadToRemoteStorage();
            }

        } catch (\Exception $e) {
            Log::error("Incremental backup failed for site {$this->site->domain}", [
                'backup_id' => $this->backup->id,
                'error' => $e->getMessage(),
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
        Log::error("CreateIncrementalBackupSSH job failed", [
            'backup_id' => $this->backup->id,
            'error' => $exception->getMessage(),
        ]);

        $this->backup->markAsFailed($exception->getMessage());
    }
}

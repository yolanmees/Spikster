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

class RestoreBackupSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 1;

    protected Backup $backup;
    protected Site $site;
    protected array $options;

    /**
     * Create a new job instance.
     */
    public function __construct(Backup $backup, array $options = [])
    {
        $this->backup = $backup;
        $this->site = $backup->site;
        $this->options = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(SSHService $ssh): void
    {
        try {
            Log::info("Starting backup restore for site {$this->site->domain}", [
                'backup_id' => $this->backup->id,
                'backup_type' => $this->backup->type,
            ]);

            $server = $this->site->server;
            $ssh->connect($server);

            $restoreFiles = $this->options['restore_files'] ?? true;
            $restoreDatabase = $this->options['restore_database'] ?? true;
            $restoreEmail = $this->options['restore_email'] ?? false;

            // Verify backup exists
            $backupExists = $ssh->execute("[ -f {$this->backup->filepath} ] && echo 'yes' || echo 'no'");
            if (trim($backupExists) !== 'yes') {
                throw new \Exception("Backup file not found: {$this->backup->filepath}");
            }

            // Verify backup integrity
            if ($this->backup->checksum) {
                Log::info("Verifying backup integrity");
                $actualChecksum = $ssh->execute("sha256sum {$this->backup->filepath} | awk '{print $1}'");
                if (trim($actualChecksum) !== $this->backup->checksum) {
                    throw new \Exception("Backup integrity verification failed");
                }
            }

            // Decrypt backup if encrypted
            $backupPath = $this->backup->filepath;
            if ($this->backup->is_encrypted) {
                Log::info("Decrypting backup");
                // Decrypt logic will be added when implementing encryption
                // For now, assume backup is already decrypted
            }

            // Create temporary restore directory
            $timestamp = Carbon::now()->format('Y-m-d_His');
            $restoreDir = "/tmp/restore_{$this->site->domain}_{$timestamp}";
            $ssh->execute("mkdir -p {$restoreDir}");

            // Extract backup
            Log::info("Extracting backup archive");
            $ssh->execute("tar -xzf {$backupPath} -C {$restoreDir}");

            // Find the extracted backup directory
            $extractedDir = $ssh->execute("ls -1 {$restoreDir} | head -1");
            $extractedPath = "{$restoreDir}/" . trim($extractedDir);

            // For incremental backups, we need the base backup too
            if ($this->backup->type === 'incremental') {
                $baseBackupId = $this->backup->metadata['base_backup_id'] ?? null;
                if ($baseBackupId) {
                    $baseBackup = Backup::find($baseBackupId);
                    if ($baseBackup && $baseBackup->isComplete()) {
                        Log::info("Restoring base backup first for incremental restore");
                        // Recursively restore base backup
                        $this->restoreBaseBackup($ssh, $baseBackup, $restoreDir);
                    }
                }
            }

            // Restore files
            if ($restoreFiles && $this->backup->includes_files) {
                Log::info("Restoring files");
                $this->restoreFiles($ssh, $extractedPath);
            }

            // Restore database
            if ($restoreDatabase && $this->backup->includes_database) {
                Log::info("Restoring database");
                $this->restoreDatabase($ssh, $extractedPath);
            }

            // Restore email
            if ($restoreEmail && $this->backup->includes_email) {
                Log::info("Restoring email");
                $this->restoreEmail($ssh, $extractedPath);
            }

            // Clean up temporary files
            $ssh->execute("rm -rf {$restoreDir}");

            Log::info("Backup restore completed successfully for site {$this->site->domain}", [
                'backup_id' => $this->backup->id,
            ]);

        } catch (\Exception $e) {
            Log::error("Backup restore failed for site {$this->site->domain}", [
                'backup_id' => $this->backup->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Restore files from backup
     */
    protected function restoreFiles(SSHService $ssh, string $extractedPath): void
    {
        $siteRoot = $this->site->path ?? "/var/www/{$this->site->domain}";
        $filesBackup = "{$extractedPath}/files.tar.gz";

        // Check if files backup exists
        $filesExists = $ssh->execute("[ -f {$filesBackup} ] && echo 'yes' || echo 'no'");
        if (trim($filesExists) !== 'yes') {
            Log::warning("Files backup not found in archive");
            return;
        }

        // Create site directory if it doesn't exist
        $ssh->execute("mkdir -p {$siteRoot}");

        // Extract files to site root
        $ssh->execute("tar -xzf {$filesBackup} -C {$siteRoot}");

        // Set proper permissions
        $ssh->execute("chown -R www-data:www-data {$siteRoot}");
        $ssh->execute("find {$siteRoot} -type f -exec chmod 644 {} \\;");
        $ssh->execute("find {$siteRoot} -type d -exec chmod 755 {} \\;");

        Log::info("Files restored successfully to {$siteRoot}");
    }

    /**
     * Restore database from backup
     */
    protected function restoreDatabase(SSHService $ssh, string $extractedPath): void
    {
        $dbBackup = "{$extractedPath}/database.sql.gz";

        // Check if database backup exists
        $dbExists = $ssh->execute("[ -f {$dbBackup} ] && echo 'yes' || echo 'no'");
        if (trim($dbExists) !== 'yes') {
            Log::warning("Database backup not found in archive");
            return;
        }

        $dbName = $this->site->database_name ?? str_replace(['.', '-'], '_', $this->site->domain);
        $dbUser = $this->site->database_user ?? 'root';
        $dbPass = $this->site->database_password ?? '';

        // Drop existing database and recreate
        $dropCmd = "mysql -u{$dbUser}";
        if ($dbPass) {
            $dropCmd .= " -p'{$dbPass}'";
        }
        $dropCmd .= " -e 'DROP DATABASE IF EXISTS {$dbName}; CREATE DATABASE {$dbName};'";

        $ssh->execute($dropCmd);

        // Restore database
        $restoreCmd = "gunzip < {$dbBackup} | mysql -u{$dbUser}";
        if ($dbPass) {
            $restoreCmd .= " -p'{$dbPass}'";
        }
        $restoreCmd .= " {$dbName}";

        $ssh->execute($restoreCmd);

        Log::info("Database '{$dbName}' restored successfully");
    }

    /**
     * Restore email from backup
     */
    protected function restoreEmail(SSHService $ssh, string $extractedPath): void
    {
        $emailBackup = "{$extractedPath}/email.tar.gz";

        // Check if email backup exists
        $emailExists = $ssh->execute("[ -f {$emailBackup} ] && echo 'yes' || echo 'no'");
        if (trim($emailExists) !== 'yes') {
            Log::warning("Email backup not found in archive");
            return;
        }

        $emailPath = "/var/vmail/{$this->site->domain}";

        // Create email directory if it doesn't exist
        $ssh->execute("mkdir -p {$emailPath}");

        // Extract email data
        $ssh->execute("tar -xzf {$emailBackup} -C {$emailPath}");

        // Set proper permissions
        $ssh->execute("chown -R vmail:vmail {$emailPath}");
        $ssh->execute("find {$emailPath} -type f -exec chmod 600 {} \\;");
        $ssh->execute("find {$emailPath} -type d -exec chmod 700 {} \\;");

        Log::info("Email data restored successfully to {$emailPath}");
    }

    /**
     * Restore base backup for incremental restore
     */
    protected function restoreBaseBackup(SSHService $ssh, Backup $baseBackup, string $restoreDir): void
    {
        $baseBackupPath = $baseBackup->filepath;

        // Extract base backup
        $ssh->execute("tar -xzf {$baseBackupPath} -C {$restoreDir}");

        $extractedDir = $ssh->execute("ls -1 {$restoreDir} | grep -v restore_ | head -1");
        $basePath = "{$restoreDir}/" . trim($extractedDir);

        // Restore base backup files and database
        if ($baseBackup->includes_files) {
            $this->restoreFiles($ssh, $basePath);
        }

        if ($baseBackup->includes_database) {
            $this->restoreDatabase($ssh, $basePath);
        }

        Log::info("Base backup restored", [
            'base_backup_id' => $baseBackup->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("RestoreBackupSSH job failed", [
            'backup_id' => $this->backup->id,
            'site_id' => $this->site->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

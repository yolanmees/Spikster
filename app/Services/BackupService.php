<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\BackupSchedule;
use App\Models\BackupStorageLocation;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    /**
     * Create a full backup of a site
     */
    public function createFullBackup(Site $site, array $options = []): Backup
    {
        $backup = Backup::create([
            'site_id' => $site->site_id,
            'server_id' => $site->server->server_id,
            'backup_schedule_id' => $options['schedule_id'] ?? null,
            'type' => 'full',
            'status' => 'pending',
            'includes_database' => $options['include_database'] ?? true,
            'includes_files' => $options['include_files'] ?? true,
            'includes_email' => $options['include_email'] ?? true,
            'is_encrypted' => $options['encrypt'] ?? false,
            'storage_location' => $options['storage_location'] ?? 'local',
        ]);

        $daemon = app(DaemonService::class);
        $result = $daemon->send('backup.create', [
            'site_id' => $backup->site->site_id,
            'username' => $backup->site->username,
            'db_name' => $backup->site->username,
            'db_pass' => $backup->site->database,
            'db_root' => $backup->site->server->database,
            'site_root' => '/home/'.$backup->site->username.'/web',
        ]);

        $backup->update([
            'status' => $result['success'] ? 'completed' : 'failed',
            'filename' => $result['output'] ?? null,
        ]);

        Log::info("Full backup for site {$site->domain}: ".($result['success'] ? 'completed' : 'failed'), [
            'backup_id' => $backup->id,
        ]);

        return $backup->fresh();
    }

    /**
     * Create an incremental backup of a site
     */
    public function createIncrementalBackup(Site $site, array $options = []): Backup
    {
        // Find the last full or incremental backup
        $lastBackup = Backup::where('site_id', $site->site_id)
            ->whereIn('type', ['full', 'incremental'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $lastBackup) {
            // No previous backup exists, create a full backup instead
            Log::info("No previous backup found for site {$site->domain}, creating full backup");

            return $this->createFullBackup($site, $options);
        }

        $backup = Backup::create([
            'site_id' => $site->site_id,
            'server_id' => $site->server->server_id,
            'backup_schedule_id' => $options['schedule_id'] ?? null,
            'type' => 'incremental',
            'status' => 'pending',
            'includes_database' => $options['include_database'] ?? true,
            'includes_files' => $options['include_files'] ?? true,
            'includes_email' => $options['include_email'] ?? false,
            'is_encrypted' => $options['encrypt'] ?? false,
            'storage_location' => $options['storage_location'] ?? 'local',
            'metadata' => [
                'base_backup_id' => $lastBackup->id,
                'base_backup_date' => $lastBackup->created_at->toIso8601String(),
            ],
        ]);

        // Dispatch SSH job to create the incremental backup
        app(DaemonService::class)->send('backup.create', [
            'site_id' => $backup->site->site_id,
            'username' => $backup->site->username,
            'db_name' => $backup->site->username,
            'db_root' => $backup->site->server->database,
            'site_root' => '/home/'.$backup->site->username.'/web',
        ]);

        Log::info("Incremental backup queued for site {$site->domain}", [
            'backup_id' => $backup->id,
            'base_backup_id' => $lastBackup->id,
        ]);

        return $backup;
    }

    /**
     * Create a database-only backup
     */
    public function createDatabaseBackup(Site $site, array $options = []): Backup
    {
        $backup = Backup::create([
            'site_id' => $site->site_id,
            'server_id' => $site->server->server_id,
            'backup_schedule_id' => $options['schedule_id'] ?? null,
            'type' => 'database',
            'status' => 'pending',
            'includes_database' => true,
            'includes_files' => false,
            'includes_email' => false,
            'is_encrypted' => $options['encrypt'] ?? false,
            'storage_location' => $options['storage_location'] ?? 'local',
        ]);

        // For database-only backups, we use the full backup job but it will only backup DB
        app(DaemonService::class)->send('backup.create', [
            'site_id' => $backup->site->site_id,
            'username' => $backup->site->username,
            'db_name' => $backup->site->username,
            'db_root' => $backup->site->server->database,
            'site_root' => '/home/'.$backup->site->username.'/web',
        ]);

        Log::info("Database backup queued for site {$site->domain}", [
            'backup_id' => $backup->id,
        ]);

        return $backup;
    }

    /**
     * Create a files-only backup
     */
    public function createFilesBackup(Site $site, array $options = []): Backup
    {
        $backup = Backup::create([
            'site_id' => $site->site_id,
            'server_id' => $site->server->server_id,
            'backup_schedule_id' => $options['schedule_id'] ?? null,
            'type' => 'files',
            'status' => 'pending',
            'includes_database' => false,
            'includes_files' => true,
            'includes_email' => $options['include_email'] ?? false,
            'is_encrypted' => $options['encrypt'] ?? false,
            'storage_location' => $options['storage_location'] ?? 'local',
        ]);

        // For files-only backups, we use the full backup job but it will only backup files
        app(DaemonService::class)->send('backup.create', [
            'site_id' => $backup->site->site_id,
            'username' => $backup->site->username,
            'db_name' => $backup->site->username,
            'db_root' => $backup->site->server->database,
            'site_root' => '/home/'.$backup->site->username.'/web',
        ]);

        Log::info("Files backup queued for site {$site->domain}", [
            'backup_id' => $backup->id,
        ]);

        return $backup;
    }

    /**
     * Restore a backup
     */
    public function restoreBackup(Backup $backup, array $options = []): bool
    {
        if (! $backup->isComplete()) {
            Log::error('Cannot restore incomplete backup', [
                'backup_id' => $backup->id,
                'status' => $backup->status,
            ]);

            return false;
        }

        // Check if backup exists
        if (! $backup->exists()) {
            Log::error('Backup file does not exist', [
                'backup_id' => $backup->id,
                'filepath' => $backup->filepath,
            ]);

            return false;
        }

        // Verify backup integrity before restore
        if (! $backup->verifyIntegrity()) {
            Log::error('Backup integrity verification failed', [
                'backup_id' => $backup->id,
            ]);

            return false;
        }

        // Dispatch SSH job to restore the backup
        app(DaemonService::class)->send('backup.restore', [
            'archive' => $backup->filepath,
            'username' => $backup->site->username,
            'db_name' => $backup->site->username,
            'db_root' => $backup->site->server->database,
            'site_root' => '/home/'.$backup->site->username.'/web',
        ]);

        Log::info('Restore queued for backup', [
            'backup_id' => $backup->id,
            'site_id' => $backup->site_id,
            'restore_database' => $options['restore_database'] ?? true,
            'restore_files' => $options['restore_files'] ?? true,
        ]);

        return true;
    }

    /**
     * Download a backup
     *
     * @return string|null Path to the downloaded backup file
     */
    public function downloadBackup(Backup $backup): ?string
    {
        if (! $backup->isComplete()) {
            return null;
        }

        // If backup is stored remotely, download it first
        if ($backup->storage_location !== 'local') {
            $storageLocation = BackupStorageLocation::where('name', $backup->storage_location)
                ->where('is_active', true)
                ->first();

            if (! $storageLocation) {
                Log::error('Storage location not found', [
                    'storage_location' => $backup->storage_location,
                ]);

                return null;
            }

            // Download from remote storage
            // This will be implemented with the SSH jobs
            Log::info('Downloading backup from remote storage', [
                'backup_id' => $backup->id,
                'storage_location' => $backup->storage_location,
            ]);
        }

        return $backup->filepath;
    }

    /**
     * Delete a backup (soft delete)
     *
     * @param  bool  $deleteFile  Also delete the physical backup file
     */
    public function deleteBackup(Backup $backup, bool $deleteFile = false): bool
    {
        if ($deleteFile && $backup->exists()) {
            // Delete physical backup file
            // This will be implemented with the SSH jobs
            Log::info('Deleting backup file', [
                'backup_id' => $backup->id,
                'filepath' => $backup->filepath,
            ]);
        }

        // Soft delete the backup record
        $backup->status = 'deleted';
        $backup->save();
        $backup->delete();

        Log::info('Backup deleted', [
            'backup_id' => $backup->id,
            'deleted_file' => $deleteFile,
        ]);

        return true;
    }

    /**
     * Rotate backups according to retention policy
     *
     * @return int Number of backups deleted
     */
    public function rotateBackups(Site $site, ?BackupSchedule $schedule = null): int
    {
        $deleted = 0;

        if ($schedule) {
            // Use schedule's retention policy
            $retentionCount = $schedule->retention_count;
            $retentionDays = $schedule->retention_days;

            // Get backups for this schedule
            $query = Backup::where('site_id', $site->site_id)
                ->where('backup_schedule_id', $schedule->id)
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc');
        } else {
            // Use default retention policy (keep last 30 days or 10 backups)
            $retentionCount = 10;
            $retentionDays = 30;

            $query = Backup::where('site_id', $site->site_id)
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc');
        }

        // Apply retention count
        if ($retentionCount) {
            $backupsToDelete = $query->skip($retentionCount)->get();
            foreach ($backupsToDelete as $backup) {
                $this->deleteBackup($backup, true);
                $deleted++;
            }
        }

        // Apply retention days
        if ($retentionDays) {
            $cutoffDate = Carbon::now()->subDays($retentionDays);
            $oldBackups = Backup::where('site_id', $site->site_id)
                ->where('status', 'completed')
                ->where('created_at', '<', $cutoffDate)
                ->get();

            foreach ($oldBackups as $backup) {
                if (! $backup->trashed()) {
                    $this->deleteBackup($backup, true);
                    $deleted++;
                }
            }
        }

        Log::info('Backup rotation completed', [
            'site_id' => $site->site_id,
            'schedule_id' => $schedule?->id,
            'deleted_count' => $deleted,
        ]);

        return $deleted;
    }

    /**
     * Create a backup schedule
     */
    public function createSchedule(Site $site, array $data): BackupSchedule
    {
        $schedule = BackupSchedule::create([
            'site_id' => $site->site_id,
            'name' => $data['name'],
            'type' => $data['type'] ?? 'full',
            'frequency' => $data['frequency'],
            'time' => $data['time'] ?? '02:00',
            'day_of_week' => $data['day_of_week'] ?? null,
            'day_of_month' => $data['day_of_month'] ?? null,
            'cron_expression' => $data['cron_expression'] ?? null,
            'storage_locations' => $data['storage_locations'] ?? ['local'],
            'is_encrypted' => $data['is_encrypted'] ?? false,
            'retention_count' => $data['retention_count'] ?? 10,
            'retention_days' => $data['retention_days'] ?? 30,
            'is_active' => $data['is_active'] ?? true,
        ]);

        // Calculate next run time
        $schedule->calculateNextRun();

        Log::info('Backup schedule created', [
            'schedule_id' => $schedule->id,
            'site_id' => $site->site_id,
            'frequency' => $schedule->frequency,
        ]);

        return $schedule;
    }

    /**
     * Update a backup schedule
     */
    public function updateSchedule(BackupSchedule $schedule, array $data): BackupSchedule
    {
        $schedule->update($data);

        // Recalculate next run time if frequency changed
        if (isset($data['frequency']) || isset($data['time'])) {
            $schedule->calculateNextRun();
        }

        Log::info('Backup schedule updated', [
            'schedule_id' => $schedule->id,
        ]);

        return $schedule;
    }

    /**
     * Delete a backup schedule
     *
     * @param  bool  $deleteBackups  Also delete associated backups
     */
    public function deleteSchedule(BackupSchedule $schedule, bool $deleteBackups = false): bool
    {
        if ($deleteBackups) {
            $backups = Backup::where('backup_schedule_id', $schedule->id)->get();
            foreach ($backups as $backup) {
                $this->deleteBackup($backup, true);
            }
        }

        $schedule->delete();

        Log::info('Backup schedule deleted', [
            'schedule_id' => $schedule->id,
            'deleted_backups' => $deleteBackups,
        ]);

        return true;
    }

    /**
     * Test a storage location connection
     */
    public function testStorageConnection(BackupStorageLocation $location): bool
    {
        $result = $location->testConnection();

        $location->last_test_at = Carbon::now();
        $location->test_status = $result ? 'success' : 'failed';
        $location->save();

        Log::info('Storage connection tested', [
            'location_id' => $location->id,
            'type' => $location->type,
            'result' => $result,
        ]);

        return $result;
    }

    /**
     * Create a storage location
     */
    public function createStorageLocation(array $data): BackupStorageLocation
    {
        $location = BackupStorageLocation::create([
            'user_id' => $data['user_id'] ?? Auth::id(),
            'name' => $data['name'],
            'type' => $data['type'],
            'config' => $data['config'],
            'is_default' => $data['is_default'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        // If this is set as default, unset other defaults
        if ($location->is_default) {
            BackupStorageLocation::where('id', '!=', $location->id)
                ->update(['is_default' => false]);
        }

        Log::info('Storage location created', [
            'location_id' => $location->id,
            'type' => $location->type,
        ]);

        return $location;
    }

    /**
     * Update a storage location
     */
    public function updateStorageLocation(BackupStorageLocation $location, array $data): BackupStorageLocation
    {
        $location->update($data);

        // If this is set as default, unset other defaults
        if ($location->is_default) {
            BackupStorageLocation::where('id', '!=', $location->id)
                ->update(['is_default' => false]);
        }

        Log::info('Storage location updated', [
            'location_id' => $location->id,
        ]);

        return $location;
    }

    /**
     * Delete a storage location
     */
    public function deleteStorageLocation(BackupStorageLocation $location): bool
    {
        if ($location->is_default) {
            Log::error('Cannot delete default storage location', [
                'location_id' => $location->id,
            ]);

            return false;
        }

        $location->delete();

        Log::info('Storage location deleted', [
            'location_id' => $location->id,
        ]);

        return true;
    }

    /**
     * Get backup statistics for a site
     */
    public function getBackupStats(Site $site): array
    {
        $backups = Backup::where('site_id', $site->site_id)->get();

        $stats = [
            'total_backups' => $backups->count(),
            'completed_backups' => $backups->where('status', 'completed')->count(),
            'failed_backups' => $backups->where('status', 'failed')->count(),
            'in_progress_backups' => $backups->where('status', 'in_progress')->count(),
            'total_size' => $backups->sum('size'),
            'total_compressed_size' => $backups->sum('compressed_size'),
            'average_compression_ratio' => $backups->avg('compression_ratio'),
            'last_backup' => $backups->where('status', 'completed')
                ->sortByDesc('created_at')
                ->first(),
            'oldest_backup' => $backups->where('status', 'completed')
                ->sortBy('created_at')
                ->first(),
            'storage_by_location' => $backups->groupBy('storage_location')
                ->map(fn ($group) => [
                    'count' => $group->count(),
                    'size' => $group->sum('compressed_size'),
                ]),
        ];

        return $stats;
    }

    /**
     * Process scheduled backups
     * This method should be called by Laravel Scheduler
     *
     * @return int Number of backups created
     */
    public function processScheduledBackups(): int
    {
        $created = 0;

        // Get all due schedules
        $schedules = BackupSchedule::active()
            ->due()
            ->with('site')
            ->get();

        foreach ($schedules as $schedule) {
            try {
                // Create backup based on schedule type
                switch ($schedule->type) {
                    case 'full':
                        $this->createFullBackup($schedule->site, [
                            'schedule_id' => $schedule->id,
                            'encrypt' => $schedule->is_encrypted,
                            'storage_location' => $schedule->storage_locations[0] ?? 'local',
                        ]);
                        break;

                    case 'incremental':
                        $this->createIncrementalBackup($schedule->site, [
                            'schedule_id' => $schedule->id,
                            'encrypt' => $schedule->is_encrypted,
                            'storage_location' => $schedule->storage_locations[0] ?? 'local',
                        ]);
                        break;

                    case 'database':
                        $this->createDatabaseBackup($schedule->site, [
                            'schedule_id' => $schedule->id,
                            'encrypt' => $schedule->is_encrypted,
                            'storage_location' => $schedule->storage_locations[0] ?? 'local',
                        ]);
                        break;

                    case 'files':
                        $this->createFilesBackup($schedule->site, [
                            'schedule_id' => $schedule->id,
                            'encrypt' => $schedule->is_encrypted,
                            'storage_location' => $schedule->storage_locations[0] ?? 'local',
                        ]);
                        break;
                }

                // Mark schedule as run and calculate next run time
                $schedule->markAsRun();

                // Run backup rotation
                $this->rotateBackups($schedule->site, $schedule);

                $created++;

            } catch (\Exception $e) {
                Log::error('Failed to process scheduled backup', [
                    'schedule_id' => $schedule->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Scheduled backups processed', [
            'created_count' => $created,
        ]);

        return $created;
    }
}

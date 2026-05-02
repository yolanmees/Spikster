<?php

namespace App\Console\Commands;

use App\Models\Deployment as ArtifactDeployment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ArtifactCleanupCommand extends Command
{
    protected $signature = 'spikster:cleanup-artifacts
        {--dry-run : Show what would be deleted without actually deleting}
        {--days=30 : Delete artifacts older than this many days}
        {--force : Skip confirmation prompt}';

    protected $description = 'Clean up old deployment artifacts, logs, and temporary files';

    private int $totalDeleted = 0;

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $this->info("Artifact Cleanup (older than {$days} days)");
        if ($dryRun) {
            $this->warn('DRY RUN — no files will be deleted');
        }
        $this->newLine();

        $this->cleanDeployments($cutoff, $dryRun);
        $this->cleanFailedJobs($cutoff, $dryRun);
        $this->cleanOldLogs($cutoff, $dryRun);
        $this->cleanStorageTemp($cutoff, $dryRun);
        $this->cleanBackupRecords($cutoff, $dryRun);

        $this->newLine();
        if ($dryRun) {
            $this->info("Dry run complete. {$this->totalDeleted} items would be deleted.");
        } else {
            $this->info("Cleanup complete. {$this->totalDeleted} items deleted.");
        }

        return Command::SUCCESS;
    }

    private function cleanDeployments($cutoff, bool $dryRun): void
    {
        $old = ArtifactDeployment::where('created_at', '<', $cutoff);
        $count = $old->count();

        if ($count === 0) {
            $this->line('  ✓ No old deployments to clean');

            return;
        }

        if ($dryRun) {
            $this->line("  → Would delete {$count} old deployment(s)");
            $this->totalDeleted += $count;

            return;
        }

        $old->delete();
        $this->totalDeleted += $count;
        $this->line("  ✓ Deleted {$count} old deployment(s)");
    }

    private function cleanFailedJobs($cutoff, bool $dryRun): void
    {
        $count = DB::table('failed_jobs')
            ->where('failed_at', '<', $cutoff)
            ->count();

        if ($count === 0) {
            $this->line('  ✓ No old failed jobs to clean');

            return;
        }

        if ($dryRun) {
            $this->line("  → Would delete {$count} old failed job(s)");
            $this->totalDeleted += $count;

            return;
        }

        DB::table('failed_jobs')
            ->where('failed_at', '<', $cutoff)
            ->delete();

        $this->totalDeleted += $count;
        $this->line("  ✓ Deleted {$count} old failed job(s)");
    }

    private function cleanOldLogs($cutoff, bool $dryRun): void
    {
        $logDir = storage_path('logs');
        if (! is_dir($logDir)) {
            return;
        }

        $deleted = 0;
        foreach (File::files($logDir) as $file) {
            if ($file->getExtension() !== 'log') {
                continue;
            }

            $modified = Carbon::createFromTimestamp($file->getMTime());
            if ($modified->lt($cutoff)) {
                if (! $dryRun) {
                    File::delete($file->getPathname());
                }
                $deleted++;
            }
        }

        if ($deleted === 0) {
            $this->line('  ✓ No old log files to clean');

            return;
        }

        $this->totalDeleted += $deleted;
        $this->line(($dryRun ? '  → Would delete' : '  ✓ Deleted')." {$deleted} old log file(s)");
    }

    private function cleanStorageTemp($cutoff, bool $dryRun): void
    {
        $paths = [
            storage_path('framework/cache/data'),
            storage_path('app/temp'),
            storage_path('app/public/temp'),
        ];

        $deleted = 0;
        foreach ($paths as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            foreach (File::allFiles($dir) as $file) {
                $modified = Carbon::createFromTimestamp($file->getMTime());
                if ($modified->lt($cutoff)) {
                    if (! $dryRun) {
                        File::delete($file->getPathname());
                    }
                    $deleted++;
                }
            }

            foreach (File::directories($dir) as $subDir) {
                $modified = Carbon::createFromTimestamp(File::lastModified($subDir));
                if ($modified->lt($cutoff) && count(File::files($subDir)) === 0) {
                    if (! $dryRun) {
                        File::deleteDirectory($subDir);
                    }
                    $deleted++;
                }
            }
        }

        if ($deleted === 0) {
            $this->line('  ✓ No old temp files to clean');

            return;
        }

        $this->totalDeleted += $deleted;
        $this->line(($dryRun ? '  → Would delete' : '  ✓ Deleted')." {$deleted} old temp file(s)");
    }

    private function cleanBackupRecords($cutoff, bool $dryRun): void
    {
        $count = DB::table('backups')
            ->where('status', 'failed')
            ->where('created_at', '<', $cutoff)
            ->count();

        if ($count === 0) {
            $this->line('  ✓ No old failed backup records to clean');

            return;
        }

        if ($dryRun) {
            $this->line("  → Would delete {$count} old failed backup record(s)");
            $this->totalDeleted += $count;

            return;
        }

        DB::table('backups')
            ->where('status', 'failed')
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->totalDeleted += $count;
        $this->line("  ✓ Deleted {$count} old failed backup record(s)");
    }
}

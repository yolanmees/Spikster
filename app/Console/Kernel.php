<?php

namespace App\Console;

use App\Console\Commands\ActiveSetupCount;
use App\Console\Commands\CipiUpdate;
use App\Console\Commands\LogRotate;
use App\Console\Commands\ServerSetupCheck;
use App\Jobs\FetchServerMetricsJob;
use App\Models\Backup;
use App\Models\Server;
use App\Models\ServerMetric;
use App\Models\Site;
use App\Services\BackupService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ActiveSetupCount::class,
        LogRotate::class,
        ServerSetupCheck::class,
        CipiUpdate::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('servers:setupcheck')->everyMinute();
        $schedule->command('cipi:update')->dailyAt('12:05');
        $schedule->command('cipi:logrotate')->dailyAt('00:00');
        $schedule->command('cipi:activesetupcount')->dailyAt('03:03');

        // New monitoring system - fetch metrics from spikster-agent
        $schedule->call(function () {
            $servers = Server::active()->get();
            foreach ($servers as $server) {
                FetchServerMetricsJob::dispatch($server);
            }
        })->everyMinute()->name('fetch-server-metrics');

        // Cleanup old metrics daily
        $schedule->call(function () {
            $deleted = ServerMetric::cleanupOldMetrics(
                config('monitoring.metrics_retention_days', 30)
            );
            Log::info("Cleaned up {$deleted} old server metrics");
        })->dailyAt('03:00')->name('cleanup-old-metrics');

        $schedule->command('audit:cleanup')->weekly()->sundays()->at('02:00');

        // Backup System - Process scheduled backups
        $schedule->call(function () {
            $backupService = app(BackupService::class);
            $created = $backupService->processScheduledBackups();
            Log::info("Processed scheduled backups: {$created} backups created");
        })->everyFiveMinutes()->name('process-scheduled-backups');

        // Backup System - Cleanup old backups daily
        $schedule->call(function () {
            $sites = Site::all();
            $totalDeleted = 0;

            foreach ($sites as $site) {
                $backupService = app(BackupService::class);
                $deleted = $backupService->rotateBackups($site);
                $totalDeleted += $deleted;
            }

            Log::info("Backup rotation completed: {$totalDeleted} old backups removed");
        })->dailyAt('04:00')->name('rotate-backups');

        // Backup System - Cleanup failed backups weekly
        $schedule->call(function () {
            $failedBackups = Backup::where('status', 'failed')
                ->where('created_at', '<', now()->subDays(7))
                ->get();

            $deleted = 0;
            $backupService = app(BackupService::class);

            foreach ($failedBackups as $backup) {
                $backupService->deleteBackup($backup, true);
                $deleted++;
            }

            Log::info("Cleaned up {$deleted} failed backups older than 7 days");
        })->weekly()->sundays()->at('05:00')->name('cleanup-failed-backups');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}

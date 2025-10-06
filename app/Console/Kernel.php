<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\ActiveSetupCount::class,
        \App\Console\Commands\LogRotate::class,
        \App\Console\Commands\ServerSetupCheck::class,
        \App\Console\Commands\CipiUpdate::class,
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
            $servers = \App\Models\Server::active()->get();
            foreach ($servers as $server) {
                \App\Jobs\FetchServerMetricsJob::dispatch($server);
            }
        })->everyMinute()->name('fetch-server-metrics');

        // Cleanup old metrics daily
        $schedule->call(function () {
            $deleted = \App\Models\ServerMetric::cleanupOldMetrics(
                config('monitoring.metrics_retention_days', 30)
            );
            \Illuminate\Support\Facades\Log::info("Cleaned up {$deleted} old server metrics");
        })->dailyAt('03:00')->name('cleanup-old-metrics');

        $schedule->command('audit:cleanup')->weekly()->sundays()->at('02:00');

        // Backup System - Process scheduled backups
        $schedule->call(function () {
            $backupService = app(\App\Services\BackupService::class);
            $created = $backupService->processScheduledBackups();
            \Illuminate\Support\Facades\Log::info("Processed scheduled backups: {$created} backups created");
        })->everyFiveMinutes()->name('process-scheduled-backups');

        // Backup System - Cleanup old backups daily
        $schedule->call(function () {
            $sites = \App\Models\Site::all();
            $totalDeleted = 0;

            foreach ($sites as $site) {
                $backupService = app(\App\Services\BackupService::class);
                $deleted = $backupService->rotateBackups($site);
                $totalDeleted += $deleted;
            }

            \Illuminate\Support\Facades\Log::info("Backup rotation completed: {$totalDeleted} old backups removed");
        })->dailyAt('04:00')->name('rotate-backups');

        // Backup System - Cleanup failed backups weekly
        $schedule->call(function () {
            $failedBackups = \App\Models\Backup::where('status', 'failed')
                ->where('created_at', '<', now()->subDays(7))
                ->get();

            $deleted = 0;
            $backupService = app(\App\Services\BackupService::class);

            foreach ($failedBackups as $backup) {
                $backupService->deleteBackup($backup, true);
                $deleted++;
            }

            \Illuminate\Support\Facades\Log::info("Cleaned up {$deleted} failed backups older than 7 days");
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

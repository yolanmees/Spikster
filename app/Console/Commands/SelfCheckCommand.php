<?php

namespace App\Console\Commands;

use App\Models\Deployment;
use App\Models\Server;
use App\Models\Site;
use App\Services\DaemonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SelfCheckCommand extends Command
{
    protected $signature = 'spikster:self-check
        {--json : Output as JSON}
        {--fix : Attempt to auto-fix common issues}';

    protected $description = 'Run operational health checks on the Spikster installation';

    private int $passed = 0;

    private int $warnings = 0;

    private int $failures = 0;

    private array $results = [];

    public function handle(DaemonService $daemon): int
    {
        $this->info('Spikster Self-Check');
        $this->newLine();

        $this->checkPhpVersion();
        $this->checkStorageWritable();
        $this->checkDatabaseConnection();
        $this->checkQueueWorker();
        $this->checkDaemon($daemon);
        $this->checkFailedJobs();
        $this->checkPendingJobs();
        $this->checkAppKey();
        $this->checkHttps();
        $this->checkDiskSpace();
        $this->checkSitesHealth();
        $this->checkOrphanedDeployments();
        $this->checkMaintenanceMode();

        $this->renderResults();

        return $this->failures > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function checkPhpVersion(): void
    {
        $version = PHP_VERSION;
        if (version_compare($version, '8.2', '<')) {
            $this->checkFail('PHP version', "{$version} (minimum 8.2 required)");
        } else {
            $this->checkPass('PHP version', $version);
        }
    }

    private function checkStorageWritable(): void
    {
        $paths = [
            storage_path() => 'storage',
            storage_path('logs') => 'storage/logs',
            storage_path('framework/cache') => 'storage/framework/cache',
            storage_path('framework/views') => 'storage/framework/views',
            storage_path('framework/sessions') => 'storage/framework/sessions',
        ];

        $issues = [];
        foreach ($paths as $path => $label) {
            if (! is_dir($path)) {
                $issues[] = "{$label} missing";
            } elseif (! is_writable($path)) {
                $issues[] = "{$label} not writable";
            }
        }

        if (empty($issues)) {
            $this->checkPass('Storage permissions', 'All directories writable');
        } else {
            $this->checkFail('Storage permissions', implode('; ', $issues));
        }
    }

    private function checkDatabaseConnection(): void
    {
        try {
            DB::connection()->getPdo();
            $driver = DB::connection()->getDriverName();
            $this->checkPass('Database connection', $driver);
        } catch (\Throwable $e) {
            $this->checkFail('Database connection', $e->getMessage());
        }
    }

    private function checkQueueWorker(): void
    {
        $failedCount = DB::table('failed_jobs')->count();
        $pendingCount = DB::table('jobs')->count();

        $status = "{$pendingCount} pending, {$failedCount} failed";

        if ($failedCount > 0) {
            $this->checkWarn('Queue worker', $status);
        } else {
            $this->checkPass('Queue worker', $status);
        }
    }

    private function checkDaemon(DaemonService $daemon): void
    {
        try {
            if ($daemon->isAvailable()) {
                $this->checkPass('Panel daemon', 'Running and reachable');
            } else {
                $this->checkWarn('Panel daemon', 'Not responding');
            }
        } catch (\Throwable $e) {
            $this->checkWarn('Panel daemon', $e->getMessage());
        }
    }

    private function checkFailedJobs(): void
    {
        $count = DB::table('failed_jobs')->count();
        if ($count > 0) {
            $this->checkWarn('Failed jobs', "{$count} failed job(s) found");
            if ($this->option('fix')) {
                DB::table('failed_jobs')->delete();
                $this->line('         → Cleared failed jobs table');
            }
        } else {
            $this->checkPass('Failed jobs', 'None');
        }
    }

    private function checkPendingJobs(): void
    {
        $count = DB::table('jobs')->count();
        if ($count > 100) {
            $this->checkWarn('Pending jobs', "{$count} jobs in queue (high backlog)");
        } else {
            $this->checkPass('Pending jobs', "{$count} job(s)");
        }
    }

    private function checkAppKey(): void
    {
        $key = config('app.key');
        if (! $key || $key === 'base64:'.str_repeat('=', 0)) {
            $this->checkFail('APP_KEY', 'Not set or invalid');
        } elseif (strlen($key) < 20) {
            $this->checkFail('APP_KEY', 'Suspiciously short key');
        } else {
            $this->checkPass('APP_KEY', 'Configured');
        }
    }

    private function checkHttps(): void
    {
        $url = config('app.url');
        if ($url && str_starts_with($url, 'https://')) {
            $this->checkPass('HTTPS', 'Panel URL uses HTTPS');
        } elseif (app()->environment('production')) {
            $this->checkFail('HTTPS', 'Panel URL does not use HTTPS in production');
        } else {
            $this->checkPass('HTTPS', 'Not required in development');
        }
    }

    private function checkDiskSpace(): void
    {
        $free = disk_free_space(base_path());
        $total = disk_total_space(base_path());
        $percent = round((1 - $free / $total) * 100);

        if ($percent > 90) {
            $this->checkFail('Disk space', "{$percent}% used (above 90%)");
        } elseif ($percent > 80) {
            $this->checkWarn('Disk space', "{$percent}% used");
        } else {
            $this->checkPass('Disk space', "{$percent}% used");
        }
    }

    private function checkSitesHealth(): void
    {
        $total = Site::count();
        $panelSites = Site::where('panel', true)->count();

        if ($total === 0) {
            $this->checkWarn('Sites', 'No sites configured');
        } else {
            $this->checkPass('Sites', "{$total} total ({$panelSites} panel)");
        }

        $servers = Server::count();
        if ($servers === 0) {
            $this->checkWarn('Servers', 'No servers configured');
        } else {
            $active = Server::where('status', 1)->count();
            $total = Server::count();
            if ($active < $total) {
                $this->checkWarn('Servers', "{$active} active of {$total}");
            } else {
                $this->checkPass('Servers', "{$total} total, all active");
            }
        }
    }

    private function checkOrphanedDeployments(): void
    {
        try {
            $orphaned = Deployment::whereDoesntHave('site')->count();
            if ($orphaned > 0) {
                $this->checkWarn('Orphaned deployments', "{$orphaned} deployment(s) without a site");
            }
        } catch (\Throwable) {
        }
    }

    private function checkMaintenanceMode(): void
    {
        if (file_exists(storage_path('framework/down'))) {
            $this->checkWarn('Maintenance mode', 'Application is in maintenance mode');
        } else {
            $this->checkPass('Maintenance mode', 'Not active');
        }
    }

    private function checkPass(string $check, string $detail): void
    {
        $this->passed++;
        $this->results[] = ['status' => 'PASS', 'check' => $check, 'detail' => $detail];
    }

    private function checkWarn(string $check, string $detail): void
    {
        $this->warnings++;
        $this->results[] = ['status' => 'WARN', 'check' => $check, 'detail' => $detail];
    }

    private function checkFail(string $check, string $detail): void
    {
        $this->failures++;
        $this->results[] = ['status' => 'FAIL', 'check' => $check, 'detail' => $detail];
    }

    private function renderResults(): void
    {
        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'summary' => [
                    'passed' => $this->passed,
                    'warnings' => $this->warnings,
                    'failures' => $this->failures,
                    'total' => count($this->results),
                ],
                'checks' => $this->results,
            ], JSON_PRETTY_PRINT));

            return;
        }

        foreach ($this->results as $result) {
            $icon = match ($result['status']) {
                'PASS' => "\x1b[32mPASS\x1b[0m",
                'WARN' => "\x1b[33mWARN\x1b[0m",
                'FAIL' => "\x1b[31mFAIL\x1b[0m",
            };
            $this->line("  {$icon}  {$result['check']}");
            $this->line("        {$result['detail']}");
        }

        $this->newLine();
        $this->line("  Summary: {$this->passed} passed, {$this->warnings} warnings, {$this->failures} failures");
    }
}

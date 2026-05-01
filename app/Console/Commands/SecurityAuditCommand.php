<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\DaemonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SecurityAuditCommand extends Command
{
    protected $signature = 'spikster:security-audit
        {--json : Output results as JSON for machine parsing}
        {--fail-only : Only show failing checks}
        {--server= : Check a specific remote server by ID}';

    protected $description = 'Run a comprehensive security baseline audit';

    private array $results = [];

    private int $passed = 0;

    private int $warnings = 0;

    private int $failures = 0;

    public function handle(DaemonService $daemon): int
    {
        if (! $this->option('json')) {
            $this->info('Spikster Security Baseline Audit');
            $this->newLine();
        }

        $this->checkDaemonHealth($daemon);
        $this->checkServiceStatus($daemon, 'nginx');
        $this->checkServiceStatus($daemon, 'fail2ban');
        $this->checkServiceStatus($daemon, 'mysql');
        $this->checkAuditLogging();
        $this->checkPasswordPolicy();
        $this->checkTwoFactorAuth();
        $this->checkRbacBypass();
        $this->checkBruteForceProtection();
        $this->checkSecurityHeaders();
        $this->checkQueueHealth();
        $this->checkFilePermissions();
        $this->checkAdminAccounts();
        $this->checkSslExpiry();
        $this->checkBackupHealth();
        $this->checkEnvSecurity();
        $this->checkDatabaseEncryption();

        $this->renderResults();

        return $this->failures > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function auditPass(string $check, string $detail = ''): void
    {
        $this->passed++;
        $this->results[] = ['status' => 'PASS', 'check' => $check, 'detail' => $detail];
    }

    private function auditWarn(string $check, string $detail = ''): void
    {
        $this->warnings++;
        $this->results[] = ['status' => 'WARN', 'check' => $check, 'detail' => $detail];
    }

    private function auditFail(string $check, string $detail = ''): void
    {
        $this->failures++;
        $this->results[] = ['status' => 'FAIL', 'check' => $check, 'detail' => $detail];
    }

    private function checkDaemonHealth(DaemonService $daemon): void
    {
        try {
            if ($daemon->isAvailable()) {
                $this->auditPass('Daemon connectivity', 'Spikster daemon is reachable');
            } else {
                $this->auditFail('Daemon connectivity', 'Spikster daemon is not responding');
            }
        } catch (\Exception $e) {
            $this->auditFail('Daemon connectivity', $e->getMessage());
        }
    }

    private function checkServiceStatus(DaemonService $daemon, string $service): void
    {
        try {
            $status = $daemon->status($service);
            if (str_contains($status, 'running') || str_contains($status, 'active')) {
                $this->auditPass("Service: {$service}", $status);
            } else {
                $this->auditFail("Service: {$service}", $status);
            }
        } catch (\Exception $e) {
            $this->auditWarn("Service: {$service}", "Cannot check: {$e->getMessage()}");
        }
    }

    private function checkAuditLogging(): void
    {
        $config = config('security.audit');
        if (! ($config['enabled'] ?? true)) {
            $this->auditFail('Audit logging', 'Audit logging is disabled in config');

            return;
        }

        $recentCount = AuditLog::where('created_at', '>=', now()->subDays(7))->count();
        $totalCount = AuditLog::count();

        if ($totalCount === 0) {
            $this->auditWarn('Audit logging', 'Enabled but no entries found');
        } elseif ($recentCount < 10) {
            $this->auditWarn('Audit logging', "Enabled, {$recentCount} entries in last 7 days (unusually low)");
        } else {
            $this->auditPass('Audit logging', "{$recentCount} entries in last 7 days, {$totalCount} total");
        }

        $tracked = $config['events'] ?? [];
        $expected = ['login', 'logout', 'server_create', 'server_delete', 'site_create', 'site_delete'];

        $missing = array_diff($expected, $tracked);
        if (! empty($missing)) {
            $this->auditWarn('Audit events', 'Not tracking: '.implode(', ', $missing));
        }
    }

    private function checkPasswordPolicy(): void
    {
        $config = config('security.password');
        $minLength = $config['min_length'] ?? 8;

        if ($minLength < 8) {
            $this->auditFail('Password policy', "Minimum length {$minLength} (recommended: 8+)");
        } elseif ($minLength < 12) {
            $this->auditWarn('Password policy', "Minimum length {$minLength} (recommended: 12+)");
        } else {
            $this->auditPass('Password policy', "Minimum length {$minLength}");
        }

        if (! ($config['require_uppercase'] ?? true)) {
            $this->auditWarn('Password policy', 'Uppercase not required');
        }

        if (! ($config['require_numbers'] ?? true)) {
            $this->auditWarn('Password policy', 'Numbers not required');
        }
    }

    private function checkTwoFactorAuth(): void
    {
        $config = config('security.2fa');
        if (! ($config['enabled'] ?? true)) {
            $this->auditFail('2FA', 'Two-factor authentication is disabled');

            return;
        }

        $this->auditPass('2FA', 'Two-factor authentication is enabled');

        if ($config['required_for_admin'] ?? false) {
            $adminsWithout2FA = User::role(['Super Admin', 'Admin'])
                ->whereDoesntHave('twoFactorSettings', fn ($q) => $q->where('is_enabled', true))
                ->count();

            if ($adminsWithout2FA > 0) {
                $this->auditFail('2FA enforcement', "{$adminsWithout2FA} admin(s) without 2FA enabled");
            } else {
                $this->auditPass('2FA enforcement', 'All admins have 2FA enabled');
            }
        } else {
            $this->auditWarn('2FA enforcement', '2FA is not required for admin roles');
        }
    }

    private function checkRbacBypass(): void
    {
        $config = config('security.rbac');
        if ($config['panel_admin_bypass'] ?? true) {
            $identifier = $config['panel_admin_identifier'] ?? 'admin@localhost';
            $this->auditWarn('RBAC bypass', "Panel admin bypass is ENABLED ({$identifier})");
        } else {
            $this->auditPass('RBAC bypass', 'Panel admin bypass is disabled');
        }
    }

    private function checkBruteForceProtection(): void
    {
        $config = config('security.brute_force');
        if (! ($config['enabled'] ?? true)) {
            $this->auditFail('Brute force protection', 'Disabled');

            return;
        }

        $maxAttempts = $config['max_attempts'] ?? 5;
        $lockoutDuration = $config['lockout_duration'] ?? 900;

        if ($maxAttempts > 10) {
            $this->auditWarn('Brute force protection', "Max attempts: {$maxAttempts} (recommended: <=10)");
        } else {
            $this->auditPass('Brute force protection', "Max attempts: {$maxAttempts}, lockout: {$lockoutDuration}s");
        }
    }

    private function checkSecurityHeaders(): void
    {
        $headers = config('security.headers');
        $checks = [
            'x_frame_options' => ['SAMEORIGIN', 'DENY'],
            'x_content_type_options' => ['nosniff'],
            'x_xss_protection' => ['1; mode=block'],
            'strict_transport_security' => ['max-age='],
            'referrer_policy' => ['strict-origin', 'same-origin', 'no-referrer'],
        ];

        $issues = [];
        foreach ($checks as $key => $expected) {
            $value = $headers[$key] ?? '';
            $matched = false;
            foreach ($expected as $pattern) {
                if (str_contains($value, $pattern)) {
                    $matched = true;
                    break;
                }
            }
            if (! $matched) {
                $issues[] = "{$key}: '{$value}'";
            }
        }

        if (empty($issues)) {
            $this->auditPass('Security headers', 'All header presets are configured');
        } else {
            $this->auditWarn('Security headers', implode('; ', $issues));
        }

        $csp = config('security.csp');
        if (! ($csp['enabled'] ?? true)) {
            $this->auditWarn('CSP', 'Content Security Policy is disabled');
        }
    }

    private function checkQueueHealth(): void
    {
        $failedCount = DB::table('failed_jobs')->count();
        if ($failedCount > 0) {
            $this->auditWarn('Queue health', "{$failedCount} failed job(s) in the failed_jobs table");
        } else {
            $this->auditPass('Queue health', 'No failed jobs');
        }

        $pendingCount = DB::table('jobs')->count();
        if ($pendingCount > 50) {
            $this->auditWarn('Queue backlog', "{$pendingCount} pending jobs in queue");
        } elseif ($pendingCount > 0) {
            $this->auditPass('Queue backlog', "{$pendingCount} pending jobs");
        }
    }

    private function checkFilePermissions(): void
    {
        $paths = [
            'storage' => storage_path(),
            'bootstrap/cache' => base_path('bootstrap/cache'),
            '.env' => base_path('.env'),
        ];

        foreach ($paths as $label => $path) {
            if (! file_exists($path)) {
                $this->auditFail("File permissions: {$label}", 'Path does not exist');

                continue;
            }

            $perms = fileperms($path);
            $permStr = substr(sprintf('%o', $perms), -4);

            if ($label === '.env' && $perms & 0x0004) {
                $this->auditWarn("File permissions: {$label}", "World-readable ({$permStr})");
            } elseif (! is_writable($path)) {
                if ($label === 'storage' || $label === 'bootstrap/cache') {
                    $this->auditFail("File permissions: {$label}", "Not writable ({$permStr})");
                }
            }
        }
    }

    private function checkAdminAccounts(): void
    {
        $adminCount = User::role('Super Admin')->count();
        if ($adminCount === 0) {
            $this->auditFail('Admin accounts', 'No Super Admin users exist');
        } elseif ($adminCount > 3) {
            $this->auditWarn('Admin accounts', "{$adminCount} Super Admin users (consider minimizing)");
        } else {
            $this->auditPass('Admin accounts', "{$adminCount} Super Admin user(s)");
        }

        $totalUsers = User::count();
        $unassigned = User::doesntHave('roles')->count();
        if ($unassigned > 0) {
            $this->auditWarn('User roles', "{$unassigned} of {$totalUsers} users have no role assigned");
        }
    }

    private function checkSslExpiry(): void
    {
        try {
            $panelUrl = config('app.url');
            if ($panelUrl && str_starts_with($panelUrl, 'https://')) {
                $host = parse_url($panelUrl, PHP_URL_HOST);
                $cert = @file_get_contents("https://{$host}/", false, stream_context_create([
                    'ssl' => ['capture_peer_cert' => true, 'verify_peer' => false],
                ]));

                if ($cert !== false) {
                    $certInfo = openssl_x509_parse(stream_context_get_params(
                        stream_context_get_default()
                    )['options']['ssl']['peer_certificate'] ?? null);

                    if ($certInfo && isset($certInfo['validTo_time_t'])) {
                        $daysLeft = floor(($certInfo['validTo_time_t'] - time()) / 86400);
                        if ($daysLeft < 0) {
                            $this->auditFail('SSL certificate', "Panel certificate expired {$daysLeft} days ago");
                        } elseif ($daysLeft < 14) {
                            $this->auditFail('SSL certificate', "Panel certificate expires in {$daysLeft} days");
                        } elseif ($daysLeft < 30) {
                            $this->auditWarn('SSL certificate', "Panel certificate expires in {$daysLeft} days");
                        } else {
                            $this->auditPass('SSL certificate', "Panel certificate expires in {$daysLeft} days");
                        }
                    }
                }
            } else {
                $this->auditWarn('SSL certificate', 'Panel URL is not HTTPS');
            }
        } catch (\Exception $e) {
            $this->auditWarn('SSL certificate', "Cannot check: {$e->getMessage()}");
        }
    }

    private function checkBackupHealth(): void
    {
        $totalBackups = DB::table('backups')->count();
        if ($totalBackups === 0) {
            $this->auditWarn('Backup health', 'No backups have been created');
        } else {
            $recentFailures = DB::table('backups')
                ->where('status', 'failed')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            if ($recentFailures > 0) {
                $this->auditWarn('Backup health', "{$recentFailures} failed backup(s) in the last 7 days");
            } else {
                $this->auditPass('Backup health', "{$totalBackups} total backups, no recent failures");
            }
        }

        $schedules = DB::table('backup_schedules')->count();
        if ($schedules === 0) {
            $this->auditWarn('Backup scheduling', 'No backup schedules configured');
        }
    }

    private function checkEnvSecurity(): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            $this->auditFail('Environment file', '.env not found');

            return;
        }

        if (! $this->option('json')) {
            $sensitiveKeys = ['DB_PASSWORD', 'DAEMON_TOKEN', 'APP_KEY', 'REDIS_PASSWORD'];
            $exposed = [];
            $envContent = file_get_contents($envPath);

            foreach ($sensitiveKeys as $key) {
                if (preg_match("/^{$key}=$/m", $envContent)) {
                    $exposed[] = "{$key} is empty";
                } elseif (preg_match("/^{$key}=['\"]?test|default|password|secret['\"]?$/mi", $envContent)) {
                    $exposed[] = "{$key} uses default value";
                }
            }

            if (empty($exposed)) {
                $this->auditPass('Environment secrets', 'All keys appear to have values');
            } else {
                $this->auditWarn('Environment secrets', implode('; ', $exposed));
            }
        }

        if (app()->environment('production')) {
            $this->auditPass('Environment mode', 'Application is in production mode');
        } elseif (app()->environment('local')) {
            $this->auditWarn('Environment mode', 'Application is in local/development mode');
        }

        if (config('app.debug')) {
            $this->auditFail('Debug mode', 'APP_DEBUG is enabled in production');
        }
    }

    private function checkDatabaseEncryption(): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'mysql') {
            $unencrypted = DB::table('information_schema.tables')
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('CREATE_OPTIONS', 'not like', '%ENCRYPTION="Y"%')
                ->count();

            if ($unencrypted > 0) {
                $this->auditWarn('Database encryption', "{$unencrypted} tables may not use encryption at rest");
            } else {
                $this->auditPass('Database encryption', 'MySQL native encryption appears configured');
            }
        } else {
            $this->auditPass('Database connection', "Using {$driver} driver");
        }
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

        $failOnly = $this->option('fail-only');

        foreach ($this->results as $result) {
            if ($failOnly && $result['status'] === 'PASS') {
                continue;
            }

            $icon = match ($result['status']) {
                'PASS' => "\x1b[32mPASS\x1b[0m",
                'WARN' => "\x1b[33mWARN\x1b[0m",
                'FAIL' => "\x1b[31mFAIL\x1b[0m",
            };

            $this->line("  {$icon}  {$result['check']}");
            if ($result['detail']) {
                $this->line("        {$result['detail']}");
            }
        }

        $this->newLine();
        $this->line(sprintf(
            '  Summary: %d passed, %d warnings, %d failures',
            $this->passed,
            $this->warnings,
            $this->failures
        ));
    }
}

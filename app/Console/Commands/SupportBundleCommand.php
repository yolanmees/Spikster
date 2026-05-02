<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Models\Site;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ZipArchive;

class SupportBundleCommand extends Command
{
    protected $signature = 'spikster:support-bundle
        {--output= : Output path for the bundle ZIP}
        {--no-logs : Exclude log files}
        {--no-env : Exclude .env (will still include sanitized version)}
        {--include-database : Include database dump (SQLite only, skip for MySQL)}';

    protected $description = 'Generate a support bundle ZIP with logs, config, and system info';

    public function handle(): int
    {
        $outputPath = $this->option('output') ?? storage_path('spikster-support-'.now()->format('Ymd-His').'.zip');
        $workDir = storage_path('spikster-bundle-'.now()->timestamp);

        File::ensureDirectoryExists($workDir);
        File::ensureDirectoryExists(dirname($outputPath));

        $this->info('Generating support bundle...');

        $this->writeSystemInfo($workDir);
        $this->writeConfigDump($workDir);
        $this->writeComposerInfo($workDir);
        $this->writeDatabaseStats($workDir);

        if (! $this->option('no-logs')) {
            $this->includeLogs($workDir);
        }

        $this->includeEnv($workDir);

        if ($this->option('include-database') && config('database.default') === 'sqlite') {
            $this->includeSqlite($workDir);
        }

        $this->createZip($workDir, $outputPath);

        File::deleteDirectory($workDir);

        $this->info("Support bundle created: {$outputPath}");
        $this->line('Size: '.$this->formatBytes(filesize($outputPath)));

        return Command::SUCCESS;
    }

    private function writeSystemInfo(string $dir): void
    {
        $info = [
            'Generated At' => now()->toIso8601String(),
            'PHP Version' => PHP_VERSION,
            'Laravel Version' => app()->version(),
            'Environment' => app()->environment(),
            'Debug Mode' => config('app.debug') ? 'Yes' : 'No',
            'Database Driver' => config('database.default'),
            'Queue Driver' => config('queue.default'),
            'Cache Driver' => config('cache.default'),
            'Session Driver' => config('session.driver'),
            'OS' => PHP_OS.' '.php_uname('r'),
            'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI',
            'Total Servers' => Server::count(),
            'Active Servers' => Server::where('status', 1)->count(),
            'Total Sites' => Site::count(),
            'Total Users' => User::count(),
            'PHP Extensions' => implode(', ', get_loaded_extensions()),
            'Disk Free Space' => $this->formatBytes(disk_free_space(base_path())),
            'Disk Total Space' => $this->formatBytes(disk_total_space(base_path())),
            'Memory Limit' => ini_get('memory_limit'),
            'Max Upload Size' => ini_get('upload_max_filesize'),
            'Max Execution Time' => ini_get('max_execution_time'),
        ];
        $yaml = '';
        foreach ($info as $key => $value) {
            $yaml .= "{$key}: {$value}\n";
        }

        File::put($dir.'/system-info.txt', $yaml);
        $this->line('  ✓ System info');
    }

    private function writeConfigDump(string $dir): void
    {
        $sensitive = ['password', 'secret', 'token', 'key', 'hash', 'apikey'];
        $config = [];

        foreach (['app', 'spikster', 'security', 'database', 'mail', 'queue', 'cache', 'session', 'logging', 'filesystems'] as $key) {
            $values = config($key, []);
            if (! is_array($values)) {
                continue;
            }
            $config[$key] = $this->sanitizeArray($values, $sensitive);
        }

        File::put($dir.'/config.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->line('  ✓ Config dump');
    }

    private function writeComposerInfo(string $dir): void
    {
        $composer = json_decode(File::get(base_path('composer.json')), true);
        $lock = json_decode(File::get(base_path('composer.lock')), true);

        $packages = [];
        foreach ($lock['packages'] ?? [] as $pkg) {
            $packages[] = [
                'name' => $pkg['name'],
                'version' => $pkg['version'],
                'license' => $pkg['license'] ?? [],
            ];
        }

        File::put($dir.'/composer-packages.json', json_encode([
            'require' => $composer['require'] ?? [],
            'require-dev' => $composer['require-dev'] ?? [],
            'installed' => $packages,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->line('  ✓ Composer info');
    }

    private function writeDatabaseStats(string $dir): void
    {
        try {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
            $stats = [];
            foreach ($tables as $table) {
                $count = DB::table($table->name)->count();
                $stats[] = "{$table->name}: {$count} rows";
            }
            File::put($dir.'/database-stats.txt', implode("\n", $stats));
            $this->line('  ✓ Database stats');
        } catch (\Throwable) {
            File::put($dir.'/database-stats.txt', 'Could not read database stats');
        }
    }

    private function includeLogs(string $dir): void
    {
        $logDir = storage_path('logs');
        $targetDir = $dir.'/logs';
        File::ensureDirectoryExists($targetDir);

        if (is_dir($logDir)) {
            foreach (File::files($logDir) as $file) {
                if ($file->getExtension() === 'log' && $file->getSize() > 0) {
                    $content = File::get($file->getPathname());
                    $content = preg_replace('/password["\']?\s*[:=]\s*["\'][^"\']+["\']/i', 'password: ***REDACTED***', $content);
                    $content = preg_replace('/token["\']?\s*[:=]\s*["\'][^"\']+["\']/i', 'token: ***REDACTED***', $content);

                    $maxBytes = 500 * 1024;
                    if (strlen($content) > $maxBytes) {
                        $content = substr($content, -$maxBytes);
                        $content = "[...truncated to last {$maxBytes} bytes...]\n".$content;
                    }

                    File::put($targetDir.'/'.$file->getFilename(), $content);
                }
            }
            $this->line('  ✓ Logs');
        }
    }

    private function includeEnv(string $dir): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return;
        }

        $content = File::get($envPath);
        $content = preg_replace('/^(.+?_PASSWORD|SECRET|TOKEN|KEY)=.*$/m', '$1=***REDACTED***', $content);
        $content = preg_replace('/^DAEMON_TOKEN=.*$/m', 'DAEMON_TOKEN=***REDACTED***', $content);
        $content = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=***REDACTED***', $content);

        File::put($dir.'/env.txt', $content);
        $this->line('  ✓ Environment (sanitized)');
    }

    private function includeSqlite(string $dir): void
    {
        $dbPath = database_path('database.sqlite');
        if (file_exists($dbPath)) {
            File::copy($dbPath, $dir.'/database.sqlite');
            $this->line('  ✓ Database (SQLite)');
        }
    }

    private function createZip(string $sourceDir, string $outputPath): void
    {
        $zip = new ZipArchive;
        if ($zip->open($outputPath, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("Cannot create ZIP at {$outputPath}");
        }

        $files = File::allFiles($sourceDir);
        foreach ($files as $file) {
            $relativePath = str_replace($sourceDir.'/', '', $file->getPathname());
            $zip->addFile($file->getPathname(), 'spikster-bundle/'.$relativePath);
        }

        $zip->close();
    }

    private function sanitizeArray(array $data, array $sensitive, string $prefix = ''): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;
            if (is_array($value)) {
                $result[$key] = $this->sanitizeArray($value, $sensitive, $fullKey);
            } else {
                foreach ($sensitive as $s) {
                    if (str_contains(strtolower($key), $s) && $value !== null && $value !== '' && $value !== false) {
                        $value = '***REDACTED***';
                        break;
                    }
                }
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}

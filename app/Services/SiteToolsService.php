<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\Log;

class SiteToolsService
{
    protected DaemonService $daemon;

    public function __construct(DaemonService $daemon)
    {
        $this->daemon = $daemon;
    }

    public function detect(Site $site): array
    {
        $root = $this->getRoot($site);
        $public = $root . '/public';

        $result = [
            'type' => 'unknown',
            'framework' => null,
            'version' => null,
            'artisan_path' => null,
            'has_env' => false,
            'has_queue' => false,
            'has_horizon' => false,
            'has_telescope' => false,
        ];

        // Check artisan
        $artisan = $this->daemon->send('site.exec', [
            'username' => $site->username,
            'command' => "test -f {$root}/artisan && echo 'EXISTS' || echo 'NOT_FOUND'",
        ]);

        if (($artisan['output'] ?? '') === 'EXISTS') {
            $result['type'] = 'laravel';
            $result['artisan_path'] = "{$root}/artisan";

            // Get version
            $ver = $this->daemon->send('site.exec', [
                'username' => $site->username,
                'command' => "cd {$root} && php artisan --version 2>/dev/null",
            ]);
            $result['version'] = trim($ver['output'] ?? '');

            // Check composer.json for framework version
            $comp = $this->daemon->send('site.exec', [
                'username' => $site->username,
                'command' => "cat {$root}/composer.json 2>/dev/null | grep -oP '\"laravel/framework\":\\s*\"[^\"]+\"' || echo ''",
            ]);
            $result['framework'] = trim($comp['output'] ?? '');

            // Check queue config
            $result['has_queue'] = $this->fileExists($site, "{$root}/config/queue.php");
            $result['has_horizon'] = $this->fileExists($site, "{$root}/config/horizon.php");
            $result['has_telescope'] = $this->fileExists($site, "{$root}/config/telescope.php");
            $result['has_env'] = $this->fileExists($site, "{$root}/.env");
            $result['has_schedule'] = $this->hasSchedule($site, $root);
        } else {
            // Check WordPress
            $wp = $this->daemon->send('site.exec', [
                'username' => $site->username,
                'command' => "test -f {$root}/wp-config.php && echo 'WP' || echo ''",
            ]);
            if (trim($wp['output'] ?? '') === 'WP') {
                $result['type'] = 'wordpress';
                $result['framework'] = 'WordPress';
            }
        }

        return $result;
    }

    public function artisan(Site $site, string $command): array
    {
        $root = dirname($site->basepath);
        if (! str_ends_with($root, '/public')) {
            $root = $site->basepath;
        }

        // Strip "php artisan " prefix if included
        $cmd = preg_replace('/^php\s+artisan\s*/i', '', $command);
        $fullCmd = "cd {$root} && php artisan {$cmd} 2>&1";

        return $this->daemon->send('site.exec', [
            'username' => $site->username,
            'command' => $fullCmd,
        ]);
    }

    public function readEnv(Site $site): ?string
    {
        $root = dirname($site->basepath);
        $result = $this->daemon->send('site.read-file', [
            'username' => $site->username,
            'path' => "{$root}/.env",
        ]);
        return $result['content'] ?? null;
    }

    public function writeEnv(Site $site, string $content): bool
    {
        $root = dirname($site->basepath);
        $result = $this->daemon->send('site.write-file', [
            'username' => $site->username,
            'path' => "{$root}/.env",
            'content' => $content,
        ]);
        return ($result['success'] ?? false);
    }

    public function tailLog(Site $site, int $lines = 50): array
    {
        $root = dirname($site->basepath);
        $result = $this->daemon->send('site.exec', [
            'username' => $site->username,
            'command' => "tail -{$lines} {$root}/storage/logs/laravel.log 2>/dev/null || echo 'No log file found'",
        ]);
        return [
            'content' => $result['output'] ?? 'Unable to read log',
            'path' => "{$root}/storage/logs/laravel.log",
        ];
    }

    public function maintenanceMode(Site $site, bool $down, string $secret = ''): array
    {
        $root = dirname($site->basepath);
        $cmd = $down
            ? ($secret ? "cd {$root} && php artisan down --secret={$secret} 2>&1" : "cd {$root} && php artisan down 2>&1")
            : "cd {$root} && php artisan up 2>&1";

        return $this->daemon->send('site.exec', [
            'username' => $site->username,
            'command' => $cmd,
        ]);
    }

    public function queueStatus(Site $site): string
    {
        $root = dirname($site->basepath);
        $result = $this->daemon->send('site.exec', [
            'username' => $site->username,
            'command' => "cd {$root} && php artisan queue:monitor 2>&1 | head -10",
        ]);
        return $result['output'] ?? 'No queue information available';
    }

    public function scheduleList(Site $site): string
    {
        $root = dirname($site->basepath);
        $result = $this->daemon->send('site.exec', [
            'username' => $site->username,
            'command' => "cd {$root} && php artisan schedule:list 2>&1",
        ]);
        return $result['output'] ?? 'No scheduled tasks';
    }

    protected function getRoot(Site $site): string
    {
        $root = $site->basepath;
        if (str_ends_with($root, '/public')) {
            $root = dirname($root);
        }
        return rtrim($root, '/');
    }

    protected function fileExists(Site $site, string $path): bool
    {
        $result = $this->daemon->send('site.exec', [
            'username' => $site->username,
            'command' => "test -f {$path} && echo '1' || echo '0'",
        ]);
        return trim($result['output'] ?? '') === '1';
    }

    protected function hasSchedule(Site $site, string $root): bool
    {
        $result = $this->daemon->send('site.exec', [
            'username' => $site->username,
            'command' => "cd {$root} && php artisan schedule:list 2>&1 | head -5 || true",
        ]);
        return ! empty(trim($result['output'] ?? ''));
    }
}

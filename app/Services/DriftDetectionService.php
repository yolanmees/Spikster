<?php

namespace App\Services;

use App\Models\Server;
use App\Services\RemoteDaemonService;

class DriftDetectionService
{
    public function __construct(
        protected RemoteDaemonService $daemon
    ) {}

    public function detect(Server $server): array
    {
        $drift = [];

        try {
            $installed = $this->daemon->send($server, 'server.package-list', []);
            $expected = ['nginx', 'mysql', 'php8.3-fpm', 'redis', 'supervisor', 'fail2ban'];

            foreach ($expected as $pkg) {
                $installedList = $installed['output'] ?? '';
                if (! str_contains($installedList, $pkg)) {
                    $drift[] = [
                        'type' => 'missing_package',
                        'expected' => $pkg,
                        'actual' => 'not installed',
                    ];
                }
            }

            $configFiles = [
                '/etc/nginx/sites-enabled/',
                '/etc/php/',
                '/etc/mysql/',
            ];

            foreach ($configFiles as $path) {
                if (! file_exists($path)) {
                    $drift[] = [
                        'type' => 'missing_config',
                        'path' => $path,
                        'severity' => 'high',
                    ];
                }
            }

        } catch (\Throwable $e) {
            $drift[] = [
                'type' => 'agent_unreachable',
                'message' => $e->getMessage(),
                'severity' => 'critical',
            ];
        }

        return [
            'server_id' => $server->server_id,
            'server_name' => $server->name,
            'drift_count' => count($drift),
            'drifts' => $drift,
            'is_consistent' => empty($drift),
            'checked_at' => now()->toIso8601String(),
        ];
    }

    public function detectAll(): array
    {
        $results = [];
        foreach (Server::active()->get() as $server) {
            $results[$server->server_id] = $this->detect($server);
        }
        return $results;
    }
}

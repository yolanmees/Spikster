<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\AlertingService;
use App\Services\MonitoringService;
use Illuminate\Console\Command;

class MonitoringCheckCommand extends Command
{
    protected $signature = 'spikster:monitor-check
        {--server= : Check a specific server by ID}
        {--alert : Send alerts for threshold breaches}';

    protected $description = 'Check server health metrics and optionally send alerts';

    public function handle(MonitoringService $monitoringService, AlertingService $alertingService): int
    {
        $serverId = $this->option('server');
        $sendAlerts = $this->option('alert');

        $servers = $serverId
            ? Server::where('server_id', $serverId)->get()
            : Server::active()->get();

        if ($servers->isEmpty()) {
            $this->warn('No servers found.');
            return 0;
        }

        $this->info("Checking {$servers->count()} server(s)...");

        foreach ($servers as $server) {
            $health = $monitoringService->checkServerHealth($server);
            $status = $health['status'];
            $icon = match ($status) {
                'healthy' => '✓',
                'warning' => '!',
                'critical' => '✗',
                default => '?',
            };
            $this->line("  {$icon} {$server->name} ({$server->ip}): {$status}");

            if ($sendAlerts && $status !== 'healthy') {
                $alertResult = $alertingService->checkAndAlert($server);
                if ($alertResult['alerts_sent']) {
                    $this->line("     → Alerts sent ({$alertResult['status']})");
                }
            }
        }

        $this->info('Done.');

        return 0;
    }
}

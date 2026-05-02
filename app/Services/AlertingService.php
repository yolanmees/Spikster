<?php

namespace App\Services;

use App\Models\Server;
use App\Models\ServerMetric;
use App\Models\Webhook;
use Illuminate\Support\Facades\Log;

class AlertingService
{
    public function __construct(
        protected MonitoringService $monitoringService,
        protected WebhookService $webhookService
    ) {}

    public function checkAndAlert(Server $server): array
    {
        $issues = [];
        $metric = ServerMetric::getLatestForServer($server->server_id);

        if (! $metric) {
            return ['status' => 'unknown', 'alerts_sent' => false, 'issues' => []];
        }

        $thresholds = config('monitoring.thresholds', []);
        $alerts = [];

        if ($this->thresholdExceeded($metric->cpu_percent, $thresholds['cpu']['critical'] ?? 90)) {
            $alerts[] = ['type' => 'cpu', 'severity' => 'critical', 'value' => $metric->cpu_percent, 'message' => "CPU usage critical: {$metric->cpu_percent}%"];
        } elseif ($this->thresholdExceeded($metric->cpu_percent, $thresholds['cpu']['warning'] ?? 70)) {
            $alerts[] = ['type' => 'cpu', 'severity' => 'warning', 'value' => $metric->cpu_percent, 'message' => "CPU usage high: {$metric->cpu_percent}%"];
        }

        if ($this->thresholdExceeded($metric->memory_percent, $thresholds['memory']['critical'] ?? 95)) {
            $alerts[] = ['type' => 'memory', 'severity' => 'critical', 'value' => $metric->memory_percent, 'message' => "Memory usage critical: {$metric->memory_percent}%"];
        } elseif ($this->thresholdExceeded($metric->memory_percent, $thresholds['memory']['warning'] ?? 80)) {
            $alerts[] = ['type' => 'memory', 'severity' => 'warning', 'value' => $metric->memory_percent, 'message' => "Memory usage high: {$metric->memory_percent}%"];
        }

        if ($this->thresholdExceeded($metric->disk_percent, $thresholds['disk']['critical'] ?? 90)) {
            $alerts[] = ['type' => 'disk', 'severity' => 'critical', 'value' => $metric->disk_percent, 'message' => "Disk usage critical: {$metric->disk_percent}%"];
        } elseif ($this->thresholdExceeded($metric->disk_percent, $thresholds['disk']['warning'] ?? 80)) {
            $alerts[] = ['type' => 'disk', 'severity' => 'warning', 'value' => $metric->disk_percent, 'message' => "Disk usage high: {$metric->disk_percent}%"];
        }

        if (empty($alerts)) {
            return ['status' => 'healthy', 'alerts_sent' => false, 'issues' => []];
        }

        $this->sendAlerts($server, $alerts);

        return [
            'status' => collect($alerts)->contains(fn ($a) => $a['severity'] === 'critical') ? 'critical' : 'warning',
            'alerts_sent' => true,
            'issues' => $alerts,
        ];
    }

    public function checkAllServers(): array
    {
        $results = [];
        foreach (Server::active()->get() as $server) {
            $results[$server->server_id] = $this->checkAndAlert($server);
        }
        return $results;
    }

    protected function thresholdExceeded(float $value, int $threshold): bool
    {
        return $value >= $threshold;
    }

    protected function sendAlerts(Server $server, array $alerts): void
    {
        $severity = collect($alerts)->contains(fn ($a) => $a['severity'] === 'critical') ? 'critical' : 'warning';

        $this->webhookService->dispatch('monitoring.alert', [
            'server_id' => $server->server_id,
            'server_name' => $server->name,
            'server_ip' => $server->ip,
            'severity' => $severity,
            'alerts' => $alerts,
            'checked_at' => now()->toIso8601String(),
        ]);

        Log::warning("Server {$server->name} ({$server->ip}) triggered {$severity} alert", [
            'server_id' => $server->server_id,
            'alerts' => $alerts,
        ]);
    }
}

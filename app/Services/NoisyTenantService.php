<?php

namespace App\Services;

use App\Models\Server;
use App\Models\ServerMetric;

class NoisyTenantService
{
    public function getResourceUsage(Server $server): array
    {
        $agg = ServerMetric::forServer($server->id)
            ->lastHours(24)
            ->selectRaw('AVG(cpu_percent) as avg_cpu, MAX(cpu_percent) as max_cpu, AVG(memory_percent) as avg_memory, MAX(memory_percent) as max_memory, AVG(disk_percent) as avg_disk')
            ->first();

        if (! $agg || $agg->avg_cpu === null) {
            return [];
        }

        return [
            'server_id' => $server->server_id,
            'server_name' => $server->name,
            'avg_cpu' => round((float) $agg->avg_cpu, 2),
            'max_cpu' => round((float) $agg->max_cpu, 2),
            'avg_memory' => round((float) $agg->avg_memory, 2),
            'max_memory' => round((float) $agg->max_memory, 2),
            'avg_disk' => round((float) $agg->avg_disk, 2),
            'sites_count' => $server->sites_count ?? $server->sites()->count(),
        ];
    }

    public function getNoisyTenants(): array
    {
        $thresholds = config('spikster.noisy_tenant_thresholds', [
            'cpu' => 80,
            'memory' => 85,
            'disk' => 90,
        ]);

        $noisy = [];

        foreach (Server::active()->get() as $server) {
            $usage = $this->getResourceUsage($server);

            if (empty($usage)) {
                continue;
            }

            $reasons = [];
            if ($usage['avg_cpu'] > $thresholds['cpu']) {
                $reasons[] = "CPU avg {$usage['avg_cpu']}% > {$thresholds['cpu']}%";
            }
            if ($usage['avg_memory'] > $thresholds['memory']) {
                $reasons[] = "Memory avg {$usage['avg_memory']}% > {$thresholds['memory']}%";
            }
            if ($usage['avg_disk'] > $thresholds['disk']) {
                $reasons[] = "Disk avg {$usage['avg_disk']}% > {$thresholds['disk']}%";
            }

            if (! empty($reasons)) {
                $usage['reasons'] = $reasons;
                $noisy[] = $usage;
            }
        }

        return $noisy;
    }

    public function getCapacityPlan(): array
    {
        $servers = Server::active()->withCount('sites')->get();
        $serverIds = $servers->pluck('id');
        $totalSites = $servers->sum('sites_count');

        $latestMetrics = ServerMetric::whereIn('server_id', $serverIds)
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('server_metrics')->groupBy('server_id');
            })
            ->get()
            ->keyBy('server_id');

        $totalCpu = 0;
        $totalMemory = 0;
        $totalDisk = 0;

        foreach ($servers as $server) {
            $metric = $latestMetrics->get($server->id);
            if ($metric) {
                $totalCpu += $metric->cpu_percent;
                $totalMemory += $metric->memory_percent;
                $totalDisk += $metric->disk_percent;
            }
        }

        $serverCount = $servers->count();

        return [
            'server_count' => $serverCount,
            'total_sites' => $totalSites,
            'avg_cpu' => $serverCount > 0 ? round($totalCpu / $serverCount, 2) : 0,
            'avg_memory' => $serverCount > 0 ? round($totalMemory / $serverCount, 2) : 0,
            'avg_disk' => $serverCount > 0 ? round($totalDisk / $serverCount, 2) : 0,
            'sites_per_server' => $serverCount > 0 ? round($totalSites / $serverCount, 2) : 0,
            'recommendation' => $this->getRecommendation($serverCount, $totalSites, $totalCpu / max($serverCount, 1)),
        ];
    }

    protected function getRecommendation(int $serverCount, int $totalSites, float $avgCpu): string
    {
        if ($avgCpu > 80) {
            return 'CRITICAL: Average CPU exceeds 80%. Add more servers or redistribute sites.';
        }
        if ($avgCpu > 60) {
            return 'WARNING: Average CPU at ' . round($avgCpu, 0) . '%. Plan for additional capacity soon.';
        }
        if ($serverCount === 0) {
            return 'No servers configured. Add at least one server to host sites.';
        }
        return 'OK: Resource usage within normal ranges.';
    }
}

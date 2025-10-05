<?php

namespace App\Services;

use App\Models\Server;
use App\Models\ServerMetric;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MonitoringService
{
    /**
     * Get the latest metrics for a server.
     * 
     * Returns cached metrics if agent is unreachable and caching is enabled.
     */
    public function getLatestMetrics(Server $server): ?array
    {
        $cacheKey = "server_metrics_{$server->id}";
        
        // Try to get from database first
        $metric = ServerMetric::getLatestForServer($server->id);
        
        if ($metric) {
            $data = $this->formatMetricForDisplay($metric);
            
            // Cache for 2 minutes
            Cache::put($cacheKey, $data, 120);
            
            return $data;
        }
        
        // Fallback to cached if enabled
        if (config('monitoring.use_cached_metrics', true)) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                Log::info("Using cached metrics for server {$server->id}");
                return array_merge($cached, ['cached' => true]);
            }
        }
        
        return null;
    }

    /**
     * Get metrics for a specific time period.
     */
    public function getMetricsForPeriod(Server $server, int $hours = 24): array
    {
        return ServerMetric::forServer($server->id)
            ->lastHours($hours)
            ->orderBy('measured_at', 'asc')
            ->get()
            ->map(fn($m) => $this->formatMetricForDisplay($m))
            ->toArray();
    }

    /**
     * Get aggregated metrics for charts.
     */
    public function getChartData(Server $server, int $hours = 24): array
    {
        return ServerMetric::getTimeSeriesData($server->id, $hours);
    }

    /**
     * Get aggregated statistics for a server.
     */
    public function getAggregatedStats(Server $server, int $hours = 24): array
    {
        return ServerMetric::getAggregatedMetrics($server->id, $hours);
    }

    /**
     * Check if a server is healthy based on latest metrics.
     */
    public function checkServerHealth(Server $server): array
    {
        $metric = ServerMetric::getLatestForServer($server->id);
        
        if (!$metric) {
            return [
                'status' => 'unknown',
                'message' => 'No metrics available',
                'issues' => [],
            ];
        }

        $issues = [];
        $thresholds = config('monitoring.thresholds', []);

        // Check CPU
        if (isset($thresholds['cpu']['critical']) && $metric->cpu_percent >= $thresholds['cpu']['critical']) {
            $issues[] = "CPU usage critical: {$metric->cpu_percent}%";
        } elseif (isset($thresholds['cpu']['warning']) && $metric->cpu_percent >= $thresholds['cpu']['warning']) {
            $issues[] = "CPU usage high: {$metric->cpu_percent}%";
        }

        // Check Memory
        if (isset($thresholds['memory']['critical']) && $metric->memory_percent >= $thresholds['memory']['critical']) {
            $issues[] = "Memory usage critical: {$metric->memory_percent}%";
        } elseif (isset($thresholds['memory']['warning']) && $metric->memory_percent >= $thresholds['memory']['warning']) {
            $issues[] = "Memory usage high: {$metric->memory_percent}%";
        }

        // Check Disk
        if (isset($thresholds['disk']['critical']) && $metric->disk_percent >= $thresholds['disk']['critical']) {
            $issues[] = "Disk usage critical: {$metric->disk_percent}%";
        } elseif (isset($thresholds['disk']['warning']) && $metric->disk_percent >= $thresholds['disk']['warning']) {
            $issues[] = "Disk usage high: {$metric->disk_percent}%";
        }

        if (empty($issues)) {
            return [
                'status' => 'healthy',
                'message' => 'All systems normal',
                'issues' => [],
            ];
        }

        // Determine overall status
        $hasCritical = collect($issues)->contains(fn($i) => str_contains($i, 'critical'));
        
        return [
            'status' => $hasCritical ? 'critical' : 'warning',
            'message' => count($issues) . ' issue(s) detected',
            'issues' => $issues,
        ];
    }

    /**
     * Test connection to the monitoring agent.
     */
    public function testAgentConnection(Server $server): array
    {
        $port = config('monitoring.agent_port', 9273);
        $host = $server->ip ?? $server->domain;
        $healthUrl = "http://{$host}:{$port}/health";
        
        try {
            $response = Http::timeout(5)->get($healthUrl);
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Agent is running',
                    'data' => $data,
                ];
            }
            
            return [
                'success' => false,
                'message' => "Agent returned status {$response->status()}",
                'data' => null,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Connection failed: {$e->getMessage()}",
                'data' => null,
            ];
        }
    }

    /**
     * Format a ServerMetric for display.
     */
    private function formatMetricForDisplay(ServerMetric $metric): array
    {
        return [
            'timestamp' => $metric->measured_at->toISOString(),
            'measured_at' => $metric->measured_at->diffForHumans(),
            
            'cpu' => [
                'percent' => $metric->cpu_percent,
                'cores' => $metric->cpu_cores,
                'status' => $this->getMetricStatus($metric->cpu_percent, 'cpu'),
            ],
            
            'memory' => [
                'total' => $metric->memory_total,
                'used' => $metric->memory_used,
                'free' => $metric->memory_free,
                'available' => $metric->memory_available,
                'percent' => $metric->memory_percent,
                'cached' => $metric->memory_cached,
                'buffers' => $metric->memory_buffers,
                'formatted' => $metric->formatted_memory,
                'status' => $this->getMetricStatus($metric->memory_percent, 'memory'),
            ],
            
            'disk' => [
                'total' => $metric->disk_total,
                'used' => $metric->disk_used,
                'free' => $metric->disk_free,
                'percent' => $metric->disk_percent,
                'formatted' => $metric->formatted_disk,
                'status' => $this->getMetricStatus($metric->disk_percent, 'disk'),
            ],
            
            'load' => [
                '1' => $metric->load_1,
                '5' => $metric->load_5,
                '15' => $metric->load_15,
            ],
            
            'network' => [
                'bytes_sent' => $metric->network_bytes_sent,
                'bytes_recv' => $metric->network_bytes_recv,
                'packets_sent' => $metric->network_packets_sent,
                'packets_recv' => $metric->network_packets_recv,
            ],
            
            'uptime' => [
                'seconds' => $metric->uptime_seconds,
                'formatted' => $metric->formatted_uptime,
            ],
        ];
    }

    /**
     * Get the status level for a metric value.
     */
    private function getMetricStatus(float $value, string $type): string
    {
        $thresholds = config("monitoring.thresholds.{$type}", []);
        
        if (isset($thresholds['critical']) && $value >= $thresholds['critical']) {
            return 'critical';
        }
        
        if (isset($thresholds['warning']) && $value >= $thresholds['warning']) {
            return 'warning';
        }
        
        return 'normal';
    }

    /**
     * Get color for a status badge.
     */
    public function getStatusColor(string $status): string
    {
        return match($status) {
            'critical' => 'red',
            'warning' => 'yellow',
            'healthy', 'normal' => 'green',
            default => 'gray',
        };
    }
}

<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChaosTestingService
{
    protected array $scenarios = [];

    public function __construct()
    {
        $this->scenarios = [
            'agent_unreachable' => 'Simulate monitoring agent being down',
            'queue_failure' => 'Simulate queue job processing failure',
            'database_timeout' => 'Simulate database connection timeout',
            'high_cpu_load' => 'Simulate high CPU load alert',
        ];
    }

    public function getScenarios(): array
    {
        return $this->scenarios;
    }

    public function run(string $scenario, ?Server $server = null): array
    {
        Log::warning("Chaos test running: {$scenario}", ['server' => $server?->server_id]);

        return match ($scenario) {
            'agent_unreachable' => $this->simulateAgentUnreachable($server),
            'queue_failure' => $this->simulateQueueFailure(),
            'database_timeout' => $this->simulateDatabaseTimeout(),
            'high_cpu_load' => $this->simulateHighCpuLoad($server),
            default => throw new \InvalidArgumentException("Unknown scenario: {$scenario}"),
        };
    }

    protected function simulateAgentUnreachable(?Server $server): array
    {
        if ($server) {
            Cache::put("agent_down_{$server->server_id}", true, 300);
            Cache::put("server_metrics_{$server->id}", null, 300);
        }

        return [
            'scenario' => 'agent_unreachable',
            'status' => 'simulated',
            'message' => 'Monitoring agent marked as unreachable for 5 minutes',
            'expected_behavior' => 'AlertingService should detect missing metrics and log warning',
            'recovery' => 'System should recover automatically after cache expires',
        ];
    }

    protected function simulateQueueFailure(): array
    {
        Cache::put('chaos_queue_failure', true, 60);

        return [
            'scenario' => 'queue_failure',
            'status' => 'simulated',
            'message' => 'Queue jobs will fail for 60 seconds',
            'expected_behavior' => 'Jobs should be retried according to their backoff configuration',
            'recovery' => 'Failed jobs should appear in failed_jobs table for review',
        ];
    }

    protected function simulateDatabaseTimeout(): array
    {
        Cache::put('chaos_db_timeout', true, 30);

        return [
            'scenario' => 'database_timeout',
            'status' => 'simulated',
            'message' => 'Database queries will be delayed for 30 seconds',
            'expected_behavior' => 'Application should handle timeouts gracefully, not crash',
            'recovery' => 'Normal operation resumes after cache expires',
        ];
    }

    protected function simulateHighCpuLoad(?Server $server): array
    {
        if ($server) {
            // Inject a fake metric with high CPU
            \App\Models\ServerMetric::create([
                'server_id' => $server->server_id,
                'cpu_percent' => 99.9,
                'cpu_cores' => 1,
                'memory_total' => 1024,
                'memory_used' => 512,
                'memory_free' => 512,
                'memory_percent' => 50,
                'disk_total' => 1024,
                'disk_used' => 614,
                'disk_free' => 410,
                'disk_percent' => 60,
                'load_1' => 12.5,
                'load_5' => 6.0,
                'load_15' => 3.0,
                'network_bytes_sent' => 1000,
                'network_bytes_recv' => 2000,
                'uptime_seconds' => 86400,
                'measured_at' => now(),
            ]);
        }

        return [
            'scenario' => 'high_cpu_load',
            'status' => 'simulated',
            'message' => 'High CPU metric injected' . ($server ? " for {$server->name}" : ''),
            'expected_behavior' => 'AlertingService should detect threshold breach and send alert',
            'recovery' => 'Next normal metric collection will clear the alert',
        ];
    }

    public function cleanup(string $scenario): void
    {
        match ($scenario) {
            'agent_unreachable' => Cache::forget('chaos_agent_down'),
            'queue_failure' => Cache::forget('chaos_queue_failure'),
            'database_timeout' => Cache::forget('chaos_db_timeout'),
            default => null,
        };

        Log::info("Chaos test cleanup: {$scenario}");
    }
}

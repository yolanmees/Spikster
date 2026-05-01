<?php

namespace App\Jobs;

use App\Models\Server;
use App\Models\ServerMetric;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchServerMetricsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The server to fetch metrics for.
     */
    public Server $server;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 10;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 30;

    /**
     * The unique ID of the job.
     * This prevents duplicate jobs for the same server within a 1-minute window.
     */
    public function uniqueId(): string
    {
        return 'fetch-metrics-'.$this->server->id;
    }

    /**
     * The number of seconds after which the job's unique lock will be released.
     * This should match or exceed your scheduling interval (1 minute).
     */
    public int $uniqueFor = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Skip if server is not active
        if (! $this->isServerActive()) {
            Log::info("Skipping metrics for inactive server: {$this->server->id}");

            return;
        }

        // Rate limiting: don't fetch if we already have fresh metrics (< 50 seconds old)
        $cacheKey = "last_metrics_fetch_{$this->server->id}";
        if (Cache::has($cacheKey)) {
            Log::debug("Skipping duplicate metrics fetch for server {$this->server->id} - already fetched recently");

            return;
        }

        try {
            // Fetch metrics from the agent
            $metrics = $this->fetchMetricsFromAgent();

            if (! $metrics) {
                Log::warning("No metrics received from server: {$this->server->id}");

                return;
            }

            // Store metrics in database
            $this->storeMetrics($metrics);

            // Set cache to prevent duplicate fetches for 50 seconds
            Cache::put($cacheKey, true, 50);

            // Cleanup old metrics (every 100th run to avoid overhead)
            if (rand(1, 100) === 1) {
                $this->cleanupOldMetrics();
            }

        } catch (\Exception $e) {
            Log::error("Failed to fetch metrics for server {$this->server->id}: {$e->getMessage()}", [
                'server' => $this->server->id,
                'exception' => $e,
            ]);

            // Don't fail the job - we'll try again next time
            // This prevents filling the failed_jobs table
        }
    }

    /**
     * Check if the server is active and should be monitored.
     */
    private function isServerActive(): bool
    {
        // Add your logic here - check if server should be monitored
        // For example, check if server has monitoring enabled
        return true; // For now, monitor all servers
    }

    /**
     * Fetch metrics from the spikster-agent on the server.
     */
    private function fetchMetricsFromAgent(): ?array
    {
        $agentUrl = $this->getAgentUrl();

        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->retry(2, 1000)
                ->get($agentUrl);

            if (! $response->successful()) {
                Log::warning("Agent returned non-200 status: {$response->status()}", [
                    'server' => $this->server->id,
                    'url' => $agentUrl,
                ]);

                return null;
            }

            $data = $response->json();

            if (! $this->validateMetricsData($data)) {
                Log::error('Invalid metrics data received from agent', [
                    'server' => $this->server->id,
                    'data' => $data,
                ]);

                return null;
            }

            return $data;

        } catch (\Exception $e) {
            Log::error("Failed to connect to agent: {$e->getMessage()}", [
                'server' => $this->server->id,
                'url' => $agentUrl,
            ]);

            return null;
        }
    }

    /**
     * Get the agent URL for this server.
     */
    private function getAgentUrl(): string
    {
        // Default port for spikster-agent
        $port = 9273;

        // Use server IP - agent listens on localhost but we connect via server IP
        $host = $this->server->ip ?? $this->server->domain;

        return "http://{$host}:{$port}/metrics";
    }

    /**
     * Validate the metrics data structure.
     */
    private function validateMetricsData(array $data): bool
    {
        $required = ['cpu_percent', 'memory', 'disk', 'load', 'network', 'uptime_seconds', 'timestamp'];

        foreach ($required as $field) {
            if (! isset($data[$field])) {
                Log::error("Missing required field in metrics: {$field}");

                return false;
            }
        }

        return true;
    }

    /**
     * Store the metrics in the database.
     */
    private function storeMetrics(array $data): void
    {
        $memory = $data['memory'];
        $disk = $data['disk'];
        $load = $data['load'];
        $network = $data['network'];

        ServerMetric::create([
            'server_id' => $this->server->id,

            // CPU
            'cpu_percent' => $data['cpu_percent'] ?? 0,
            'cpu_cores' => $data['cpu_cores'] ?? null,

            // Memory (agent returns *_bytes fields)
            'memory_total' => $memory['total_bytes'] ?? 0,
            'memory_used' => $memory['used_bytes'] ?? 0,
            'memory_free' => $memory['free_bytes'] ?? 0,
            'memory_available' => $memory['available_bytes'] ?? null,
            'memory_percent' => $memory['percent'] ?? 0,
            'memory_cached' => $memory['cached_bytes'] ?? null,
            'memory_buffers' => $memory['buffers_bytes'] ?? null,

            // Disk (agent returns *_bytes fields)
            'disk_total' => $disk['total_bytes'] ?? 0,
            'disk_used' => $disk['used_bytes'] ?? 0,
            'disk_free' => $disk['free_bytes'] ?? 0,
            'disk_percent' => $disk['percent'] ?? 0,

            // Load (agent returns load1, load5, load15)
            'load_1' => $load['load1'] ?? 0,
            'load_5' => $load['load5'] ?? 0,
            'load_15' => $load['load15'] ?? 0,

            // Network
            'network_bytes_sent' => $network['bytes_sent'] ?? 0,
            'network_bytes_recv' => $network['bytes_recv'] ?? 0,
            'network_packets_sent' => $network['packets_sent'] ?? null,
            'network_packets_recv' => $network['packets_recv'] ?? null,

            // System (agent returns uptime_seconds)
            'uptime_seconds' => $data['uptime_seconds'] ?? 0,

            // Timestamp from agent
            'measured_at' => isset($data['timestamp'])
                ? Carbon::parse($data['timestamp'])
                : now(),
        ]);

        Log::info("Stored metrics for server: {$this->server->id}");
    }

    /**
     * Cleanup old metrics beyond retention period.
     */
    private function cleanupOldMetrics(): void
    {
        try {
            $daysToKeep = config('monitoring.metrics_retention_days', 30);
            $deleted = ServerMetric::cleanupForServer($this->server->id, $daysToKeep);

            if ($deleted > 0) {
                Log::info("Cleaned up {$deleted} old metrics for server: {$this->server->id}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to cleanup old metrics: {$e->getMessage()}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("FetchServerMetricsJob failed permanently for server {$this->server->id}", [
            'server' => $this->server->id,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

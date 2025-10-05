<?php

namespace App\Jobs;

use App\Models\Server;
use App\Models\ServerMetric;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FetchServerMetricsJob implements ShouldQueue
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
        if (!$this->isServerActive()) {
            Log::info("Skipping metrics for inactive server: {$this->server->id}");
            return;
        }

        try {
            // Fetch metrics from the agent
            $metrics = $this->fetchMetricsFromAgent();
            
            if (!$metrics) {
                Log::warning("No metrics received from server: {$this->server->id}");
                return;
            }

            // Store metrics in database
            $this->storeMetrics($metrics);

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

            if (!$response->successful()) {
                Log::warning("Agent returned non-200 status: {$response->status()}", [
                    'server' => $this->server->id,
                    'url' => $agentUrl,
                ]);
                return null;
            }

            $data = $response->json();

            if (!$this->validateMetricsData($data)) {
                Log::error("Invalid metrics data received from agent", [
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
        $required = ['cpu', 'memory', 'disk', 'load', 'network', 'uptime', 'timestamp'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
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
        $cpu = $data['cpu'];
        $memory = $data['memory'];
        $disk = $data['disk'];
        $load = $data['load'];
        $network = $data['network'];

        ServerMetric::create([
            'server_id' => $this->server->id,
            
            // CPU
            'cpu_percent' => $cpu['percent'] ?? 0,
            'cpu_cores' => $cpu['cores'] ?? null,
            
            // Memory
            'memory_total' => $memory['total'] ?? 0,
            'memory_used' => $memory['used'] ?? 0,
            'memory_free' => $memory['free'] ?? 0,
            'memory_available' => $memory['available'] ?? null,
            'memory_percent' => $memory['percent'] ?? 0,
            'memory_cached' => $memory['cached'] ?? null,
            'memory_buffers' => $memory['buffers'] ?? null,
            
            // Disk
            'disk_total' => $disk['total'] ?? 0,
            'disk_used' => $disk['used'] ?? 0,
            'disk_free' => $disk['free'] ?? 0,
            'disk_percent' => $disk['percent'] ?? 0,
            
            // Load
            'load_1' => $load['1'] ?? 0,
            'load_5' => $load['5'] ?? 0,
            'load_15' => $load['15'] ?? 0,
            
            // Network
            'network_bytes_sent' => $network['bytes_sent'] ?? 0,
            'network_bytes_recv' => $network['bytes_recv'] ?? 0,
            'network_packets_sent' => $network['packets_sent'] ?? null,
            'network_packets_recv' => $network['packets_recv'] ?? null,
            
            // System
            'uptime_seconds' => $data['uptime'] ?? 0,
            
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

<?php

namespace Tests\Unit;

use App\Models\Server;
use App\Models\ServerMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerMetricTest extends TestCase
{
    use RefreshDatabase;

    public function test_metric_belongs_to_server_by_uuid(): void
    {
        $server = Server::factory()->create();
        $metric = ServerMetric::factory()->create(['server_id' => $server->server_id]);

        $this->assertInstanceOf(Server::class, $metric->server);
        $this->assertEquals($server->server_id, $metric->server->server_id);
    }

    public function test_server_has_many_metrics_by_uuid(): void
    {
        $server = Server::factory()->create();
        ServerMetric::factory(3)->create(['server_id' => $server->server_id]);

        $this->assertEquals(3, $server->metrics()->count());
    }

    public function test_latest_metric_scope(): void
    {
        $server = Server::factory()->create();
        ServerMetric::factory()->create([
            'server_id' => $server->server_id,
            'measured_at' => now()->subHours(2),
        ]);
        $latest = ServerMetric::factory()->create([
            'server_id' => $server->server_id,
            'measured_at' => now(),
        ]);

        $result = ServerMetric::forServer($server->server_id)->latest()->first();
        $this->assertEquals($latest->id, $result->id);
    }

    public function test_cleanup_old_metrics(): void
    {
        $server = Server::factory()->create();
        ServerMetric::factory()->create([
            'server_id' => $server->server_id,
            'created_at' => now()->subDays(60),
        ]);
        ServerMetric::factory()->create([
            'server_id' => $server->server_id,
            'created_at' => now(),
        ]);

        $deleted = ServerMetric::cleanupOldMetrics(30);
        $this->assertEquals(1, $deleted);
        $this->assertEquals(1, ServerMetric::count());
    }

    public function test_get_latest_for_server(): void
    {
        $server = Server::factory()->create();
        $metric = ServerMetric::factory()->create([
            'server_id' => $server->server_id,
            'measured_at' => now(),
        ]);

        $result = ServerMetric::getLatestForServer($server->server_id);
        $this->assertEquals($metric->id, $result->id);
    }

    public function test_get_aggregated_metrics(): void
    {
        $server = Server::factory()->create();
        ServerMetric::factory(5)->create([
            'server_id' => $server->server_id,
            'cpu_percent' => 50,
            'memory_percent' => 60,
            'disk_percent' => 70,
            'measured_at' => now()->subMinutes(rand(1, 60)),
        ]);

        $aggregated = ServerMetric::getAggregatedMetrics($server->server_id, 24);
        $this->assertArrayHasKey('cpu', $aggregated);
        $this->assertArrayHasKey('memory', $aggregated);
        $this->assertArrayHasKey('disk', $aggregated);
        $this->assertEquals(50, $aggregated['cpu']['avg']);
    }

    public function test_format_bytes(): void
    {
        $metric = new ServerMetric;
        $this->assertEquals('1 KB', $metric->formatBytes(1024));
        $this->assertEquals('1 MB', $metric->formatBytes(1024 * 1024));
        $this->assertEquals('1 GB', $metric->formatBytes(1024 * 1024 * 1024));
    }

    public function test_formatted_uptime(): void
    {
        $metric = new ServerMetric(['uptime_seconds' => 90061]);
        $this->assertEquals('1d 1h 1m', $metric->formatted_uptime);
    }
}

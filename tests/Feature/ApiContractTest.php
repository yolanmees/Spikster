<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiContractTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->user = User::factory()->create();
        $this->user->assignRole('Super Admin');
        $this->headers = [
            'Authorization' => 'Bearer '.$this->user->createToken('test')->plainTextToken,
        ];
    }

    #[Test]
    public function health_endpoint_returns_expected_structure(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'timestamp',
        ]);
    }

    #[Test]
    public function servers_index_returns_array(): void
    {
        $response = $this->getJson('/api/servers', $this->headers);

        $response->assertOk();
    }

    #[Test]
    public function sites_index_returns_expected_structure(): void
    {
        $response = $this->getJson('/api/sites', $this->headers);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data',
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
                'from',
                'to',
            ],
        ]);
    }

    #[Test]
    public function auth_returns_expected_error_structure(): void
    {
        $response = $this->getJson('/api/servers');

        $response->assertStatus(401);
        $response->assertJsonStructure([
            'message',
            'errors',
        ]);
    }

    #[Test]
    public function nonexistent_resource_returns_404_structure(): void
    {
        $response = $this->getJson('/api/servers/srv_nonexistent', $this->headers);

        $response->assertStatus(404);
    }

    #[Test]
    public function unauthorized_access_returns_403(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('Customer');

        $this->withoutExceptionHandling();
        $this->expectException(AuthorizationException::class);

        $this->getJson('/api/servers', [
            'Authorization' => 'Bearer '.$customer->createToken('test')->plainTextToken,
        ]);
    }

    #[Test]
    public function api_response_has_security_headers(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertHeader('X-Request-Id');
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
    }
}

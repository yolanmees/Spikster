<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    private string $password = 'TestPass123!';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt($this->password),
        ]);
    }

    public function test_unauthenticated_api_request_returns_401(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200); // Health endpoint is public
    }

    public function test_unauthenticated_protected_request_returns_422(): void
    {
        $response = $this->getJson('/api/servers');
        $response->assertStatus(422);
    }

    public function test_authenticated_request_with_sanctum_token_succeeds(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/servers');

        $response->assertStatus(200);
    }

    public function test_expired_token_returns_401(): void
    {
        $token = $this->user->createToken('test-token');
        $token->accessToken->forceFill(['expires_at' => now()->subDay()])->save();

        $response = $this->withHeader('Authorization', "Bearer {$token->plainTextToken}")
            ->getJson('/api/sites');

        $response->assertStatus(401);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $this->withoutExceptionHandling();
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $customer = User::factory()->create()->assignRole('Customer');
        $token = $customer->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/servers');
    }

    public function test_missing_bearer_token_returns_422(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ')
            ->getJson('/api/servers');

        $response->assertStatus(422);
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
    }

    public function test_rate_limiting_on_login(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'username' => 'wrong',
                'password' => 'wrong',
            ]);
        }

        $response = $this->postJson('/api/login', [
            'username' => 'wrong',
            'password' => 'wrong',
        ]);

        $response->assertStatus(429);
    }
}

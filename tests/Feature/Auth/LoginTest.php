<?php

namespace Tests\Feature\Auth;

use App\Models\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_login_with_valid_credentials(): void
    {
        $user = Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-' . now()->timestamp,
        ]);

        $response = $this->postJson('/auth', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'access_token',
            'refresh_token',
            'username',
        ]);
        $response->assertJson([
            'username' => 'testuser',
        ]);
    }

    #[Test]
    public function user_cannot_login_with_invalid_password(): void
    {
        Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-' . now()->timestamp,
        ]);

        $response = $this->postJson('/auth', [
            'username' => 'testuser',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
        $response->assertJsonFragment([
            'errors' => __('spikster.invalid_login'),
        ]);
    }

    #[Test]
    public function user_cannot_login_with_invalid_username(): void
    {
        $response = $this->postJson('/auth', [
            'username' => 'nonexistent',
            'password' => 'password123',
        ]);

        $response->assertUnauthorized();
    }

    #[Test]
    public function login_requires_username_field(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The username field is required');

        $this->withoutExceptionHandling();

        $this->postJson('/auth', [
            'password' => 'password123',
        ]);
    }

    #[Test]
    public function login_requires_password_field(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The password field is required');

        $this->withoutExceptionHandling();

        $this->postJson('/auth', [
            'username' => 'testuser',
        ]);
    }

    #[Test]
    public function login_returns_jwt_tokens(): void
    {
        Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-' . now()->timestamp,
        ]);

        $response = $this->postJson('/auth', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertOk();

        $this->assertNotEmpty($response->json('access_token'));
        $this->assertNotEmpty($response->json('refresh_token'));

        // Verify tokens are valid JWT format (3 parts separated by dots)
        $accessToken = $response->json('access_token');
        $refreshToken = $response->json('refresh_token');

        $this->assertCount(3, explode('.', $accessToken));
        $this->assertCount(3, explode('.', $refreshToken));
    }

    #[Test]
    public function login_updates_user_jwt_in_database(): void
    {
        $user = Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-' . now()->timestamp,
            'jwt' => null,
        ]);

        $this->assertNull($user->jwt);

        $response = $this->postJson('/auth', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertNotNull($user->jwt);
        $this->assertEquals($response->json('refresh_token'), $user->jwt);
    }
}

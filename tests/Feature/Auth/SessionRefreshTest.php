<?php

namespace Tests\Feature\Auth;

use App\Models\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SessionRefreshTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_can_refresh_token(): void
    {
        $user = Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-'.now()->timestamp,
        ]);

        // Login first
        $loginResponse = $this->postJson('/auth', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $refreshToken = $loginResponse->json('refresh_token');

        // Refresh token using query parameters
        $response = $this->get('/auth?'.http_build_query([
            'username' => 'testuser',
            'refresh_token' => $refreshToken,
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'access_token',
            'refresh_token',
            'username',
        ]);
    }

    #[Test]
    public function refresh_with_invalid_token_fails(): void
    {
        Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-'.now()->timestamp,
        ]);

        $response = $this->get('/auth?'.http_build_query([
            'username' => 'testuser',
            'refresh_token' => 'invalid-token',
        ]));

        $response->assertUnauthorized();
    }

    #[Test]
    public function refresh_requires_username(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The username field is required');

        $this->withoutExceptionHandling();

        $this->get('/auth?'.http_build_query([
            'refresh_token' => 'some-token',
        ]));
    }

    #[Test]
    public function refresh_requires_token(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The refresh token field is required');

        $this->withoutExceptionHandling();

        $this->get('/auth?'.http_build_query([
            'username' => 'testuser',
        ]));
    }

    #[Test]
    public function refresh_returns_new_tokens(): void
    {
        $user = Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-'.now()->timestamp,
        ]);

        // Login
        $loginResponse = $this->postJson('/auth', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $oldRefreshToken = $loginResponse->json('refresh_token');

        // Wait to ensure different timestamp
        sleep(1);

        // Refresh using query parameters
        $refreshResponse = $this->get('/auth?'.http_build_query([
            'username' => 'testuser',
            'refresh_token' => $oldRefreshToken,
        ]));

        $newRefreshToken = $refreshResponse->json('refresh_token');

        // Tokens should be different
        $this->assertNotEquals($oldRefreshToken, $newRefreshToken);
    }

    #[Test]
    public function refresh_updates_jwt_in_database(): void
    {
        $user = Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-'.now()->timestamp,
        ]);

        // Login
        $loginResponse = $this->postJson('/auth', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $oldJwt = $user->fresh()->jwt;

        // Wait to ensure different timestamp
        sleep(1);

        // Refresh using query parameters
        $refreshResponse = $this->get('/auth?'.http_build_query([
            'username' => 'testuser',
            'refresh_token' => $loginResponse->json('refresh_token'),
        ]));

        $newJwt = $user->fresh()->jwt;

        $this->assertNotEquals($oldJwt, $newJwt);
        $this->assertEquals($refreshResponse->json('refresh_token'), $newJwt);
    }
}

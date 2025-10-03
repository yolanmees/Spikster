<?php

namespace Tests\Feature\Auth;

use App\Models\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_can_logout(): void
    {
        $user = Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-' . now()->timestamp,
        ]);

        // Login first
        $loginResponse = $this->postJson('/auth', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $refreshToken = $loginResponse->json('refresh_token');

        // Then logout
        $response = $this->deleteJson('/auth', [
            'username' => 'testuser',
            'refresh_token' => $refreshToken,
        ]);

        $response->assertOk();

        // Verify JWT is cleared from database
        $user->refresh();
        $this->assertNull($user->jwt);
    }

    #[Test]
    public function guest_cannot_logout(): void
    {
        $response = $this->deleteJson('/auth', [
            'username' => 'testuser',
            'refresh_token' => 'invalid-token',
        ]);

        $response->assertUnauthorized();
    }

    #[Test]
    public function logout_requires_username(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The username field is required');

        $this->withoutExceptionHandling();

        $this->deleteJson('/auth', [
            'refresh_token' => 'some-token',
        ]);
    }

    #[Test]
    public function logout_requires_refresh_token(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The refresh token field is required');

        $this->withoutExceptionHandling();

        $this->deleteJson('/auth', [
            'username' => 'testuser',
        ]);
    }

    #[Test]
    public function logout_with_invalid_token_fails(): void
    {
        Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-' . now()->timestamp,
            'jwt' => 'valid-jwt-token',
        ]);

        $response = $this->deleteJson('/auth', [
            'username' => 'testuser',
            'refresh_token' => 'wrong-token',
        ]);

        $response->assertUnauthorized();
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_can_update_username(): void
    {
        $user = Auth::create([
            'username' => 'oldusername',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-'.now()->timestamp,
        ]);

        $response = $this->patchJson('/auth', [
            'username' => 'oldusername',
            'password' => 'password123',
            'newusername' => 'newusername',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('auths', [
            'id' => $user->id,
            'username' => 'newusername',
        ]);
    }

    #[Test]
    public function user_cannot_update_to_existing_username(): void
    {
        Auth::create([
            'username' => 'existinguser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-1',
        ]);

        $user = Auth::create([
            'username' => 'currentuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-2',
        ]);

        $response = $this->patchJson('/auth', [
            'username' => 'currentuser',
            'password' => 'password123',
            'newusername' => 'existinguser',
        ]);

        $response->assertStatus(409); // Conflict
        $response->assertJsonFragment([
            'errors' => __('spikster.username_conflict'),
        ]);
    }

    #[Test]
    public function user_can_update_password(): void
    {
        $user = Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('oldpassword'),
            'apikey' => 'test-api-key-'.now()->timestamp,
        ]);

        $response = $this->patchJson('/auth', [
            'username' => 'testuser',
            'password' => 'oldpassword',
            'newpassword' => 'newpassword123',
        ]);

        $response->assertOk();

        // Verify new password works
        $this->assertTrue(
            Hash::check('newpassword123', $user->fresh()->password)
        );
    }

    #[Test]
    public function user_can_regenerate_api_key(): void
    {
        $user = Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'old-api-key',
        ]);

        $oldApiKey = $user->apikey;

        $response = $this->patchJson('/auth', [
            'username' => 'testuser',
            'password' => 'password123',
            'apikey' => true,
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertNotEquals($oldApiKey, $user->apikey);
        $this->assertEquals(48, strlen($user->apikey)); // Should be 48 chars
    }

    #[Test]
    public function update_requires_current_username_and_password(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The username field is required');

        $this->withoutExceptionHandling();

        $this->patchJson('/auth', [
            'newusername' => 'newuser',
        ]);
    }

    #[Test]
    public function update_with_wrong_password_fails(): void
    {
        Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('correctpassword'),
            'apikey' => 'test-api-key-'.now()->timestamp,
        ]);

        $response = $this->patchJson('/auth', [
            'username' => 'testuser',
            'password' => 'wrongpassword',
            'newusername' => 'newuser',
        ]);

        $response->assertUnauthorized();
    }

    #[Test]
    public function new_username_must_be_at_least_6_characters(): void
    {
        $user = Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-'.now()->timestamp,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The newusername must be at least 6 characters');

        $this->withoutExceptionHandling();

        $this->patchJson('/auth', [
            'username' => 'testuser',
            'password' => 'password123',
            'newusername' => 'short',
        ]);
    }

    #[Test]
    public function new_password_must_be_at_least_8_characters(): void
    {
        $user = Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-'.now()->timestamp,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The newpassword must be at least 8 characters');

        $this->withoutExceptionHandling();

        $this->patchJson('/auth', [
            'username' => 'testuser',
            'password' => 'password123',
            'newpassword' => 'short',
        ]);
    }

    #[Test]
    public function username_is_converted_to_lowercase(): void
    {
        $user = Auth::create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'apikey' => 'test-api-key-'.now()->timestamp,
        ]);

        $response = $this->patchJson('/auth', [
            'username' => 'testuser',
            'password' => 'password123',
            'newusername' => 'UPPERCASE',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('auths', [
            'id' => $user->id,
            'username' => 'uppercase', // Should be lowercase
        ]);
    }
}

<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable exception handling for better error messages
        $this->withoutExceptionHandling();

        // Additional setup can go here
    }

    /**
     * Create and authenticate a user.
     */
    protected function actingAsUser($user = null): self
    {
        $user = $user ?? User::factory()->create();

        return $this->actingAs($user);
    }

    /**
     * Create and authenticate an admin user.
     */
    protected function actingAsAdmin($user = null): self
    {
        $user = $user ?? User::factory()->create([
            'role' => 'admin',
        ]);

        return $this->actingAs($user);
    }

    /**
     * Assert that a model exists in the database.
     */
    protected function assertModelExists($model): void
    {
        $this->assertDatabaseHas($model->getTable(), [
            $model->getKeyName() => $model->getKey(),
        ]);
    }

    /**
     * Assert that a model does not exist in the database.
     */
    protected function assertModelMissing($model): void
    {
        $this->assertDatabaseMissing($model->getTable(), [
            $model->getKeyName() => $model->getKey(),
        ]);
    }
}

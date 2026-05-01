<?php

namespace Database\Seeders\Testing;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        // Create regular user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
        ]);

        // Create a few more users for testing
        User::factory()->count(3)->create();
    }
}

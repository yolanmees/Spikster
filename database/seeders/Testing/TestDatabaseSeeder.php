<?php

namespace Database\Seeders\Testing;

use Illuminate\Database\Seeder;

class TestDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for testing.
     */
    public function run(): void
    {
        $this->call([
            TestUserSeeder::class,
        ]);
    }
}

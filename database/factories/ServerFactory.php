<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Server>
 */
class ServerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server_id' => 'srv_'.Str::random(16),
            'ip' => $this->faker->ipv4(),
            'name' => $this->faker->words(2, true).' Server',
            'password' => Str::random(24),
            'database' => Str::random(24),
            'provider' => $this->faker->randomElement(['AWS', 'DigitalOcean', 'Linode', 'Hetzner', 'Vultr']),
            'location' => $this->faker->randomElement(['Frankfurt', 'Amsterdam', 'London', 'New York', 'Singapore']),
            'php' => $this->faker->randomElement(['7.4', '8.0', '8.1', '8.2', '8.3']),
            'github_key' => null,
            'cron' => ' ',
            'default' => false,
            'build' => null,
            'status' => 1,
        ];
    }

    /**
     * Indicate that the server is the default server.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'default' => true,
        ]);
    }

    /**
     * Indicate that the server is not yet installed.
     */
    public function notInstalled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }

    /**
     * Indicate that the server has a specific build version.
     */
    public function withBuild(int $build): static
    {
        return $this->state(fn (array $attributes) => [
            'build' => $build,
        ]);
    }
}

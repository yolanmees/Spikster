<?php

namespace Database\Factories;

use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Site>
 */
class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $domain = $this->faker->domainName();
        $username = 'u'.Str::random(7);

        return [
            'site_id' => 'ste_'.Str::random(16),
            'server_id' => Server::factory(),
            'domain' => $domain,
            'username' => $username,
            'password' => Str::random(24),
            'database' => Str::random(24),
            'basepath' => '/public',
            'repository' => null,
            'branch' => null,
            'php' => $this->faker->randomElement(['7.4', '8.0', '8.1', '8.2', '8.3']),
            'supervisor' => null,
            'nginx' => null,
            'deploy' => null,
            'panel' => false,
        ];
    }

    /**
     * Indicate that the site is a panel site.
     */
    public function panel(): static
    {
        return $this->state(fn (array $attributes) => [
            'panel' => true,
        ]);
    }

    /**
     * Indicate that the site has a Git repository.
     */
    public function withRepository(string $repository = 'https://github.com/user/repo.git', string $branch = 'main'): static
    {
        return $this->state(fn (array $attributes) => [
            'repository' => $repository,
            'branch' => $branch,
        ]);
    }

    /**
     * Indicate that the site uses a specific PHP version.
     */
    public function withPhp(string $version): static
    {
        return $this->state(fn (array $attributes) => [
            'php' => $version,
        ]);
    }

    /**
     * Indicate that the site belongs to a specific server.
     */
    public function forServer(Server $server): static
    {
        return $this->state(fn (array $attributes) => [
            'server_id' => $server->id,
        ]);
    }
}

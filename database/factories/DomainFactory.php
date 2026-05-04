<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\Server;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DomainFactory extends Factory
{
    protected $model = Domain::class;

    public function definition(): array
    {
        return [
            'domain_id' => 'dom_'.Str::random(16),
            'server_id' => Server::factory()->create()->server_id,
            'domain' => $this->faker->unique()->domainName(),
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => ['is_primary' => true]);
    }

    public function alias(): static
    {
        return $this->state(fn (array $attributes) => ['is_primary' => false]);
    }

    public function forSite(Site $site): static
    {
        return $this->state(fn (array $attributes) => [
            'site_id' => $site->site_id,
            'server_id' => $site->server?->server_id ?? $attributes['server_id'],
        ]);
    }
}

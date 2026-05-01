<?php

namespace Database\Factories;

use App\Models\Alias;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Alias>
 */
class AliasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alias_id' => 'als_'.Str::random(16),
            'site_id' => Site::factory(),
            'domain' => $this->faker->unique()->domainName(),
            'ssl' => $this->faker->boolean(30), // 30% chance of SSL enabled
        ];
    }

    /**
     * Indicate that the alias has SSL enabled.
     */
    public function withSsl(): static
    {
        return $this->state(fn (array $attributes) => [
            'ssl' => true,
        ]);
    }

    /**
     * Indicate that the alias has SSL disabled.
     */
    public function withoutSsl(): static
    {
        return $this->state(fn (array $attributes) => [
            'ssl' => false,
        ]);
    }

    /**
     * Indicate that the alias belongs to a specific site.
     */
    public function forSite(Site $site): static
    {
        return $this->state(fn (array $attributes) => [
            'site_id' => $site->id,
        ]);
    }
}

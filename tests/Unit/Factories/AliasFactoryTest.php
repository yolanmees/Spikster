<?php

namespace Tests\Unit\Factories;

use App\Models\Alias;
use App\Models\Site;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\UnitTestCase;

class AliasFactoryTest extends UnitTestCase
{
    #[Test]
    public function it_creates_a_valid_alias(): void
    {
        $alias = Alias::factory()->create();

        $this->assertInstanceOf(Alias::class, $alias);
        $this->assertNotNull($alias->id);
        $this->assertNotEmpty($alias->domain);
        $this->assertNotNull($alias->site_id);
    }

    #[Test]
    public function it_creates_an_alias_with_custom_attributes(): void
    {
        $site = Site::factory()->create();

        $alias = Alias::factory()->create([
            'domain' => 'alias.example.com',
            'site_id' => $site->id,
        ]);

        $this->assertEquals('alias.example.com', $alias->domain);
        $this->assertEquals($site->id, $alias->site_id);
    }

    #[Test]
    public function it_creates_multiple_aliases(): void
    {
        $aliases = Alias::factory()->count(5)->create();

        $this->assertCount(5, $aliases);

        foreach ($aliases as $alias) {
            $this->assertInstanceOf(Alias::class, $alias);
            $this->assertNotNull($alias->id);
        }
    }

    #[Test]
    public function it_creates_alias_with_site_relationship(): void
    {
        $site = Site::factory()->create();

        $alias = Alias::factory()
            ->for($site)
            ->create();

        $this->assertEquals($site->id, $alias->site_id);
        $this->assertInstanceOf(Site::class, $alias->site);
    }

    #[Test]
    public function it_generates_unique_domains(): void
    {
        $aliases = Alias::factory()->count(10)->create();

        $domains = $aliases->pluck('domain')->toArray();
        $uniqueDomains = array_unique($domains);

        $this->assertCount(count($domains), $uniqueDomains);
    }

    #[Test]
    public function it_creates_multiple_aliases_for_same_site(): void
    {
        $site = Site::factory()->create();

        $aliases = Alias::factory()
            ->count(3)
            ->for($site)
            ->create();

        $this->assertCount(3, $aliases);

        foreach ($aliases as $alias) {
            $this->assertEquals($site->id, $alias->site_id);
        }
    }
}

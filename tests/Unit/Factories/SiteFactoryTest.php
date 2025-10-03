<?php

namespace Tests\Unit\Factories;

use App\Models\Server;
use App\Models\Site;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\UnitTestCase;

class SiteFactoryTest extends UnitTestCase
{
    #[Test]
    public function it_creates_a_valid_site(): void
    {
        $site = Site::factory()->create();

        $this->assertInstanceOf(Site::class, $site);
        $this->assertNotNull($site->id);
        $this->assertNotEmpty($site->domain);
        $this->assertNotEmpty($site->username);
        $this->assertNotEmpty($site->basepath);
    }

    #[Test]
    public function it_creates_a_site_with_custom_attributes(): void
    {
        $server = Server::factory()->create();

        $site = Site::factory()->create([
            'domain' => 'example.com',
            'username' => 'testuser',
            'basepath' => '/var/www/example',
            'php' => '8.3',
            'server_id' => $server->id,
        ]);

        $this->assertEquals('example.com', $site->domain);
        $this->assertEquals('testuser', $site->username);
        $this->assertEquals('/var/www/example', $site->basepath);
        $this->assertEquals('8.3', $site->php);
        $this->assertEquals($server->id, $site->server_id);
    }

    #[Test]
    public function it_creates_multiple_sites(): void
    {
        $sites = Site::factory()->count(5)->create();

        $this->assertCount(5, $sites);

        foreach ($sites as $site) {
            $this->assertInstanceOf(Site::class, $site);
            $this->assertNotNull($site->id);
        }
    }

    #[Test]
    public function it_creates_site_with_server_relationship(): void
    {
        $server = Server::factory()->create();

        $site = Site::factory()
            ->for($server)
            ->create();

        $this->assertEquals($server->id, $site->server_id);
        $this->assertInstanceOf(Server::class, $site->server);
    }

    #[Test]
    public function it_generates_unique_domains(): void
    {
        $sites = Site::factory()->count(10)->create();

        $domains = $sites->pluck('domain')->toArray();
        $uniqueDomains = array_unique($domains);

        $this->assertCount(count($domains), $uniqueDomains);
    }

    #[Test]
    public function it_generates_valid_php_versions(): void
    {
        $site = Site::factory()->create();

        $validVersions = ['7.4', '8.0', '8.1', '8.2', '8.3', '8.4'];
        $this->assertContains($site->php, $validVersions);
    }

    #[Test]
    public function it_generates_valid_usernames(): void
    {
        $site = Site::factory()->create();

        // Usernames can contain uppercase letters too
        $this->assertMatchesRegularExpression(
            '/^[a-zA-Z0-9_-]+$/',
            $site->username
        );
    }

    #[Test]
    public function it_generates_unique_site_ids(): void
    {
        $sites = Site::factory()->count(10)->create();

        $siteIds = $sites->pluck('site_id')->toArray();
        $uniqueIds = array_unique($siteIds);

        $this->assertCount(count($siteIds), $uniqueIds);

        // Site IDs should have a prefix
        foreach ($sites as $site) {
            $this->assertStringStartsWith('ste_', $site->site_id);
        }
    }
}

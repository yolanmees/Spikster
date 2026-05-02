<?php

namespace Tests\Unit;

use App\Models\Server;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_belongs_to_server(): void
    {
        $server = Server::factory()->create();
        $site = Site::factory()->forServer($server)->create();

        $this->assertInstanceOf(Server::class, $site->server);
        $this->assertEquals($server->id, $site->server->id);
    }

    public function test_site_has_display_path(): void
    {
        $site = Site::factory()->make(['username' => 'testuser', 'domain' => 'example.com']);

        $this->assertEquals('/home/testuser/example.com', $site->root_path);
    }

    public function test_site_has_public_path_with_default_basepath(): void
    {
        $site = Site::factory()->make([
            'username' => 'testuser',
            'domain' => 'example.com',
            'basepath' => '/public',
        ]);

        $this->assertEquals('/home/testuser/example.com/public', $site->public_path);
    }

    public function test_panel_site_detection(): void
    {
        $panelSite = Site::factory()->make(['panel' => true]);
        $normalSite = Site::factory()->make(['panel' => false]);

        $this->assertTrue($panelSite->isPanel());
        $this->assertFalse($normalSite->isPanel());
    }

    public function test_site_with_repository(): void
    {
        $site = Site::factory()->make(['repository' => 'https://github.com/user/repo.git']);
        $this->assertTrue($site->hasRepository());

        $site2 = Site::factory()->make(['repository' => null]);
        $this->assertFalse($site2->hasRepository());
    }

    public function test_php_settings_are_encrypted(): void
    {
        $site = Site::factory()->create(['password' => 'secret123', 'database' => 'db_secret']);
        $this->assertNotEquals('secret123', $site->getRawOriginal('password'));
        $this->assertNotEquals('db_secret', $site->getRawOriginal('database'));
    }

    public function test_non_panel_scope(): void
    {
        Server::factory()->create();
        Site::factory()->create(['panel' => true]);
        Site::factory()->create(['panel' => false]);

        $this->assertEquals(1, Site::nonPanel()->count());
    }

    public function test_by_php_version_scope(): void
    {
        Site::factory()->create(['php' => '8.3']);
        Site::factory()->create(['php' => '8.2']);

        $this->assertEquals(1, Site::byPhpVersion('8.3')->count());
    }

    public function test_with_repository_scope(): void
    {
        Site::factory()->create(['repository' => 'https://github.com/user/repo.git']);
        Site::factory()->create(['repository' => null]);

        $this->assertEquals(1, Site::withRepository()->count());
    }
}

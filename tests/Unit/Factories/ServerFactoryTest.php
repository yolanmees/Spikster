<?php

namespace Tests\Unit\Factories;

use App\Models\Server;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\UnitTestCase;

class ServerFactoryTest extends UnitTestCase
{
    #[Test]
    public function it_creates_a_valid_server(): void
    {
        $server = Server::factory()->create();

        $this->assertInstanceOf(Server::class, $server);
        $this->assertNotNull($server->id);
        $this->assertNotEmpty($server->name);
        $this->assertNotEmpty($server->ip);
        $this->assertNotEmpty($server->server_id);
        $this->assertNotEmpty($server->password);
        $this->assertNotEmpty($server->database);
    }

    #[Test]
    public function it_creates_a_server_with_custom_attributes(): void
    {
        $server = Server::factory()->create([
            'name' => 'Custom Server',
            'ip' => '192.168.1.100',
            'provider' => 'AWS',
            'location' => 'Amsterdam',
        ]);

        $this->assertEquals('Custom Server', $server->name);
        $this->assertEquals('192.168.1.100', $server->ip);
        $this->assertEquals('AWS', $server->provider);
        $this->assertEquals('Amsterdam', $server->location);
    }

    #[Test]
    public function it_creates_multiple_servers(): void
    {
        $servers = Server::factory()->count(5)->create();

        $this->assertCount(5, $servers);

        foreach ($servers as $server) {
            $this->assertInstanceOf(Server::class, $server);
            $this->assertNotNull($server->id);
        }
    }

    #[Test]
    public function it_creates_server_with_sites_relationship(): void
    {
        $server = Server::factory()
            ->has(\App\Models\Site::factory()->count(3))
            ->create();

        $this->assertCount(3, $server->sites);
        $this->assertInstanceOf(\App\Models\Site::class, $server->sites->first());
    }

    #[Test]
    public function it_generates_unique_names(): void
    {
        $servers = Server::factory()->count(10)->create();

        $names = $servers->pluck('name')->toArray();
        $uniqueNames = array_unique($names);

        $this->assertCount(count($names), $uniqueNames);
    }

    #[Test]
    public function it_generates_valid_ip_addresses(): void
    {
        $server = Server::factory()->create();

        $this->assertMatchesRegularExpression(
            '/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/',
            $server->ip
        );
    }

    #[Test]
    public function it_generates_unique_server_ids(): void
    {
        $servers = Server::factory()->count(10)->create();

        $serverIds = $servers->pluck('server_id')->toArray();
        $uniqueIds = array_unique($serverIds);

        $this->assertCount(count($serverIds), $uniqueIds);

        // Server IDs should have a prefix
        foreach ($servers as $server) {
            $this->assertStringStartsWith('srv_', $server->server_id);
        }
    }
}

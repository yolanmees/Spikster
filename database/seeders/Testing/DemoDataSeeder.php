<?php

namespace Database\Seeders\Testing;

use App\Models\Server;
use App\Models\Site;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Demo server 1 — default
        $server1 = Server::create([
            'server_id' => Str::uuid(),
            'ip' => '10.0.0.1',
            'name' => 'Production Server',
            'password' => Str::random(24),
            'database' => Str::random(24),
            'provider' => 'DigitalOcean',
            'location' => 'Amsterdam, NL',
            'php' => '8.3',
            'default' => true,
            'status' => 1,
        ]);

        // Demo server 2
        $server2 = Server::create([
            'server_id' => Str::uuid(),
            'ip' => '10.0.0.2',
            'name' => 'Staging Server',
            'password' => Str::random(24),
            'database' => Str::random(24),
            'provider' => 'Hetzner',
            'location' => 'Nuremberg, DE',
            'php' => '8.2',
            'default' => false,
            'status' => 1,
        ]);

        // Panel site on server 1
        Site::create([
            'site_id' => Str::uuid(),
            'server_id' => $server1->id,
            'domain' => 'panel.example.com',
            'username' => 'cp_panel',
            'password' => Str::random(16),
            'database' => Str::random(16),
            'basepath' => '/public',
            'php' => '8.3',
            'panel' => true,
        ]);

        // Regular sites on server 1
        $domains1 = ['myshop.com', 'portfolio.dev', 'blog.example.com'];
        foreach ($domains1 as $domain) {
            Site::create([
                'site_id' => Str::uuid(),
                'server_id' => $server1->id,
                'domain' => $domain,
                'username' => 'cp_'.Str::random(6),
                'password' => Str::random(16),
                'database' => Str::random(16),
                'basepath' => '/public',
                'php' => '8.3',
                'panel' => false,
            ]);
        }

        // Sites on server 2
        $domains2 = ['staging.myshop.com', 'test.portfolio.dev'];
        foreach ($domains2 as $domain) {
            Site::create([
                'site_id' => Str::uuid(),
                'server_id' => $server2->id,
                'domain' => $domain,
                'username' => 'cp_'.Str::random(6),
                'password' => Str::random(16),
                'database' => Str::random(16),
                'basepath' => '/public',
                'php' => '8.2',
                'panel' => false,
            ]);
        }
    }
}

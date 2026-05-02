<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Str;

class ServerTemplateService
{
    protected array $templates = [];

    public function __construct()
    {
        $this->templates = config('server-templates.templates', []);
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }

    public function getTemplate(string $name): ?array
    {
        return $this->templates[$name] ?? null;
    }

    public function getProviders(): array
    {
        return config('server-templates.providers', [
            'digitalocean' => [
                'name' => 'DigitalOcean',
                'regions' => ['nyc1', 'nyc3', 'sfo2', 'sfo3', 'ams3', 'fra1', 'lon1', 'sgp1', 'tor1'],
                'sizes' => ['s-1vcpu-1gb', 's-1vcpu-2gb', 's-2vcpu-2gb', 's-2vcpu-4gb', 's-4vcpu-8gb'],
                'images' => ['ubuntu-22-04-x64', 'ubuntu-24-04-x64'],
            ],
            'vultr' => [
                'name' => 'Vultr',
                'regions' => ['ams', 'atl', 'cdg', 'dfw', 'ewr', 'fra', 'lax', 'lhr', 'mia', 'ord', 'sea', 'sgp', 'syd', 'tokyo', 'tor'],
                'sizes' => ['vc2-1c-1gb', 'vc2-1c-2gb', 'vc2-2c-2gb', 'vc2-2c-4gb'],
                'images' => ['ubuntu-22-04-x64', 'ubuntu-24-04-x64'],
            ],
            'linode' => [
                'name' => 'Linode',
                'regions' => ['us-east', 'us-central', 'us-west', 'us-southeast', 'eu-central', 'eu-west', 'ap-northeast', 'ap-south'],
                'sizes' => ['g6-nanode-1', 'g6-standard-1', 'g6-standard-2', 'g6-standard-4'],
                'images' => ['linode/ubuntu22.04', 'linode/ubuntu24.04'],
            ],
        ]);
    }

    public function createFromTemplate(string $templateName, array $overrides = []): Server
    {
        $template = $this->getTemplate($templateName);

        if (! $template) {
            throw new \InvalidArgumentException("Template '{$templateName}' not found");
        }

        $data = array_merge($template, $overrides);

        return Server::create([
            'server_id' => 'srv_'.Str::random(16),
            'name' => $data['name'] ?? $templateName,
            'ip' => $data['ip'] ?? '0.0.0.0',
            'password' => $data['password'] ?? Str::random(24),
            'database' => $data['db_password'] ?? Str::random(24),
            'provider' => $data['provider'] ?? 'manual',
            'location' => $data['region'] ?? null,
            'php' => $data['php'] ?? config('spikster.default_php', '8.3'),
            'build' => $data['build'] ?? null,
            'status' => 0,
        ]);
    }
}

<?php

namespace App\Services\Providers;

use Illuminate\Support\Facades\Http;

class DigitalOceanProvider implements CloudProviderInterface
{
    protected string $token;
    protected string $apiBase = 'https://api.digitalocean.com/v2';

    public function __construct()
    {
        $this->token = config('providers.digitalocean.token', '');
    }

    public function getName(): string { return 'DigitalOcean'; }

    public function listRegions(): array
    {
        $response = Http::withToken($this->token)->get("{$this->apiBase}/regions");
        return $response->successful() ? $response->json('regions') : [];
    }

    public function listSizes(): array
    {
        $response = Http::withToken($this->token)->get("{$this->apiBase}/sizes");
        return $response->successful() ? $response->json('sizes') : [];
    }

    public function listImages(): array
    {
        $response = Http::withToken($this->token)->get("{$this->apiBase}/images", ['type' => 'distribution']);
        return $response->successful() ? $response->json('images') : [];
    }

    public function createServer(string $name, string $region, string $size, string $image): array
    {
        $response = Http::withToken($this->token)->post("{$this->apiBase}/droplets", [
            'name' => $name,
            'region' => $region,
            'size' => $size,
            'image' => $image,
        ]);
        return $response->successful() ? $response->json('droplet') : throw new \RuntimeException($response->body());
    }

    public function deleteServer(string $providerId): bool
    {
        return Http::withToken($this->token)->delete("{$this->apiBase}/droplets/{$providerId}")->successful();
    }

    public function getServer(string $providerId): array
    {
        $response = Http::withToken($this->token)->get("{$this->apiBase}/droplets/{$providerId}");
        return $response->successful() ? $response->json('droplet') : [];
    }

    public function rebootServer(string $providerId): bool
    {
        return Http::withToken($this->token)->post("{$this->apiBase}/droplets/{$providerId}/actions", [
            'type' => 'reboot',
        ])->successful();
    }
}

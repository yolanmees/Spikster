<?php

namespace App\Services\Providers;

use Illuminate\Support\Facades\Http;

class VultrProvider implements CloudProviderInterface
{
    protected string $token;
    protected string $apiBase = 'https://api.vultr.com/v2';

    public function __construct()
    {
        $this->token = config('providers.vultr.token', '');
    }

    public function getName(): string { return 'Vultr'; }

    public function listRegions(): array
    {
        $response = Http::withHeaders(['Authorization' => "Bearer {$this->token}"])->get("{$this->apiBase}/regions");
        return $response->successful() ? $response->json('regions') : [];
    }

    public function listSizes(): array
    {
        $response = Http::withHeaders(['Authorization' => "Bearer {$this->token}"])->get("{$this->apiBase}/plans");
        return $response->successful() ? $response->json('plans') : [];
    }

    public function listImages(): array
    {
        $response = Http::withHeaders(['Authorization' => "Bearer {$this->token}"])->get("{$this->apiBase}/os");
        return $response->successful() ? $response->json('os') : [];
    }

    public function createServer(string $name, string $region, string $size, string $image): array
    {
        $response = Http::withHeaders(['Authorization' => "Bearer {$this->token}"])->post("{$this->apiBase}/instances", [
            'region' => $region,
            'plan' => $size,
            'os_id' => (int) $image,
            'hostname' => $name,
        ]);
        return $response->successful() ? $response->json('instance') : throw new \RuntimeException($response->body());
    }

    public function deleteServer(string $providerId): bool
    {
        return Http::withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->delete("{$this->apiBase}/instances/{$providerId}")->successful();
    }

    public function getServer(string $providerId): array
    {
        $response = Http::withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->get("{$this->apiBase}/instances/{$providerId}");
        return $response->successful() ? $response->json('instance') : [];
    }

    public function rebootServer(string $providerId): bool
    {
        $response = Http::withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->post("{$this->apiBase}/instances/{$providerId}/reboot");
        return $response->successful();
    }
}

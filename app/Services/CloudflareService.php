<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CloudflareService
{
    protected string $baseUrl = 'https://api.cloudflare.com/client/v4';
    protected ?string $apiToken = null;
    protected ?string $accountId = null;

    public function __construct()
    {
        $this->apiToken = config('services.cloudflare.api_token') ?? env('CLOUDFLARE_API_TOKEN');
        $this->accountId = config('services.cloudflare.account_id') ?? env('CLOUDFLARE_ACCOUNT_ID');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiToken);
    }

    protected function request(string $method, string $path, array $data = []): array
    {
        $url = $this->baseUrl . $path;
        $response = Http::withToken($this->apiToken)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->$method($url, $data);

        return $response->json() ?? [];
    }

    // ─── Zones ───────────────────────────────────────

    public function listZones(): array
    {
        return Cache::remember('cloudflare.zones', 300, function () {
            return $this->request('get', '/zones?per_page=50')['result'] ?? [];
        });
    }

    public function getZone(string $zoneId): ?array
    {
        return $this->request('get', "/zones/{$zoneId}")['result'] ?? null;
    }

    public function findZone(string $domain): ?array
    {
        $zones = $this->listZones();
        foreach ($zones as $zone) {
            if ($zone['name'] === $domain) {
                return $zone;
            }
        }

        // Try parent domain
        $parts = explode('.', $domain);
        if (count($parts) > 2) {
            $parent = implode('.', array_slice($parts, -2));
            return $this->findZone($parent);
        }

        return null;
    }

    public function createZone(string $domain): ?array
    {
        $result = $this->request('post', '/zones', [
            'name' => $domain,
            'account' => ['id' => $this->accountId],
            'jump_start' => false,
        ]);

        if (! empty($result['success']) && ! empty($result['result'])) {
            Cache::forget('cloudflare.zones');
            return $result['result'];
        }

        return null;
    }

    // ─── DNS Records ─────────────────────────────────

    public function listDnsRecords(string $zoneId, string $type = null): array
    {
        $params = '?per_page=100';
        if ($type) {
            $params .= "&type={$type}";
        }
        return $this->request('get', "/zones/{$zoneId}/dns_records{$params}")['result'] ?? [];
    }

    public function createDnsRecord(string $zoneId, string $type, string $name, string $content, bool $proxied = false, int $ttl = 1): ?array
    {
        $result = $this->request('post', "/zones/{$zoneId}/dns_records", [
            'type' => $type,
            'name' => $name,
            'content' => $content,
            'ttl' => $ttl,
            'proxied' => $proxied,
        ]);

        return ! empty($result['success']) ? $result['result'] : null;
    }

    public function updateDnsRecord(string $zoneId, string $recordId, array $data): ?array
    {
        $result = $this->request('put', "/zones/{$zoneId}/dns_records/{$recordId}", $data);
        return ! empty($result['success']) ? $result['result'] : null;
    }

    public function deleteDnsRecord(string $zoneId, string $recordId): bool
    {
        $result = $this->request('delete', "/zones/{$zoneId}/dns_records/{$recordId}");
        return ! empty($result['success']);
    }

    // ─── Helpers ─────────────────────────────────────

    public function ensureZoneAndARecord(string $domain, string $ip): ?array
    {
        $zone = $this->findZone($domain);

        if (! $zone) {
            $zone = $this->createZone($domain);
            if (! $zone) {
                return null;
            }
        }

        $zoneId = $zone['id'];

        // Check if A record exists
        $records = $this->listDnsRecords($zoneId, 'A');
        $existing = null;
        foreach ($records as $rec) {
            if ($rec['name'] === $domain) {
                $existing = $rec;
                break;
            }
        }

        if ($existing) {
            // Update if IP changed
            if ($existing['content'] !== $ip) {
                $this->updateDnsRecord($zoneId, $existing['id'], [
                    'type' => 'A',
                    'name' => $domain,
                    'content' => $ip,
                    'ttl' => 1,
                    'proxied' => $existing['proxied'],
                ]);
            }
        } else {
            $this->createDnsRecord($zoneId, 'A', $domain, $ip);
        }

        return $zone;
    }

    public function updateNameServers(array $nameservers): string
    {
        return implode(', ', $nameservers);
    }
}

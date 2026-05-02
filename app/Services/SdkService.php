<?php

namespace App\Services;

class SdkService
{
    public function getPhpSdkStub(): string
    {
        return <<<'PHP'
<?php

namespace Spikster;

class SpiksterClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    public function servers(): ServerResource
    {
        return new ServerResource($this);
    }

    public function sites(): SiteResource
    {
        return new SiteResource($this);
    }

    public function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    public function post(string $path, array $data = []): array
    {
        return $this->request('POST', $path, $data);
    }

    public function patch(string $path, array $data = []): array
    {
        return $this->request('PATCH', $path, $data);
    }

    public function delete(string $path): array
    {
        return $this->request('DELETE', $path);
    }

    private function request(string $method, string $path, array $data = []): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        if (! empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['data' => json_decode($response, true), 'status' => $httpCode];
    }
}

class ServerResource
{
    private SpiksterClient $client;

    public function __construct(SpiksterClient $client) { $this->client = $client; }

    public function list(): array { return $this->client->get('/api/servers'); }
    public function get(string $id): array { return $this->client->get("/api/servers/{$id}"); }
    public function create(array $data): array { return $this->client->post('/api/servers', $data); }
    public function delete(string $id): array { return $this->client->delete("/api/servers/{$id}"); }
}

class SiteResource
{
    private SpiksterClient $client;

    public function __construct(SpiksterClient $client) { $this->client = $client; }

    public function list(): array { return $this->client->get('/api/sites'); }
    public function get(string $id): array { return $this->client->get("/api/sites/{$id}"); }
    public function create(array $data): array { return $this->client->post('/api/sites', $data); }
    public function delete(string $id): array { return $this->client->delete("/api/sites/{$id}"); }
}
PHP;
    }

    public function getSdkDocumentation(): string
    {
        return <<<'MD'
# Spikster PHP SDK

## Installation

```bash
composer require spikster/sdk
```

## Usage

```php
$client = new Spikster\SpiksterClient('https://panel.example.com', 'your-api-token');

// List servers
$servers = $client->servers()->list();

// Get site details
$site = $client->sites()->get('ste_abc123');

// Create a server
$server = $client->servers()->create([
    'name' => 'My Server',
    'ip' => '192.168.1.1',
    'password' => 'root-password',
]);
```

## Resources

- `servers()` → `list()`, `get(id)`, `create(data)`, `delete(id)`
- `sites()` → `list()`, `get(id)`, `create(data)`, `delete(id)`

Full documentation: https://spikster.com/docs/sdk
MD;
    }
}

<?php

namespace App\Livewire\Settings;

use App\Services\CloudflareService;
use Livewire\Component;

class Cloudflare extends Component
{
    public string $apiToken = '';
    public string $accountId = '';
    public array $zones = [];
    public bool $configured = false;
    public string $statusMessage = '';

    public function mount(CloudflareService $cloudflare)
    {
        $this->apiToken = config('services.cloudflare.api_token') ?? '';
        $this->accountId = config('services.cloudflare.account_id') ?? '';
        $this->checkConnection($cloudflare);
    }

    public function checkConnection(CloudflareService $cloudflare)
    {
        $this->configured = $cloudflare->isConfigured();
        if ($this->configured) {
            try {
                $this->zones = $cloudflare->listZones();
                $this->statusMessage = count($this->zones) . ' zones found in Cloudflare';
            } catch (\Exception $e) {
                $this->statusMessage = 'Connection failed: ' . $e->getMessage();
                $this->configured = false;
            }
        }
    }

    public function save()
    {
        $this->validate([
            'apiToken' => 'required|string',
            'accountId' => 'nullable|string',
        ]);

        // Write to .env
        $envFile = base_path('.env');
        $content = file_get_contents($envFile);

        $this->setEnvValue($content, 'CLOUDFLARE_API_TOKEN', $this->apiToken);
        $this->setEnvValue($content, 'CLOUDFLARE_ACCOUNT_ID', $this->accountId);

        file_put_contents($envFile, $content);

        session()->flash('message', 'Cloudflare credentials saved!');
        $this->dispatch('$refresh');
    }

    protected function setEnvValue(string &$content, string $key, string $value): void
    {
        $pattern = "/^{$key}=.*/m";
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, "{$key}={$value}", $content);
        } else {
            $content .= "\n{$key}={$value}";
        }
    }

    public function render()
    {
        return view('livewire.settings.cloudflare');
    }
}

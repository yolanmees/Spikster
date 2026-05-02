<?php

namespace App\Services\Providers;

class ProviderManager
{
    protected array $providers = [];

    public function __construct()
    {
        $this->register('digitalocean', app(DigitalOceanProvider::class));

        if (config('providers.vultr.token')) {
            $this->register('vultr', app(VultrProvider::class));
        }
        if (config('providers.linode.token')) {
            $this->register('linode', app(LinodeProvider::class));
        }
        if (config('providers.aws.token')) {
            $this->register('aws', app(AwsProvider::class));
        }
    }

    public function register(string $name, CloudProviderInterface $provider): void
    {
        $this->providers[$name] = $provider;
    }

    public function get(string $name): CloudProviderInterface
    {
        if (! isset($this->providers[$name])) {
            throw new \InvalidArgumentException("Provider '{$name}' is not registered");
        }
        return $this->providers[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->providers[$name]);
    }

    public function all(): array
    {
        return $this->providers;
    }

    public function getAvailableProviders(): array
    {
        return array_map(fn ($p) => $p->getName(), $this->providers);
    }
}

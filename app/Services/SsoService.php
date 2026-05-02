<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SsoService
{
    protected array $providers = [];

    public function __construct()
    {
        $this->providers = config('sso.providers', []);
    }

    public function getProvider(string $name): ?array
    {
        return $this->providers[$name] ?? null;
    }

    public function getRegisteredProviders(): array
    {
        return array_keys($this->providers);
    }

    public function isEnabled(): bool
    {
        return config('sso.enabled', false) && ! empty($this->providers);
    }

    public function handleCallback(string $provider, array $attributes): User
    {
        $email = $attributes['email'] ?? null;

        if (! $email) {
            throw new \RuntimeException("SSO callback missing email from provider '{$provider}'");
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $attributes['name'] ?? explode('@', $email)[0],
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ]);
            $user->assignRole(config('sso.default_role', 'Customer'));
        }

        return $user;
    }

    public function getAuthorizationUrl(string $provider): string
    {
        $config = $this->getProvider($provider);

        if (! $config) {
            throw new \RuntimeException("SSO provider '{$provider}' is not configured");
        }

        return $config['authorize_url'] ?? throw new \RuntimeException("Provider '{$provider}' has no authorize_url configured");
    }
}

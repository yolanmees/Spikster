<?php

namespace App\Services;

use App\Models\SetupToken;
use App\Models\Server;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SetupWizardService
{
    public function getSetupState(string $token): array
    {
        $setupToken = SetupToken::where('token', $token)->first();

        if (! $setupToken || $setupToken->isExpired() || $setupToken->used_at) {
            return ['valid' => false, 'completed_steps' => []];
        }

        $completed = [];

        if (User::count() > 0) {
            $completed[] = 'admin_user';
        }
        if (Server::count() > 0) {
            $completed[] = 'server';
        }
        if (Site::count() > 0) {
            $completed[] = 'site';
        }

        return [
            'valid' => true,
            'token' => $setupToken->token,
            'completed_steps' => $completed,
            'total_steps' => ['admin_user', 'server', 'site', 'ssl'],
            'has_admin' => in_array('admin_user', $completed),
            'has_server' => in_array('server', $completed),
            'has_site' => in_array('site', $completed),
        ];
    }

    public function completeStep(string $token, string $step, array $data): array
    {
        $setupToken = SetupToken::where('token', $token)->firstOrFail();

        if ($setupToken->isExpired() || $setupToken->used_at) {
            throw new \RuntimeException('Setup token is expired or already used.');
        }

        return match ($step) {
            'admin_user' => $this->createAdminUser($setupToken, $data),
            'server' => $this->createInitialServer($setupToken, $data),
            'ssl' => $this->enablePanelSsl($setupToken, $data),
            default => throw new \InvalidArgumentException("Unknown setup step: {$step}"),
        };
    }

    protected function createAdminUser(SetupToken $token, array $data): array
    {
        $user = User::create([
            'name' => $data['name'] ?? 'Admin',
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->assignRole('Super Admin');

        return ['step' => 'admin_user', 'completed' => true, 'user_id' => $user->id];
    }

    protected function createInitialServer(SetupToken $token, array $data): array
    {
        $server = Server::create([
            'server_id' => 'srv_'.Str::random(16),
            'name' => $data['name'] ?? 'Main Server',
            'ip' => $data['ip'],
            'password' => $data['password'] ?? Str::random(24),
            'database' => $data['db_password'] ?? Str::random(24),
            'provider' => $data['provider'] ?? 'manual',
            'status' => 1,
            'default' => true,
        ]);

        return ['step' => 'server', 'completed' => true, 'server_id' => $server->server_id];
    }

    protected function enablePanelSsl(SetupToken $token, array $data): array
    {
        $server = Server::where('default', true)->first();
        if (! $server) {
            throw new \RuntimeException('No default server found. Complete the server step first.');
        }

        $token->markAsUsed();

        return ['step' => 'ssl', 'completed' => true, 'message' => 'Setup completed successfully.'];
    }
}

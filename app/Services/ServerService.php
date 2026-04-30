<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Server Service
 *
 * Handles all server-related business logic.
 */
class ServerService
{
    /**
     * Get all servers query builder.
     */
    public function getAllServersQuery(): Builder
    {
        return Server::query();
    }

    /**
     * Get all servers for the authenticated user.
     */
    public function getAllServers(): Collection
    {
        return Server::all();
    }

    /**
     * Get a server by ID.
     */
    public function getServerById(string $serverId): ?Server
    {
        return Server::where('server_id', $serverId)->first();
    }

    /**
     * Get the default server.
     */
    public function getDefaultServer(): ?Server
    {
        return Server::where('default', true)->first();
    }

    /**
     * Create a new server.
     */
    public function createServer(array $data): Server
    {
        // Generate unique server ID
        $data['server_id'] = $this->generateServerId();

        // Set default values
        $data['status'] = $data['status'] ?? 1;
        $data['php'] = $data['php'] ?? config('spikster.default_php');
        $data['default'] = $data['default'] ?? false;

        return Server::create($data);
    }

    /**
     * Update a server.
     */
    public function updateServer(Server $server, array $data): Server
    {
        $server->update($data);

        return $server->fresh();
    }

    /**
     * Delete a server.
     */
    public function deleteServer(Server $server): bool
    {
        // Check if server has sites
        if ($server->allsites()->count() > 0) {
            throw new \Exception('Cannot delete server with existing sites');
        }

        return $server->delete();
    }

    /**
     * Get server statistics.
     */
    public function getServerStats(Server $server): array
    {
        return [
            'sites_count' => $server->sites()->count(),
            'total_sites_count' => $server->allsites()->count(),
            'is_active' => $server->isActive(),
            'is_default' => $server->isDefault(),
            'php_version' => $server->php,
            'build' => $server->build,
        ];
    }

    /**
     * Check if server is healthy.
     */
    public function checkServerHealth(Server $server): array
    {
        // This would typically include SSH connectivity checks, service status, etc.
        return [
            'status' => 'healthy',
            'services' => [
                'nginx' => 'running',
                'mysql' => 'running',
                'php-fpm' => 'running',
            ],
        ];
    }

    /**
     * Get all sites for a server.
     */
    public function getServerSites(Server $server, bool $includePanelSites = false): Collection
    {
        return $includePanelSites ? $server->allsites : $server->sites;
    }

    /**
     * Set server as default.
     */
    public function setAsDefault(Server $server): Server
    {
        // Remove default flag from all servers
        Server::where('default', true)->update(['default' => false]);

        // Set this server as default
        $server->update(['default' => true]);

        return $server->fresh();
    }

    /**
     * Generate a unique server ID.
     */
    protected function generateServerId(): string
    {
        do {
            $serverId = 'srv_'.Str::random(16);
        } while (Server::where('server_id', $serverId)->exists());

        return $serverId;
    }

    /**
     * Validate server connectivity.
     */
    public function validateConnectivity(string $ip, string $password): bool
    {
        // This would implement actual SSH connectivity check
        // For now, we'll return true as placeholder
        return true;
    }

    /**
     * Get server load information.
     */
    public function getServerLoad(Server $server): array
    {
        // This would fetch actual server load via SSH
        return [
            'cpu' => 0,
            'memory' => 0,
            'disk' => 0,
            'load_average' => '0.00',
        ];
    }

    /**
     * Install a package on the server.
     */
    public function installPackage(Server $server, string $packageName): bool
    {
        // This would dispatch an SSH job to install the package
        return true;
    }

    /**
     * Uninstall a package from the server.
     */
    public function uninstallPackage(Server $server, string $packageName): bool
    {
        // This would dispatch an SSH job to uninstall the package
        return true;
    }

    /**
     * Get installed packages on the server.
     */
    public function getInstalledPackages(Server $server): array
    {
        // This would fetch actual installed packages via SSH
        return [];
    }
}

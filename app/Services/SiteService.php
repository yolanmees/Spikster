<?php

namespace App\Services;

use App\Models\Alias;
use App\Models\Server;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Site Service
 *
 * Handles all site-related business logic.
 */
class SiteService
{
    /**
     * Get all sites query builder.
     */
    public function getAllSitesQuery(): Builder
    {
        return Site::query()->with('server');
    }

    /**
     * Get all sites.
     */
    public function getAllSites(): Collection
    {
        return Site::with('server')->get();
    }

    /**
     * Get a site by ID.
     */
    public function getSiteById(string $siteId): ?Site
    {
        return Site::where('site_id', $siteId)->first();
    }

    /**
     * Get sites for a specific server.
     */
    public function getSitesByServer(Server $server, bool $includePanelSites = false): Collection
    {
        return $includePanelSites
            ? $server->allsites
            : $server->sites;
    }

    /**
     * Create a new site.
     */
    public function createSite(array $data): Site
    {
        // Get server instance - use server_id from data
        $serverId = $data['server_id'];
        $server = Server::findOrFail($serverId);

        // Ensure server_id is set correctly as the internal database ID
        $data['server_id'] = $server->id;

        // Generate unique site ID
        $data['site_id'] = $this->generateSiteId();

        // Generate unique username if not provided
        if (! isset($data['username'])) {
            $data['username'] = config('spikster.users_prefix').hash('crc32', (Str::uuid()->toString())).rand(1, 9);
        }

        // Generate passwords
        if (! isset($data['password'])) {
            $data['password'] = Str::random(24);
        }

        if (! isset($data['database'])) {
            $data['database'] = Str::random(24);
        }

        // Set default values
        $data['php'] = $data['php'] ?? config('spikster.default_php');
        $data['basepath'] = $data['basepath'] ?? '/public';
        $data['panel'] = $data['panel'] ?? false;
        $data['deploy'] = $data['deploy'] ?? ' ';

        // Call the daemon first — if it fails we never write to the DB
        $daemonParams = [
            'id'       => $data['site_id'],
            'domain'   => $data['domain'],
            'username' => $data['username'],
            'password' => $data['password'],
            'db_name'  => $data['username'],
            'db_pass'  => $data['database'],
            'db_root'  => $server->database,
            'php'      => $data['php'],
            'basepath' => $data['basepath'] ?? '',
        ];

        $daemon  = app(\App\Services\DaemonService::class);
        $success = $daemon->createSite($daemonParams);

        if (! $success) {
            throw new \RuntimeException('Daemon failed to create site on the server.');
        }

        // Daemon succeeded — persist to DB
        // Wrap in try/catch so we can attempt cleanup if DB write fails
        try {
            $site = Site::create($data);
        } catch (\Throwable $e) {
            // Best-effort: remove what we just created on the server
            $daemon->deleteSite([
                'username' => $data['username'],
                'db_name'  => $data['username'],
                'db_root'  => $server->database,
                'php'      => $data['php'],
            ]);
            throw $e;
        }

        return $site;
    }

    /**
     * Update a site.
     */
    public function updateSite(Site $site, array $data): Site
    {
        // Prevent updating panel sites
        if ($site->isPanel() && ! ($data['allow_panel_update'] ?? false)) {
            throw new \Exception('Cannot update panel site');
        }

        unset($data['allow_panel_update']);
        $site->update($data);

        return $site->fresh();
    }

    /**
     * Delete a site.
     */
    public function deleteSite(Site $site): bool
    {
        // Prevent deleting panel sites
        if ($site->isPanel()) {
            throw new \Exception('Cannot delete panel site');
        }

        // Tell the daemon to remove the site from the server first
        app(\App\Services\DaemonService::class)->deleteSite([
            'username' => $site->username,
            'php'      => $site->php,
            'db_name'  => $site->username,
            'db_root'  => $site->server->database,
        ]);

        // Delete all aliases then the site record
        $site->aliases()->delete();

        return $site->delete();
    }

    /**
     * Get site statistics.
     */
    public function getSiteStats(Site $site): array
    {
        return [
            'aliases_count' => $site->aliases()->count(),
            'has_repository' => $site->hasRepository(),
            'is_panel' => $site->isPanel(),
            'php_version' => $site->php,
            'server' => [
                'name' => $site->server->name,
                'ip' => $site->server->ip,
            ],
        ];
    }

    /**
     * Create an alias for a site.
     */
    public function createAlias(Site $site, string $domain): Alias
    {
        // Check if domain already exists as site or alias
        $existingSite = Site::where('domain', $domain)->first();
        if ($existingSite) {
            throw new \Exception('Domain already exists as a site');
        }

        $existingAlias = Alias::where('domain', $domain)->first();
        if ($existingAlias) {
            throw new \Exception('Domain already exists as an alias');
        }

        return Alias::create([
            'alias_id' => $this->generateAliasId(),
            'site_id' => $site->id,
            'domain' => $domain,
        ]);
    }

    /**
     * Delete an alias.
     */
    public function deleteAlias(Alias $alias): bool
    {
        return $alias->delete();
    }

    /**
     * Get all aliases for a site.
     */
    public function getSiteAliases(Site $site): Collection
    {
        return $site->aliases;
    }

    /**
     * Enable SSL for a site.
     */
    public function enableSSL(Site $site): bool
    {
        // This would dispatch an SSL configuration job
        return true;
    }

    /**
     * Deploy a site from repository.
     */
    public function deploySite(Site $site): bool
    {
        if (! $site->hasRepository()) {
            throw new \Exception('Site does not have a repository configured');
        }

        // This would dispatch a deployment job
        return true;
    }

    /**
     * Reset site SSH password.
     */
    public function resetSSHPassword(Site $site, string $newPassword): Site
    {
        $site->update(['password' => $newPassword]);

        // This would dispatch an SSH password reset job
        return $site->fresh();
    }

    /**
     * Reset site database password.
     */
    public function resetDatabasePassword(Site $site, string $newPassword): Site
    {
        $site->update(['database' => $newPassword]);

        // This would dispatch a database password reset job
        return $site->fresh();
    }

    /**
     * Update site PHP version.
     */
    public function updatePHPVersion(Site $site, string $phpVersion): Site
    {
        $site->update(['php' => $phpVersion]);

        // This would dispatch a PHP version update job
        return $site->fresh();
    }

    /**
     * Update site repository.
     */
    public function updateRepository(Site $site, ?string $repository, ?string $branch = null): Site
    {
        $site->update([
            'repository' => $repository,
            'branch' => $branch ?? 'main',
        ]);

        return $site->fresh();
    }

    /**
     * Generate a unique site ID.
     */
    protected function generateSiteId(): string
    {
        do {
            $siteId = 'ste_'.Str::random(16);
        } while (Site::where('site_id', $siteId)->exists());

        return $siteId;
    }

    /**
     * Generate a unique alias ID.
     */
    protected function generateAliasId(): string
    {
        do {
            $aliasId = 'als_'.Str::random(16);
        } while (Alias::where('alias_id', $aliasId)->exists());

        return $aliasId;
    }

    /**
     * Generate a unique username based on domain.
     */
    protected function generateUsername(string $domain): string
    {
        $baseUsername = Str::slug(Str::before($domain, '.'), '');
        $username = substr($baseUsername, 0, 8);

        $counter = 1;
        $originalUsername = $username;

        while (Site::where('username', $username)->exists()) {
            $username = $originalUsername.$counter;
            $counter++;
        }

        return $username;
    }
}

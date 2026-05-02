<?php

namespace App\Services;

use App\Models\Alias;
use App\Models\Server;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
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
        Log::info('SiteService: starting site creation', [
            'domain' => $data['domain'] ?? 'unknown',
            'server_id' => $data['server_id'] ?? 'unknown',
            'php' => $data['php'] ?? 'default',
        ]);

        // Bump PHP time limit — daemon provisioning (useradd, chpasswd, mkdir,
        // nginx config, php-fpm pool, systemctl reload, mysql) can exceed 30s
        // on slow VPS instances.
        $previousTimeLimit = ini_get('max_execution_time');
        set_time_limit(120);

        // Get server instance - lookup by UUID string server_id
        $server = Server::find($data['server_id']);
        if (! $server) {
            Log::error('SiteService: server not found', [
                'server_id' => $data['server_id'],
            ]);
            throw new \RuntimeException('Server not found with id: '.$data['server_id']);
        }

        Log::info('SiteService: server found', [
            'server_name' => $server->name,
            'server_ip' => $server->ip,
            'server_id_db' => $server->id,
        ]);

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

        Log::info('SiteService: generated site credentials', [
            'site_id' => $data['site_id'],
            'username' => $data['username'],
            'php' => $data['php'],
            'basepath' => $data['basepath'],
        ]);

        // Call the daemon first — if it fails we never write to the DB
        $daemonParams = [
            'id' => $data['site_id'],
            'domain' => $data['domain'],
            'username' => $data['username'],
            'password' => $data['password'],
            'db_name' => $data['username'],
            'db_pass' => $data['database'],
            'db_root' => config('database.connections.mysql.password'),
            'php' => $data['php'],
            'basepath' => $data['basepath'],
        ];

        Log::info('SiteService: calling daemon to provision site', [
            'site_id' => $data['site_id'],
            'domain' => $data['domain'],
            'db_root_set' => ! empty($daemonParams['db_root']),
        ]);

        $daemonStart = microtime(true);
        $daemon = app(DaemonService::class);
        $success = $daemon->createSite($daemonParams);
        $daemonElapsed = round((microtime(true) - $daemonStart) * 1000);

        Log::info('SiteService: daemon call completed', [
            'site_id' => $data['site_id'],
            'success' => $success,
            'elapsed_ms' => $daemonElapsed,
        ]);

        if (! $success) {
            Log::error('SiteService: daemon failed to create site', [
                'site_id' => $data['site_id'],
                'domain' => $data['domain'],
                'elapsed_ms' => $daemonElapsed,
            ]);
            throw new \RuntimeException('Daemon failed to create site on the server.');
        }

        // Daemon succeeded — persist to DB
        // Wrap in try/catch so we can attempt cleanup if DB write fails
        try {
            $site = Site::create($data);
            Log::info('SiteService: site record created in database', [
                'site_id' => $site->site_id,
                'domain' => $site->domain,
                'db_id' => $site->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('SiteService: database insert failed after daemon success', [
                'site_id' => $data['site_id'],
                'domain' => $data['domain'],
                'error' => $e->getMessage(),
            ]);

            // Best-effort: remove what we just created on the server
            try {
                $daemon->deleteSite([
                    'username' => $data['username'],
                    'db_name' => $data['username'],
                    'db_root' => config('database.connections.mysql.password'),
                    'php' => $data['php'],
                ]);
                Log::info('SiteService: cleanup after DB failure completed');
            } catch (\Throwable $cleanupError) {
                Log::error('SiteService: cleanup after DB failure also failed', [
                    'error' => $cleanupError->getMessage(),
                ]);
            }
            throw $e;
        }

        AuditService::logCreate($site, "Site created: {$site->domain}");
        app(WebhookService::class)->dispatch('site.created', [
            'site_id' => $site->site_id,
            'domain' => $site->domain,
            'server_id' => $site->server_id,
        ]);

        // Restore original time limit
        set_time_limit((int) $previousTimeLimit);

        Log::info('SiteService: site creation completed successfully', [
            'site_id' => $site->site_id,
            'domain' => $site->domain,
            'total_elapsed_ms' => round((microtime(true) - LARAVEL_START) * 1000),
        ]);

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
        app(DaemonService::class)->deleteSite([
            'username' => $site->username,
            'php' => $site->php,
            'db_name' => $site->username,
            'db_root' => $site->server->database,
        ]);

        // Delete all aliases then the site record
        $site->aliases()->delete();

        AuditService::logDelete($site, "Site deleted: {$site->domain}");
        app(WebhookService::class)->dispatch('site.deleted', [
            'site_id' => $site->site_id,
            'domain' => $site->domain,
            'server_id' => $site->server_id,
        ]);

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

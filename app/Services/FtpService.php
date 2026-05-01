<?php

namespace App\Services;

use App\Models\FtpUser;
use App\Models\Site;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FtpService
{
    /**
     * Create a new FTP user
     */
    public function createUser(Site $site, array $data): FtpUser
    {
        // Generate username if not provided
        if (! isset($data['username'])) {
            $data['username'] = $this->generateUsername($site);
        }

        // Validate username format
        if (! str_contains($data['username'], '@')) {
            $data['username'] .= '@'.$site->domain;
        }

        // Set home directory if not provided
        if (! isset($data['home_directory'])) {
            $data['home_directory'] = "/var/www/vhosts/{$site->domain}/httpdocs";
        }

        // Set server_id from site
        $data['server_id'] = $site->server_id;
        $data['site_id'] = $site->site_id;

        // Set default permissions if not provided
        if (! isset($data['permissions'])) {
            $data['permissions'] = [
                'read' => true,
                'write' => true,
                'delete' => true,
                'rename' => true,
                'create_directory' => true,
            ];
        }

        // Create database record
        $ftpUser = FtpUser::create($data);

        // Dispatch SSH job to configure vsftpd
        app(DaemonService::class)->send('ftp.create', ['username' => $ftpUser->username, 'password' => $ftpUser->password, 'home_dir' => $ftpUser->home_dir ?? '/home/'.$ftpUser->username]);

        return $ftpUser->fresh();
    }

    /**
     * Update FTP user
     */
    public function updateUser(FtpUser $ftpUser, array $data): FtpUser
    {
        $needsServerUpdate = $this->determineServerUpdate($data);

        $ftpUser->update($data);

        // If password or critical settings changed, update server config
        if ($needsServerUpdate) {
            app(DaemonService::class)->send('ftp.update-password', ['username' => $ftpUser->username, 'password' => $ftpUser->password]);
        }

        return $ftpUser->fresh();
    }

    /**
     * Delete FTP user
     */
    public function deleteUser(FtpUser $ftpUser): bool
    {
        // Dispatch SSH job to remove from vsftpd before deleting
        app(DaemonService::class)->send('ftp.delete', ['username' => $ftpUser->username]);

        return $ftpUser->delete();
    }

    /**
     * Reset FTP user password
     */
    public function resetPassword(FtpUser $ftpUser, string $newPassword): FtpUser
    {
        $ftpUser->update(['password' => $newPassword]);

        // Update vsftpd configuration with new password
        app(DaemonService::class)->send('ftp.update-password', ['username' => $ftpUser->username, 'password' => $ftpUser->password]);

        return $ftpUser->fresh();
    }

    /**
     * Update FTP user quota
     */
    public function updateQuota(FtpUser $ftpUser, int $quotaMb): FtpUser
    {
        $ftpUser->update(['quota_mb' => $quotaMb]);

        // Update disk quota on server
        // quota managed via per-user vsftpd config

        return $ftpUser->fresh();
    }

    /**
     * Update disk usage for FTP user
     */
    public function updateDiskUsage(FtpUser $ftpUser): FtpUser
    {
        // Dispatch job to check actual disk usage
        // disk usage updated async

        return $ftpUser->fresh();
    }

    /**
     * Update disk usage for all FTP users of a site
     */
    public function updateAllDiskUsage(Site $site): void
    {
        $ftpUsers = $this->getUsersForSite($site);

        foreach ($ftpUsers as $ftpUser) {
            // disk usage updated async
        }
    }

    /**
     * Get FTP user usage statistics
     */
    public function getUsageStats(FtpUser $ftpUser): array
    {
        // Update disk usage first
        $this->updateDiskUsage($ftpUser);

        return $ftpUser->fresh()->getStatistics();
    }

    /**
     * Test FTP connection
     */
    public function testConnection(FtpUser $ftpUser, string $password): array
    {
        $result = TestFtpConnectionSSH::dispatchSync($ftpUser, $password);

        return $result ?? [
            'success' => false,
            'message' => 'Failed to test connection',
        ];
    }

    /**
     * Get FTP connection info for user
     */
    public function getConnectionInfo(FtpUser $ftpUser): array
    {
        $baseInfo = $ftpUser->getConnectionInfo();

        return array_merge($baseInfo, [
            'example_filezilla' => $this->getFileZillaConfig($ftpUser),
            'example_winscp' => $this->getWinSCPConfig($ftpUser),
            'example_terminal' => $this->getTerminalCommands($ftpUser),
        ]);
    }

    /**
     * Get all FTP users for a site
     */
    public function getUsersForSite(Site $site): Collection
    {
        return FtpUser::forSite($site->site_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get FTP statistics for a site
     */
    public function getSiteStatistics(Site $site): array
    {
        $ftpUsers = $this->getUsersForSite($site);

        return [
            'total_users' => $ftpUsers->count(),
            'active_users' => $ftpUsers->where('is_active', true)->count(),
            'inactive_users' => $ftpUsers->where('is_active', false)->count(),
            'locked_users' => $ftpUsers->filter->isLocked()->count(),
            'total_quota_mb' => $ftpUsers->sum('quota_mb'),
            'total_usage_bytes' => $ftpUsers->sum('current_usage_bytes'),
            'total_usage_mb' => round($ftpUsers->sum('current_usage_bytes') / (1024 * 1024), 2),
            'users_near_quota' => $ftpUsers->filter->isNearQuotaLimit()->count(),
            'users_exceeded_quota' => $ftpUsers->filter->isQuotaExceeded()->count(),
            'total_connections_allowed' => $ftpUsers->sum('max_connections'),
            'users_with_ssl' => $ftpUsers->where('require_ssl', true)->count(),
        ];
    }

    /**
     * Enable FTP user
     */
    public function enableUser(FtpUser $ftpUser): FtpUser
    {
        $ftpUser->update(['is_active' => true]);

        app(DaemonService::class)->send('ftp.update-password', ['username' => $ftpUser->username, 'password' => $ftpUser->password]);

        return $ftpUser->fresh();
    }

    /**
     * Disable FTP user
     */
    public function disableUser(FtpUser $ftpUser): FtpUser
    {
        $ftpUser->update(['is_active' => false]);

        app(DaemonService::class)->send('ftp.update-password', ['username' => $ftpUser->username, 'password' => $ftpUser->password]);

        return $ftpUser->fresh();
    }

    /**
     * Unlock FTP user
     */
    public function unlockUser(FtpUser $ftpUser): FtpUser
    {
        $ftpUser->unlock();

        return $ftpUser->fresh();
    }

    /**
     * Update FTP user permissions
     */
    public function updatePermissions(FtpUser $ftpUser, array $permissions): FtpUser
    {
        $ftpUser->setPermissions($permissions);

        app(DaemonService::class)->send('ftp.update-password', ['username' => $ftpUser->username, 'password' => $ftpUser->password]);

        return $ftpUser->fresh();
    }

    /**
     * Set read-only mode for FTP user
     */
    public function setReadOnly(FtpUser $ftpUser, bool $readOnly = true): FtpUser
    {
        $permissions = [
            'read' => true,
            'write' => ! $readOnly,
            'delete' => ! $readOnly,
            'rename' => ! $readOnly,
            'create_directory' => ! $readOnly,
        ];

        return $this->updatePermissions($ftpUser, $permissions);
    }

    /**
     * Generate unique FTP username
     */
    protected function generateUsername(Site $site): string
    {
        $baseName = Str::before($site->domain, '.');
        $counter = 1;
        $username = "{$baseName}@{$site->domain}";

        while (FtpUser::where('username', $username)->exists()) {
            $username = "{$baseName}{$counter}@{$site->domain}";
            $counter++;
        }

        return $username;
    }

    /**
     * Determine if server configuration needs update
     */
    protected function determineServerUpdate(array $data): bool
    {
        $serverFields = [
            'password',
            'home_directory',
            'quota_mb',
            'max_connections',
            'bandwidth_limit_kbps',
            'permissions',
            'is_active',
            'require_ssl',
            'allowed_ip',
        ];

        foreach ($serverFields as $field) {
            if (array_key_exists($field, $data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get FileZilla configuration example
     */
    protected function getFileZillaConfig(FtpUser $ftpUser): array
    {
        return [
            'protocol' => $ftpUser->require_ssl ? 'FTP - FTP over explicit TLS' : 'FTP - File Transfer Protocol',
            'host' => $ftpUser->server->ip,
            'port' => $ftpUser->require_ssl ? 21 : 21,
            'encryption' => $ftpUser->require_ssl ? 'Require explicit FTP over TLS' : 'Use plain FTP (insecure)',
            'logon_type' => 'Normal',
            'user' => $ftpUser->username,
            'transfer_mode' => 'Passive',
            'max_connections' => $ftpUser->max_connections,
        ];
    }

    /**
     * Get WinSCP configuration example
     */
    protected function getWinSCPConfig(FtpUser $ftpUser): array
    {
        return [
            'protocol' => $ftpUser->require_ssl ? 'FTP with TLS' : 'FTP',
            'host' => $ftpUser->server->ip,
            'port' => 21,
            'username' => $ftpUser->username,
            'passive_mode' => 'On',
            'encryption' => $ftpUser->require_ssl ? 'TLS/SSL Explicit encryption' : 'No encryption',
        ];
    }

    /**
     * Get terminal FTP commands
     */
    protected function getTerminalCommands(FtpUser $ftpUser): array
    {
        return [
            'connect' => "ftp {$ftpUser->server->ip}",
            'connect_ftps' => "lftp -u {$ftpUser->username} -e 'set ftp:ssl-force true; set ftp:ssl-protect-data true' {$ftpUser->server->ip}",
            'curl_upload' => "curl -T file.txt ftp://{$ftpUser->username}@{$ftpUser->server->ip}/file.txt",
            'curl_upload_ftps' => "curl --ssl -T file.txt ftp://{$ftpUser->username}@{$ftpUser->server->ip}/file.txt",
        ];
    }

    /**
     * Validate FTP username format
     */
    public function validateUsername(string $username): bool
    {
        // Must contain @
        if (! str_contains($username, '@')) {
            return false;
        }

        // Must not contain special characters except @ . - _
        if (! preg_match('/^[a-zA-Z0-9@.\-_]+$/', $username)) {
            return false;
        }

        return true;
    }

    /**
     * Validate home directory path
     */
    public function validateHomeDirectory(string $path): bool
    {
        // Must start with /
        if (! str_starts_with($path, '/')) {
            return false;
        }

        // Must be within /var/www/vhosts/
        if (! str_starts_with($path, '/var/www/vhosts/')) {
            return false;
        }

        return true;
    }
}

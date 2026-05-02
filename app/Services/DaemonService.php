<?php

namespace App\Services;

/**
 * DaemonService
 *
 * Communicates with the local spikster Go daemon via Unix socket.
 * Used for service management on the panel server itself.
 * For remote servers, use SSHService instead.
 */
class DaemonService
{
    protected string $socketPath = '/var/run/spikster.sock';

    protected array $allowedServices = [
        'nginx', 'mysql', 'redis-server',
        'fail2ban', 'php8.2-fpm', 'php8.3-fpm', 'php8.4-fpm',
    ];

    /**
     * Send a request to the daemon.
     */
    public function send(string $action, array $params = []): array
    {
        if (! file_exists($this->socketPath)) {
            throw new \Exception('Spikster daemon is not running.');
        }

        $socket = stream_socket_client("unix://{$this->socketPath}", $errno, $errstr, 10);

        if (! $socket) {
            throw new \Exception("Cannot connect to daemon: {$errstr}");
        }

        // Allow up to 60s for long-running operations (site create, ssl, backup)
        stream_set_timeout($socket, 60);

        // Authenticate with daemon token before sending the payload
        $token = config('spikster.daemon_token');
        fwrite($socket, "TOKEN {$token}\n");

        $payload = json_encode(['action' => $action, 'params' => $params]);
        fwrite($socket, $payload);

        $response = '';
        while (! feof($socket)) {
            $response .= fread($socket, 4096);
        }
        fclose($socket);

        $decoded = json_decode($response, true);
        if (! $decoded) {
            throw new \Exception('Invalid response from daemon.');
        }

        return $decoded;
    }

    /**
     * Restart a service.
     */
    public function restart(string $service): bool
    {
        $this->guardService($service);
        $result = $this->send('restart', ['service' => $service]);

        return $result['success'] ?? false;
    }

    /**
     * Start a service.
     */
    public function start(string $service): bool
    {
        $this->guardService($service);
        $result = $this->send('start', ['service' => $service]);

        return $result['success'] ?? false;
    }

    /**
     * Stop a service.
     */
    public function stop(string $service): bool
    {
        $this->guardService($service);
        $result = $this->send('stop', ['service' => $service]);

        return $result['success'] ?? false;
    }

    /**
     * Get service status.
     */
    public function status(string $service): string
    {
        $this->guardService($service);
        $result = $this->send('status', ['service' => $service]);

        return $result['output'] ?? 'unknown';
    }

    /**
     * Check if daemon is reachable.
     */
    public function isAvailable(): bool
    {
        try {
            $this->send('status', ['service' => 'nginx']);

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    protected function guardService(string $service): void
    {
        if (! in_array($service, $this->allowedServices)) {
            throw new \InvalidArgumentException("Service not allowed: {$service}");
        }
    }

    // ─── Site management ──────────────────────────────────────────────────────

    public function createSite(array $params): bool
    {
        return $this->send('site.create', $params)['success'] ?? false;
    }

    public function deleteSite(array $params): bool
    {
        return $this->send('site.delete', $params)['success'] ?? false;
    }

    public function updateSitePHP(string $username, string $oldPHP, string $newPHP): bool
    {
        return $this->send('site.update-php', compact('username', 'oldPHP', 'newPHP'))['success'] ?? false;
    }

    public function updateSitePHPSettings($site): bool
    {
        return $this->send('site.php-settings', [
            'id' => $site->site_id,
            'domain' => $site->domain,
            'username' => $site->username,
            'password' => $site->password,
            'db_name' => $site->username,
            'db_pass' => $site->database,
            'db_root' => $site->server->database ?? '',
            'php' => $site->php,
            'basepath' => $site->basepath ?? '',
            'php_memory_limit' => $site->php_memory_limit ?? '',
            'php_upload_max_filesize' => $site->php_upload_max_filesize ?? '',
            'php_max_execution_time' => $site->php_max_execution_time ?? '',
            'php_max_input_vars' => $site->php_max_input_vars ?? '',
            'php_post_max_size' => $site->php_post_max_size ?? '',
        ])['success'] ?? false;
    }

    public function updateSiteNginxConfig($site): bool
    {
        return $this->send('site.nginx-config', [
            'id' => $site->site_id,
            'domain' => $site->domain,
            'username' => $site->username,
            'password' => $site->password,
            'db_name' => $site->username,
            'db_pass' => $site->database,
            'db_root' => $site->server->database ?? '',
            'php' => $site->php,
            'basepath' => $site->basepath ?? '',
            'nginx_config' => $site->nginx ?? '',
        ])['success'] ?? false;
    }

    public function updateSiteDomain(string $username, string $oldDomain, string $newDomain): bool
    {
        return $this->send('site.update-domain', compact('username', 'oldDomain', 'newDomain'))['success'] ?? false;
    }

    public function updateSiteBasepath(string $username, string $basepath): bool
    {
        return $this->send('site.update-basepath', compact('username', 'basepath'))['success'] ?? false;
    }

    public function enableSSL(string $username, string $domain): bool
    {
        return $this->send('site.ssl', compact('username', 'domain'))['success'] ?? false;
    }

    // ─── Alias management ─────────────────────────────────────────────────────

    public function createAlias(string $domain, string $username, string $php, string $basepath = ''): bool
    {
        return $this->send('alias.create', compact('domain', 'username', 'php', 'basepath'))['success'] ?? false;
    }

    public function deleteAlias(string $domain): bool
    {
        return $this->send('alias.delete', ['domain' => $domain])['success'] ?? false;
    }

    public function enableAliasSSL(string $domain): bool
    {
        return $this->send('alias.ssl', ['domain' => $domain])['success'] ?? false;
    }

    // ─── Cron management ──────────────────────────────────────────────────────

    public function writeCron(string $content): bool
    {
        return $this->send('cron.write', ['content' => $content])['success'] ?? false;
    }

    public function readCron(): string
    {
        return $this->send('cron.read', [])['output'] ?? '';
    }

    // ─── Email management ─────────────────────────────────────────────────────

    public function createEmailAccount(string $domain, string $username, string $email, string $passwordHash, int $quotaMb = 0): bool
    {
        return $this->send('email.create', compact('domain', 'username', 'email') + [
            'password_hash' => $passwordHash,
            'quota_mb' => (string) $quotaMb,
        ])['success'] ?? false;
    }

    public function deleteEmailAccount(string $domain, string $username, string $email): bool
    {
        return $this->send('email.delete', compact('domain', 'username', 'email'))['success'] ?? false;
    }

    public function updateEmailPassword(string $email, string $passwordHash): bool
    {
        return $this->send('email.update-password', ['email' => $email, 'password_hash' => $passwordHash])['success'] ?? false;
    }

    public function updateEmailQuota(string $email, int $quotaMb): bool
    {
        return $this->send('email.update-quota', ['email' => $email, 'quota_mb' => (string) $quotaMb])['success'] ?? false;
    }

    public function createEmailForwarder(string $source, string $destination): bool
    {
        return $this->send('email.forwarder-create', compact('source', 'destination'))['success'] ?? false;
    }

    public function deleteEmailForwarder(string $source): bool
    {
        return $this->send('email.forwarder-delete', ['source' => $source])['success'] ?? false;
    }

    public function createEmailAlias(string $alias, string $target): bool
    {
        return $this->send('email.alias-create', compact('alias', 'target'))['success'] ?? false;
    }

    public function deleteEmailAlias(string $alias): bool
    {
        return $this->send('email.alias-delete', ['alias' => $alias])['success'] ?? false;
    }

    /**
     * Setup DKIM. Returns ['private_key' => ..., 'public_key' => ...] or empty array on failure.
     */
    public function setupDKIM(string $domain, string $selector): array
    {
        $result = $this->send('email.dkim-setup', compact('domain', 'selector'));
        if (! ($result['success'] ?? false)) {
            return [];
        }
        $decoded = json_decode($result['output'] ?? '', true);

        return is_array($decoded) ? $decoded : [];
    }

    public function updateAutoresponder(
        string $domain, string $username, bool $enabled,
        string $subject = '', string $message = '',
        string $startDate = '', string $endDate = ''
    ): bool {
        return $this->send('email.autoresponder-update', [
            'domain' => $domain, 'username' => $username,
            'enabled' => $enabled ? 'true' : 'false',
            'subject' => $subject, 'message' => $message,
            'start_date' => $startDate, 'end_date' => $endDate,
        ])['success'] ?? false;
    }

    public function installRoundcube(
        string $domain, string $siteRoot, string $dbName,
        string $dbUser, string $dbPass, string $php
    ): bool {
        return $this->send('email.roundcube-install', [
            'domain' => $domain, 'site_root' => $siteRoot,
            'db_name' => $dbName, 'db_user' => $dbUser, 'db_pass' => $dbPass,
            'php' => $php,
        ])['success'] ?? false;
    }

    // ─── Fail2ban management ──────────────────────────────────────────────────

    public function fail2banBan(string $ip, string $jail = 'sshd'): bool
    {
        return $this->send('fail2ban.ban', ['ip' => $ip, 'jail' => $jail])['success'] ?? false;
    }

    public function fail2banUnban(string $ip, string $jail = ''): bool
    {
        return $this->send('fail2ban.unban', ['ip' => $ip, 'jail' => $jail])['success'] ?? false;
    }

    public function fail2banWhitelist(string $ip): bool
    {
        return $this->send('fail2ban.whitelist', ['ip' => $ip])['success'] ?? false;
    }

    public function supervisorCtl(string $action, string $process = ''): array
    {
        return $this->send('server.supervisorctl', compact('action', 'process'));
    }
}

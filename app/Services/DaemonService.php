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
        return ($this->send('site.create', $params))['success'] ?? false;
    }

    public function deleteSite(array $params): bool
    {
        return ($this->send('site.delete', $params))['success'] ?? false;
    }

    public function updateSitePHP(string $username, string $oldPHP, string $newPHP): bool
    {
        return ($this->send('site.update-php', compact('username', 'oldPHP', 'newPHP')))['success'] ?? false;
    }

    public function updateSiteDomain(string $username, string $oldDomain, string $newDomain): bool
    {
        return ($this->send('site.update-domain', compact('username', 'oldDomain', 'newDomain')))['success'] ?? false;
    }

    public function updateSiteBasepath(string $username, string $basepath): bool
    {
        return ($this->send('site.update-basepath', compact('username', 'basepath')))['success'] ?? false;
    }

    public function enableSSL(string $username, string $domain): bool
    {
        return ($this->send('site.ssl', compact('username', 'domain')))['success'] ?? false;
    }

    // ─── Alias management ─────────────────────────────────────────────────────

    public function createAlias(string $domain, string $username, string $php, string $basepath = ''): bool
    {
        return ($this->send('alias.create', compact('domain', 'username', 'php', 'basepath')))['success'] ?? false;
    }

    public function deleteAlias(string $domain): bool
    {
        return ($this->send('alias.delete', ['domain' => $domain]))['success'] ?? false;
    }

    public function enableAliasSSL(string $domain): bool
    {
        return ($this->send('alias.ssl', ['domain' => $domain]))['success'] ?? false;
    }

    // ─── Cron management ──────────────────────────────────────────────────────

    public function writeCron(string $content): bool
    {
        return ($this->send('cron.write', ['content' => $content]))['success'] ?? false;
    }

    public function readCron(): string
    {
        return ($this->send('cron.read', []))['output'] ?? '';
    }
}

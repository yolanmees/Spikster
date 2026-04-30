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

        $socket = stream_socket_client("unix://{$this->socketPath}", $errno, $errstr, 5);

        if (! $socket) {
            throw new \Exception("Cannot connect to daemon: {$errstr}");
        }

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
}

    // ─── Site management ──────────────────────────────────────────────────────

    /**
     * Create a new site via the daemon.
     */
    public function createSite(array $params): bool
    {
        $result = $this->send('site.create', $params);
        return $result['success'] ?? false;
    }

    /**
     * Delete a site via the daemon.
     */
    public function deleteSite(array $params): bool
    {
        $result = $this->send('site.delete', $params);
        return $result['success'] ?? false;
    }
}

<?php

namespace App\Services;

use App\Models\Server;

/**
 * RemoteDaemonService
 *
 * Connects to a spikster daemon running on a remote server via TCP.
 * The remote daemon listens on 127.0.0.1:18999 — access requires SSH tunnel.
 *
 * For the local (panel) server, use DaemonService (Unix socket) instead.
 */
class RemoteDaemonService
{
    protected int $port = 18999;
    protected int $timeout = 30;

    /**
     * Send a request to a remote server's daemon.
     */
    public function send(Server $server, string $action, array $params = []): array
    {
        // Open SSH tunnel to forward remote daemon port locally
        $localPort = $this->openTunnel($server);

        try {
            $socket = stream_socket_client(
                "tcp://127.0.0.1:{$localPort}",
                $errno, $errstr, $this->timeout
            );

            if (! $socket) {
                throw new \Exception("Cannot connect to remote daemon on {$server->ip}: {$errstr}");
            }

            stream_set_timeout($socket, $this->timeout);

            // Authenticate with daemon token before sending the JSON payload
            $token = config('spikster.daemon_token');
            fwrite($socket, "TOKEN {$token}\n");

            fwrite($socket, json_encode(['action' => $action, 'params' => $params]));

            $response = '';
            while (! feof($socket)) {
                $response .= fread($socket, 4096);
            }
            fclose($socket);

            $decoded = json_decode($response, true);
            if (! $decoded) {
                throw new \Exception('Invalid response from remote daemon.');
            }

            return $decoded;
        } finally {
            $this->closeTunnel($localPort);
        }
    }

    /**
     * Open SSH tunnel to remote daemon port.
     * Returns the local port to connect to.
     */
    protected function openTunnel(Server $server): int
    {
        $localPort = $this->getFreePort();

        $cmd = sprintf(
            'ssh -o StrictHostKeyChecking=no -o ConnectTimeout=10 '
            . '-fN -L %d:127.0.0.1:%d spikster@%s -i /etc/spikster/ssh_key 2>/dev/null',
            $localPort, $this->port, $server->ip
        );

        exec($cmd);
        usleep(500000); // 0.5s for tunnel to establish

        return $localPort;
    }

    protected function closeTunnel(int $localPort): void
    {
        exec("pkill -f 'ssh.*{$localPort}:127.0.0.1:{$this->port}'");
    }

    protected function getFreePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        $name = stream_socket_get_name($socket, false);
        fclose($socket);
        [, $port] = explode(':', $name);
        return (int) $port;
    }

    /**
     * Convenience: run a site action on a remote server.
     */
    public function createSite(Server $server, array $params): bool
    {
        return ($this->send($server, 'site.create', $params))['success'] ?? false;
    }

    public function deleteSite(Server $server, array $params): bool
    {
        return ($this->send($server, 'site.delete', $params))['success'] ?? false;
    }

    public function restartService(Server $server, string $service): bool
    {
        return ($this->send($server, 'restart', ['service' => $service]))['success'] ?? false;
    }

    public function doctor(Server $server): array
    {
        $result = $this->send($server, 'server.package-list', []);
        return $result;
    }

    /**
     * Install the spikster daemon on a remote server via SSH.
     * Only needed once per server — after this, all communication goes via daemon.
     */
    public function bootstrapServer(Server $server): bool
    {
        $script = 'curl -fsSL https://raw.githubusercontent.com/yolanmees/Spikster/v2-update/go.sh | bash';

        $ssh = new \phpseclib3\Net\SSH2($server->ip, 22);
        if (! $ssh->login('root', $server->password)) {
            throw new \Exception("SSH authentication failed for {$server->ip}");
        }
        $ssh->setTimeout(2400);
        $output = $ssh->exec($script);
        $ssh->exec('exit');

        return str_contains($output, 'SETUP COMPLETE');
    }
}

<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SSH2;

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
        $sshKey = '/etc/spikster/ssh_key';
        $knownHosts = '/etc/spikster/known_hosts';

        // Create known_hosts file if it doesn't exist
        if (! is_file($knownHosts)) {
            touch($knownHosts);
            chmod($knownHosts, 0600);
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $cmd = sprintf(
            'ssh -o StrictHostKeyChecking=accept-new -o UserKnownHostsFile=%s '
            .'-o ConnectTimeout=10 -o ExitOnForwardFailure=yes '
            .'-fN -L %d:127.0.0.1:%d spikster@%s -i %s',
            escapeshellarg($knownHosts),
            $localPort, $this->port,
            escapeshellarg($server->ip),
            escapeshellarg($sshKey)
        );

        $process = proc_open($cmd, $descriptors, $pipes);
        if (! is_resource($process)) {
            throw new \Exception("Failed to open SSH tunnel to {$server->ip}");
        }

        fclose($pipes[0]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new \Exception("SSH tunnel failed for {$server->ip}: {$stderr}");
        }

        usleep(500000); // 0.5s for tunnel to establish

        return $localPort;
    }

    protected function closeTunnel(int $localPort): void
    {
        $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $cmd = sprintf('ssh -O stop -L %d:127.0.0.1:%d 127.0.0.1 2>/dev/null', $localPort, $this->port);

        $process = proc_open($cmd, $descriptors, $pipes);
        if (is_resource($process)) {
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }
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
        return $this->send($server, 'site.create', $params)['success'] ?? false;
    }

    public function deleteSite(Server $server, array $params): bool
    {
        return $this->send($server, 'site.delete', $params)['success'] ?? false;
    }

    public function restartService(Server $server, string $service): bool
    {
        return $this->send($server, 'restart', ['service' => $service])['success'] ?? false;
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

        $ssh = new SSH2($server->ip, 22);
        if (! $ssh->login('root', $server->password)) {
            throw new \Exception("SSH authentication failed for {$server->ip}");
        }
        $ssh->setTimeout(2400);
        $output = $ssh->exec($script);
        $ssh->exec('exit');

        return str_contains($output, 'SETUP COMPLETE');
    }

    // ─── Backup management ────────────────────────────────────────────────────

    public function createBackup(Server $server, array $params): array
    {
        return $this->send($server, 'backup.create', $params);
    }

    public function restoreBackup(Server $server, array $params): array
    {
        return $this->send($server, 'backup.restore', $params);
    }

    public function encryptBackup(Server $server, array $params): array
    {
        return $this->send($server, 'backup.encrypt', $params);
    }

    public function uploadBackupToFtp(Server $server, array $params): array
    {
        return $this->send($server, 'backup.upload-ftp', $params);
    }

    public function uploadBackupToS3(Server $server, array $params): array
    {
        return $this->send($server, 'backup.upload-s3', $params);
    }

    // ─── FTP management ───────────────────────────────────────────────────────

    public function createFtpUser(Server $server, array $params): bool
    {
        return $this->send($server, 'ftp.create', $params)['success'] ?? false;
    }

    public function deleteFtpUser(Server $server, string $username): bool
    {
        return $this->send($server, 'ftp.delete', ['username' => $username])['success'] ?? false;
    }

    public function updateFtpUser(Server $server, array $params): bool
    {
        return $this->send($server, 'ftp.update-password', $params)['success'] ?? false;
    }

    public function getFtpDiskUsage(Server $server, string $username, string $homeDirectory): int
    {
        $result = $this->send($server, 'ftp.disk-usage', [
            'username' => $username,
            'home_directory' => $homeDirectory,
        ]);

        return (int) ($result['usage_bytes'] ?? 0);
    }

    public function updateFtpQuota(Server $server, string $username, string $homeDirectory, int $quotaMb): bool
    {
        return $this->send($server, 'ftp.update-quota', compact('username', 'homeDirectory', 'quotaMb'))['success'] ?? false;
    }

    public function testFtpConnection(Server $server, string $username, string $password): array
    {
        return $this->send($server, 'ftp.test', [
            'username' => $username,
            'password' => $password,
            'host' => '127.0.0.1',
        ]);
    }
    // ─── Email management ─────────────────────────────────────────────────────

    public function createEmailAccount(Server $server, string $domain, string $username, string $email, string $passwordHash, int $quotaMb = 0): bool
    {
        return $this->send($server, 'email.create', compact('domain', 'username', 'email') + [
            'password_hash' => $passwordHash,
            'quota_mb' => (string) $quotaMb,
        ])['success'] ?? false;
    }

    public function deleteEmailAccount(Server $server, string $domain, string $username, string $email): bool
    {
        return $this->send($server, 'email.delete', compact('domain', 'username', 'email'))['success'] ?? false;
    }

    public function updateEmailPassword(Server $server, string $email, string $passwordHash): bool
    {
        return $this->send($server, 'email.update-password', ['email' => $email, 'password_hash' => $passwordHash])['success'] ?? false;
    }

    public function updateEmailQuota(Server $server, string $email, int $quotaMb): bool
    {
        return $this->send($server, 'email.update-quota', ['email' => $email, 'quota_mb' => (string) $quotaMb])['success'] ?? false;
    }

    /**
     * Configure SpamAssassin and ClamAV filters for a mailbox.
     */
    public function setEmailFilters(
        Server $server,
        string $email,
        bool $spamFilter,
        float $spamScore,
        bool $antivirus
    ): bool {
        return $this->send($server, 'email.set-filters', [
            'email' => $email,
            'spam_filter' => $spamFilter ? 'true' : 'false',
            'spam_score' => (string) $spamScore,
            'antivirus' => $antivirus ? 'true' : 'false',
        ])['success'] ?? false;
    }

    public function createEmailForwarder(Server $server, string $source, string $destination): bool
    {
        return $this->send($server, 'email.forwarder-create', compact('source', 'destination'))['success'] ?? false;
    }

    public function deleteEmailForwarder(Server $server, string $source): bool
    {
        return $this->send($server, 'email.forwarder-delete', ['source' => $source])['success'] ?? false;
    }

    public function createEmailAlias(Server $server, string $alias, string $target): bool
    {
        return $this->send($server, 'email.alias-create', compact('alias', 'target'))['success'] ?? false;
    }

    public function deleteEmailAlias(Server $server, string $alias): bool
    {
        return $this->send($server, 'email.alias-delete', ['alias' => $alias])['success'] ?? false;
    }

    /**
     * @return array{private_key: string, public_key: string}|array{}
     */
    public function setupDKIM(Server $server, string $domain, string $selector): array
    {
        $result = $this->send($server, 'email.dkim-setup', compact('domain', 'selector'));
        if (! ($result['success'] ?? false)) {
            return [];
        }
        $decoded = json_decode($result['output'] ?? '', true);

        return is_array($decoded) ? $decoded : [];
    }

    public function updateAutoresponder(
        Server $server, string $domain, string $username, bool $enabled,
        string $subject = '', string $message = '',
        string $startDate = '', string $endDate = ''
    ): bool {
        return $this->send($server, 'email.autoresponder-update', [
            'domain' => $domain, 'username' => $username,
            'enabled' => $enabled ? 'true' : 'false',
            'subject' => $subject, 'message' => $message,
            'start_date' => $startDate, 'end_date' => $endDate,
        ])['success'] ?? false;
    }

    public function installRoundcube(
        Server $server, string $domain, string $siteRoot,
        string $dbName, string $dbUser, string $dbPass, string $php
    ): bool {
        return $this->send($server, 'email.roundcube-install', [
            'domain' => $domain, 'site_root' => $siteRoot,
            'db_name' => $dbName, 'db_user' => $dbUser, 'db_pass' => $dbPass,
            'php' => $php,
        ])['success'] ?? false;
    }

    // ─── Fail2ban management ──────────────────────────────────────────────────

    public function fail2banBan(Server $server, string $ip, string $jail = 'sshd'): bool
    {
        return $this->send($server, 'fail2ban.ban', ['ip' => $ip, 'jail' => $jail])['success'] ?? false;
    }

    public function fail2banUnban(Server $server, string $ip, string $jail = ''): bool
    {
        return $this->send($server, 'fail2ban.unban', ['ip' => $ip, 'jail' => $jail])['success'] ?? false;
    }

    public function fail2banWhitelist(Server $server, string $ip): bool
    {
        return $this->send($server, 'fail2ban.whitelist', ['ip' => $ip])['success'] ?? false;
    }

    // ─── Deployment ───────────────────────────────────────────────────────────

    public function deploySite(Server $server, array $params): array
    {
        return $this->send($server, 'site.deploy', $params);
    }

    public function rollbackDeploy(Server $server, string $username, string $commitHash): array
    {
        return $this->send($server, 'site.deploy-rollback', [
            'username' => $username,
            'commit_hash' => $commitHash,
        ]);
    }

    public function deployHistory(Server $server, string $username, int $count = 10): array
    {
        return $this->send($server, 'site.deploy-history', [
            'username' => $username,
            'count' => (string) $count,
        ]);
    }

    // ─── File operations ──────────────────────────────────────────────────────

    public function deleteDirectory(Server $server, string $path): bool
    {
        return $this->send($server, 'file.delete-dir', ['path' => $path])['success'] ?? false;
    }

    public function uploadFile(Server $server, string $path, string $content): bool
    {
        return $this->send($server, 'file.upload', [
            'path' => $path,
            'content' => $content,
        ])['success'] ?? false;
    }

    public function changeOwnership(Server $server, string $path, string $owner, string $group = ''): bool
    {
        return $this->send($server, 'file.chown', [
            'path' => $path,
            'owner' => $owner,
            'group' => $group,
        ])['success'] ?? false;
    }

    // ─── Log rotation ─────────────────────────────────────────────────────────

    public function rotateLogs(Server $server, string $day = ''): array
    {
        return $this->send($server, 'log.rotate', ['day' => $day]);
    }

    // ─── Mail queue & log management ─────────────────────────────────────────

    /**
     * List messages in the Postfix mail queue, optionally filtered by domain.
     *
     * @return array{queue: list<array{id: string, sender: string, recipient: string, size: int, arrival: string, reason: string}>}
     */
    public function mailQueueList(Server $server, string $domain = ''): array
    {
        return $this->send($server, 'email.queue-list', ['domain' => $domain]);
    }

    /**
     * Flush (retry) a specific message in the mail queue.
     */
    public function mailQueueRetry(Server $server, string $queueId): bool
    {
        return $this->send($server, 'email.queue-retry', ['queue_id' => $queueId])['success'] ?? false;
    }

    /**
     * Delete a specific message from the mail queue.
     */
    public function mailQueueDelete(Server $server, string $queueId): bool
    {
        return $this->send($server, 'email.queue-delete', ['queue_id' => $queueId])['success'] ?? false;
    }

    /**
     * Tail the mail log, optionally filtered by domain and substring.
     *
     * @return array{lines: list<string>}
     */
    public function mailLogTail(Server $server, string $domain = '', int $lines = 100, string $filter = ''): array
    {
        return $this->send($server, 'email.log-tail', [
            'domain' => $domain,
            'lines' => $lines,
            'filter' => $filter,
        ]);
    }
}

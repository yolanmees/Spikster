<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\Log;

class Fail2banService
{
    protected DaemonService $daemon;

    public function __construct(DaemonService $daemon)
    {
        $this->daemon = $daemon;
    }

    /**
     * Get all banned IPs via daemon.
     */
    public function getBannedIps(Server $server): array
    {
        try {
            $result = $this->daemon->send('server.fail2ban-list');
            if (! ($result['success'] ?? false)) {
                return [];
            }
            return $this->parseFailbanOutput($result['output'] ?? '');
        } catch (\Throwable $e) {
            Log::error('Fail2ban getBannedIps error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all available Fail2ban jails.
     */
    public function getJails(Server $server): array
    {
        try {
            $banned = $this->getBannedIps($server);
            $jails  = [];
            foreach ($banned as $entry) {
                $jail = $entry['jail'] ?? 'sshd';
                if (! in_array($jail, array_column($jails, 'name'))) {
                    $jails[] = ['name' => $jail];
                }
            }
            return $jails ?: [['name' => 'sshd']];
        } catch (\Throwable $e) {
            Log::error('Fail2ban getJails error: ' . $e->getMessage());
            return [['name' => 'sshd']];
        }
    }

    /**
     * Get jail status (returns basic info).
     */
    public function getJailStatus(Server $server, string $jail): array
    {
        $banned = array_filter($this->getBannedIps($server), fn($b) => ($b['jail'] ?? '') === $jail);
        return ['jail' => $jail, 'banned_count' => count($banned), 'banned' => array_values($banned)];
    }

    /**
     * Ban an IP address.
     */
    public function banIp(Server $server, string $ip, string $jail = 'sshd'): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception("Invalid IP address: {$ip}");
        }
        try {
            $result = $this->daemon->send('fail2ban.ban', ['ip' => $ip, 'jail' => $jail]);
            return $result['success'] ?? false;
        } catch (\Throwable $e) {
            Log::error('Fail2ban banIp error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Unban an IP address.
     */
    public function unbanIp(Server $server, string $ip, ?string $jail = null): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception("Invalid IP address: {$ip}");
        }
        try {
            $result = $this->daemon->send('fail2ban.unban', ['ip' => $ip, 'jail' => $jail ?? '']);
            return $result['success'] ?? false;
        } catch (\Throwable $e) {
            Log::error('Fail2ban unbanIp error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if an IP is banned.
     */
    public function isIpBanned(Server $server, string $ip): array
    {
        return array_values(array_filter(
            $this->getBannedIps($server),
            fn($b) => ($b['ip'] ?? '') === $ip
        ));
    }

    /**
     * Whitelist an IP address.
     */
    public function whitelistIp(Server $server, string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception("Invalid IP address: {$ip}");
        }
        try {
            $result = $this->daemon->send('fail2ban.whitelist', ['ip' => $ip]);
            return $result['success'] ?? false;
        } catch (\Throwable $e) {
            Log::error('Fail2ban whitelistIp error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get whitelisted IPs (from daemon config if available).
     */
    public function getWhitelistedIps(Server $server): array
    {
        return [];
    }

    /**
     * Get fail2ban service status.
     */
    public function getServiceStatus(Server $server): array
    {
        try {
            $status = $this->daemon->status('fail2ban');
            return [
                'active'  => str_contains($status, 'active') || str_contains($status, 'running'),
                'status'  => $status,
            ];
        } catch (\Throwable) {
            return ['active' => false, 'status' => 'unknown'];
        }
    }

    /**
     * Get statistics (count of banned IPs per jail).
     */
    public function getStatistics(Server $server): array
    {
        $banned = $this->getBannedIps($server);
        $stats  = [];
        foreach ($banned as $entry) {
            $jail = $entry['jail'] ?? 'unknown';
            $stats[$jail] = ($stats[$jail] ?? 0) + 1;
        }
        return $stats;
    }

    /**
     * Get fail2ban logs.
     */
    public function getLogs(Server $server, int $lines = 100): array
    {
        try {
            $result = $this->daemon->send('site.deploy-script', [
                'username' => 'root',
                'script'   => "tail -{$lines} /var/log/fail2ban.log 2>/dev/null || echo 'Log not found'",
            ]);
            $output = $result['output'] ?? '';
            return array_filter(explode("\n", trim($output)));
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Restart fail2ban service.
     */
    public function restartService(Server $server): bool
    {
        try {
            $result = $this->daemon->send('restart', ['service' => 'fail2ban']);
            return $result['success'] ?? false;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Parse fail2ban-list output.
     * Handles both:
     *   - sqlite: "ip jail\n..." format
     *   - fail2ban-client: "[{'sshd': ['ip1', 'ip2']}, ...]" Python-dict format
     */
    private function parseFailbanOutput(string $output): array
    {
        $output = trim($output);
        if (empty($output)) {
            return [];
        }

        $ips = [];

        // Python dict format: [{'jail': ['ip1', 'ip2']}, ...]
        if (str_starts_with($output, '[')) {
            // Extract all jail:'ip' pairs
            preg_match_all("/'([^']+)':\s*\[([^\]]*)\]/", $output, $jailMatches, PREG_SET_ORDER);
            foreach ($jailMatches as $match) {
                $jail     = $match[1];
                $ipString = $match[2];
                preg_match_all("/'([^']+)'/", $ipString, $ipMatches);
                foreach ($ipMatches[1] as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        $ips[] = ['ip' => $ip, 'jail' => $jail];
                    }
                }
            }
            return $ips;
        }

        // SQLite format: "ip jail" per line
        foreach (array_filter(explode("\n", $output)) as $line) {
            $parts = preg_split('/[\s|]+/', trim($line), 2);
            if (! empty($parts[0]) && filter_var($parts[0], FILTER_VALIDATE_IP)) {
                $ips[] = ['ip' => $parts[0], 'jail' => $parts[1] ?? 'unknown'];
            }
        }

        return $ips;
    }
}
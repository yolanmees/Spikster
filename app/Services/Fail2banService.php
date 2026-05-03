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
     * Get all available Fail2ban jails with full stats.
     */
    public function getJails(Server $server): array
    {
        try {
            $result = $this->daemon->send('site.deploy-script', [
                'username' => 'root',
                'script'   => 'fail2ban-client status 2>/dev/null',
            ]);
            $output = $result['output'] ?? '';
            preg_match('/Jail list:\s*(.+)/i', $output, $m);
            $jailNames = array_map('trim', explode(',', $m[1] ?? 'sshd'));

            $jails = [];
            foreach ($jailNames as $name) {
                if (empty($name)) continue;
                $jails[] = $this->getJailStatus($server, $name);
            }
            return $jails ?: [['name' => 'sshd', 'current_banned' => 0, 'total_banned' => 0, 'current_failed' => 0, 'total_failed' => 0]];
        } catch (\Throwable $e) {
            Log::error('Fail2ban getJails error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get jail status with full stats via fail2ban-client.
     */
    public function getJailStatus(Server $server, string $jail): array
    {
        try {
            $result = $this->daemon->send('site.deploy-script', [
                'username' => 'root',
                'script'   => "fail2ban-client status {$jail} 2>/dev/null",
            ]);
            $output = $result['output'] ?? '';

            preg_match('/Currently failed:\s*(\d+)/i', $output, $cf);
            preg_match('/Total failed:\s*(\d+)/i',     $output, $tf);
            preg_match('/Currently banned:\s*(\d+)/i', $output, $cb);
            preg_match('/Total banned:\s*(\d+)/i',     $output, $tb);

            return [
                'name'           => $jail,
                'current_failed' => (int)($cf[1] ?? 0),
                'total_failed'   => (int)($tf[1] ?? 0),
                'current_banned' => (int)($cb[1] ?? 0),
                'total_banned'   => (int)($tb[1] ?? 0),
            ];
        } catch (\Throwable) {
            return ['name' => $jail, 'current_banned' => 0, 'total_banned' => 0, 'current_failed' => 0, 'total_failed' => 0];
        }
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
            fn($b) => ($b[0] ?? '') === $ip
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
     * Get aggregate statistics for the stat cards.
     */
    public function getStatistics(Server $server): array
    {
        try {
            $jails       = $this->getJails($server);
            $totalBanned = 0;
            $totalBans   = 0;
            foreach ($jails as $jail) {
                $totalBanned += $jail['current_banned'] ?? 0;
                $totalBans   += $jail['total_banned']   ?? 0;
            }
            return [
                'total_jails'      => count($jails),
                'jails_active'     => count($jails),
                'total_banned_ips' => $totalBanned,
                'total_bans'       => $totalBans,
            ];
        } catch (\Throwable) {
            return ['total_jails' => 0, 'jails_active' => 0, 'total_banned_ips' => 0, 'total_bans' => 0];
        }
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
            preg_match_all("/'([^']+)':\s*\[([^\]]*)\]/", $output, $jailMatches, PREG_SET_ORDER);
            foreach ($jailMatches as $match) {
                $jail     = $match[1];
                $ipString = $match[2];
                preg_match_all("/'([^']+)'/", $ipString, $ipMatches);
                foreach ($ipMatches[1] as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        $ips[] = [$ip, $jail, null]; // [ip, jail, timestamp]
                    }
                }
            }
            return $ips;
        }

        // SQLite format: "ip jail" per line
        foreach (array_filter(explode("\n", $output)) as $line) {
            $parts = preg_split('/[\s|]+/', trim($line), 2);
            if (! empty($parts[0]) && filter_var($parts[0], FILTER_VALIDATE_IP)) {
                $ips[] = [$parts[0], $parts[1] ?? 'unknown', null];
            }
        }

        return $ips;
    }
}
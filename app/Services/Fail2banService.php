<?php

namespace App\Services;

use App\Models\Server;
use phpseclib3\Net\SSH2;

/**
 * Fail2ban Service
 *
 * Handles all Fail2ban-related operations with servers.
 */
class Fail2banService
{
    protected SSHService $sshService;

    public function __construct(SSHService $sshService)
    {
        $this->sshService = $sshService;
    }

    /**
     * Get all banned IPs from the Fail2ban database.
     */
    public function getBannedIps(Server $server): array
    {
        $command = "sqlite3 /var/lib/fail2ban/fail2ban.sqlite3 'select ip,jail,timeofban from bips ORDER BY timeofban DESC'";
        $output = $this->sshService->executeCommand($server, $command);

        $bannedIps = [];
        $lines = explode("\n", trim($output));

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            $parts = explode('|', $line);
            if (count($parts) >= 2) {
                $bannedIps[] = [
                    'ip' => $parts[0] ?? '',
                    'jail' => $parts[1] ?? '',
                    'banned_at' => isset($parts[2]) ? date('Y-m-d H:i:s', $parts[2]) : null,
                    'timestamp' => $parts[2] ?? null,
                ];
            }
        }

        return $bannedIps;
    }

    /**
     * Get all available Fail2ban jails with statistics.
     */
    public function getJails(Server $server): array
    {
        $command = "fail2ban-client status";
        $output = $this->sshService->executeCommand($server, $command);

        // Parse jail list
        $jails = [];
        if (preg_match('/Jail list:\s+(.+)/', $output, $matches)) {
            $jailNames = array_map('trim', explode(',', $matches[1]));

            foreach ($jailNames as $jailName) {
                if (empty($jailName)) {
                    continue;
                }

                $jailStats = $this->getJailStatus($server, $jailName);
                $jails[] = array_merge(['name' => $jailName], $jailStats);
            }
        }

        return $jails;
    }

    /**
     * Get status information for a specific jail.
     */
    public function getJailStatus(Server $server, string $jail): array
    {
        $command = "fail2ban-client status {$jail}";
        $output = $this->sshService->executeCommand($server, $command);

        $stats = [
            'total_banned' => 0,
            'current_banned' => 0,
            'total_failed' => 0,
            'current_failed' => 0,
            'banned_ips' => [],
        ];

        // Parse output
        if (preg_match('/Total banned:\s+(\d+)/', $output, $matches)) {
            $stats['total_banned'] = (int) $matches[1];
        }

        if (preg_match('/Currently banned:\s+(\d+)/', $output, $matches)) {
            $stats['current_banned'] = (int) $matches[1];
        }

        if (preg_match('/Total failed:\s+(\d+)/', $output, $matches)) {
            $stats['total_failed'] = (int) $matches[1];
        }

        if (preg_match('/Currently failed:\s+(\d+)/', $output, $matches)) {
            $stats['current_failed'] = (int) $matches[1];
        }

        if (preg_match('/Banned IP list:\s+(.+)/', $output, $matches)) {
            $ips = array_filter(array_map('trim', explode(' ', $matches[1])));
            $stats['banned_ips'] = $ips;
        }

        return $stats;
    }

    /**
     * Ban an IP address in a specific jail.
     */
    public function banIp(Server $server, string $ip, string $jail = 'manual'): bool
    {
        // Validate IP address
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception("Invalid IP address: {$ip}");
        }

        // Check if jail exists, if not use sshd as default
        $jails = $this->getJails($server);
        $jailNames = array_column($jails, 'name');
        
        if (!in_array($jail, $jailNames)) {
            $jail = 'sshd'; // Default to sshd jail
        }

        $command = "fail2ban-client set {$jail} banip {$ip}";
        $output = $this->sshService->executeCommand($server, $command);

        // Log the action
        if (class_exists(\App\Services\AuditService::class)) {
            \App\Services\AuditService::log(
                action: 'fail2ban_ban_ip',
                description: "Banned IP {$ip} in jail {$jail}",
                server_id: $server->server_id
            );
        }

        return str_contains($output, '1') || str_contains(strtolower($output), 'success');
    }

    /**
     * Unban an IP address from a specific jail.
     */
    public function unbanIp(Server $server, string $ip, string $jail = null): bool
    {
        // Validate IP address
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception("Invalid IP address: {$ip}");
        }

        if ($jail) {
            // Unban from specific jail
            $command = "fail2ban-client set {$jail} unbanip {$ip}";
        } else {
            // Unban from all jails
            $jails = $this->getJails($server);
            foreach ($jails as $jailData) {
                $jailName = $jailData['name'];
                $command = "fail2ban-client set {$jailName} unbanip {$ip}";
                $this->sshService->executeCommand($server, $command);
            }

            // Also remove from database
            $command = "sqlite3 /var/lib/fail2ban/fail2ban.sqlite3 \"DELETE FROM bips WHERE ip='{$ip}'\"";
        }

        $output = $this->sshService->executeCommand($server, $command);

        // Log the action
        if (class_exists(\App\Services\AuditService::class)) {
            \App\Services\AuditService::log(
                action: 'fail2ban_unban_ip',
                description: "Unbanned IP {$ip}" . ($jail ? " from jail {$jail}" : " from all jails"),
                server_id: $server->server_id
            );
        }

        return true;
    }

    /**
     * Check if an IP address is currently banned.
     */
    public function isIpBanned(Server $server, string $ip): array
    {
        $bannedIps = $this->getBannedIps($server);
        $banned = [];

        foreach ($bannedIps as $bannedIp) {
            if ($bannedIp['ip'] === $ip) {
                $banned[] = $bannedIp;
            }
        }

        return $banned;
    }

    /**
     * Get Fail2ban service status.
     */
    public function getServiceStatus(Server $server): array
    {
        $isRunning = $this->sshService->isServiceRunning($server, 'fail2ban');
        
        $status = [
            'running' => $isRunning,
            'version' => '',
        ];

        if ($isRunning) {
            $versionOutput = $this->sshService->executeCommand($server, 'fail2ban-client version');
            $status['version'] = trim($versionOutput);
        }

        return $status;
    }

    /**
     * Restart Fail2ban service.
     */
    public function restartService(Server $server): bool
    {
        return $this->sshService->restartService($server, 'fail2ban');
    }

    /**
     * Get Fail2ban log entries.
     */
    public function getLogs(Server $server, int $lines = 100): array
    {
        $command = "tail -n {$lines} /var/log/fail2ban.log";
        $output = $this->sshService->executeCommand($server, $command);

        $logs = [];
        $logLines = explode("\n", trim($output));

        foreach ($logLines as $line) {
            if (empty($line)) {
                continue;
            }

            // Parse log line (format: YYYY-MM-DD HH:MM:SS,mmm fail2ban.actions [PID]: LEVEL Message)
            if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}),\d+ (.+?) \[(\d+)\]: (\w+)\s+(.+)$/', $line, $matches)) {
                $logs[] = [
                    'timestamp' => $matches[1],
                    'component' => $matches[2],
                    'pid' => $matches[3],
                    'level' => $matches[4],
                    'message' => $matches[5],
                    'raw' => $line,
                ];
            } else {
                // If parsing fails, include raw line
                $logs[] = [
                    'timestamp' => '',
                    'component' => '',
                    'pid' => '',
                    'level' => '',
                    'message' => $line,
                    'raw' => $line,
                ];
            }
        }

        return $logs;
    }

    /**
     * Get ban statistics.
     */
    public function getStatistics(Server $server): array
    {
        $bannedIps = $this->getBannedIps($server);
        $jails = $this->getJails($server);

        $stats = [
            'total_banned_ips' => count($bannedIps),
            'total_jails' => count($jails),
            'jails_active' => 0,
            'total_bans' => 0,
            'bans_by_jail' => [],
        ];

        foreach ($jails as $jail) {
            if ($jail['current_banned'] > 0) {
                $stats['jails_active']++;
            }
            $stats['total_bans'] += $jail['total_banned'];
            $stats['bans_by_jail'][$jail['name']] = $jail['current_banned'];
        }

        return $stats;
    }

    /**
     * Whitelist an IP address (add to ignoreip).
     */
    public function whitelistIp(Server $server, string $ip): bool
    {
        // Validate IP address
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception("Invalid IP address: {$ip}");
        }

        // First unban the IP if it's currently banned
        $this->unbanIp($server, $ip);

        // Add to ignoreip in jail.local
        $command = "grep -q 'ignoreip.*{$ip}' /etc/fail2ban/jail.local || sed -i '/^ignoreip/s/$/ {$ip}/' /etc/fail2ban/jail.local";
        $this->sshService->executeCommand($server, $command);

        // Reload Fail2ban
        $this->sshService->executeCommand($server, 'fail2ban-client reload');

        // Log the action
        if (class_exists(\App\Services\AuditService::class)) {
            \App\Services\AuditService::log(
                action: 'fail2ban_whitelist_ip',
                description: "Whitelisted IP {$ip}",
                server_id: $server->server_id
            );
        }

        return true;
    }

    /**
     * Get whitelisted IPs.
     */
    public function getWhitelistedIps(Server $server): array
    {
        $command = "grep '^ignoreip' /etc/fail2ban/jail.local | head -1";
        $output = $this->sshService->executeCommand($server, $command);

        $whitelisted = [];
        if (preg_match('/ignoreip\s*=\s*(.+)/', $output, $matches)) {
            $ips = array_filter(array_map('trim', explode(' ', $matches[1])));
            $whitelisted = array_values($ips);
        }

        return $whitelisted;
    }
}

<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fail2ban Service
 *
 * Handles all Fail2ban-related operations with servers.
 * Uses HTTP API for remote servers or direct commands for local panel server.
 */
class Fail2banService
{
    /**
     * Get all banned IPs from the Fail2ban database.
     */
    public function getBannedIps(Server $server): array
    {
        try {
            $response = Http::timeout(10)->get("http://{$server->ip}/spikster-api/fail2ban.php", [
                'action' => 'get-banned-ips',
                'server_id' => $server->server_id,
            ]);

            if (! $response->successful()) {
                Log::error('Failed to get banned IPs', ['server' => $server->id, 'status' => $response->status()]);

                return [];
            }

            $data = $response->json();

            return $data['ips'] ?? [];
        } catch (\Exception $e) {
            Log::error('Fail2ban getBannedIps error: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get all available Fail2ban jails with statistics.
     */
    public function getJails(Server $server): array
    {
        try {
            $response = Http::timeout(10)->get("http://{$server->ip}/spikster-api/fail2ban.php", [
                'action' => 'get-jails',
                'server_id' => $server->server_id,
            ]);

            if (! $response->successful()) {
                Log::error('Failed to get jails', ['server' => $server->id, 'status' => $response->status()]);

                return [];
            }

            $data = $response->json();

            return $data['jails'] ?? [];
        } catch (\Exception $e) {
            Log::error('Fail2ban getJails error: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get status information for a specific jail.
     */
    public function getJailStatus(Server $server, string $jail): array
    {
        try {
            $response = Http::timeout(10)->get("http://{$server->ip}/spikster-api/fail2ban.php", [
                'action' => 'get-jail-status',
                'server_id' => $server->server_id,
                'jail' => $jail,
            ]);

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();

            return $data['status'] ?? [];
        } catch (\Exception $e) {
            Log::error('Fail2ban getJailStatus error: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Ban an IP address in a specific jail.
     */
    public function banIp(Server $server, string $ip, string $jail = 'sshd'): bool
    {
        // Validate IP address
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception("Invalid IP address: {$ip}");
        }

        try {
            $response = Http::timeout(10)->post("http://{$server->ip}/spikster-api/fail2ban.php", [
                'action' => 'ban-ip',
                'server_id' => $server->server_id,
                'ip' => $ip,
                'jail' => $jail,
            ]);

            if (! $response->successful()) {
                throw new \Exception('HTTP request failed with status: '.$response->status());
            }

            $data = $response->json();

            // Log the action
            if (class_exists(AuditService::class)) {
                AuditService::log(
                    eventType: 'fail2ban_ban_ip',
                    description: "Banned IP {$ip} in jail {$jail}",
                    severity: 'warning'
                );
            }

            return $data['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('Fail2ban banIp error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Unban an IP address from a specific jail.
     */
    public function unbanIp(Server $server, string $ip, ?string $jail = null): bool
    {
        // Validate IP address
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception("Invalid IP address: {$ip}");
        }

        try {
            $response = Http::timeout(10)->post("http://{$server->ip}/spikster-api/fail2ban.php", [
                'action' => 'unban-ip',
                'server_id' => $server->server_id,
                'ip' => $ip,
                'jail' => $jail,
            ]);

            if (! $response->successful()) {
                throw new \Exception('HTTP request failed');
            }

            $data = $response->json();

            // Log the action
            if (class_exists(AuditService::class)) {
                AuditService::log(
                    eventType: 'fail2ban_unban_ip',
                    description: "Unbanned IP {$ip}".($jail ? " from jail {$jail}" : ' from all jails'),
                    severity: 'info'
                );
            }

            return $data['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('Fail2ban unbanIp error: '.$e->getMessage());
            throw $e;
        }
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
        try {
            $response = Http::timeout(10)->get("http://{$server->ip}/spikster-api/fail2ban.php", [
                'action' => 'get-service-status',
                'server_id' => $server->server_id,
            ]);

            if (! $response->successful()) {
                return ['running' => false, 'version' => ''];
            }

            $data = $response->json();

            return $data['status'] ?? ['running' => false, 'version' => ''];
        } catch (\Exception $e) {
            return ['running' => false, 'version' => ''];
        }
    }

    /**
     * Restart Fail2ban service.
     */
    public function restartService(Server $server): bool
    {
        try {
            $response = Http::timeout(10)->post("http://{$server->ip}/spikster-api/fail2ban.php", [
                'action' => 'restart-service',
                'server_id' => $server->server_id,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Fail2ban restartService error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get Fail2ban log entries.
     */
    public function getLogs(Server $server, int $lines = 100): array
    {
        try {
            $response = Http::timeout(10)->get("http://{$server->ip}/spikster-api/fail2ban.php", [
                'action' => 'get-logs',
                'server_id' => $server->server_id,
                'lines' => $lines,
            ]);

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();

            return $data['logs'] ?? [];
        } catch (\Exception $e) {
            Log::error('Fail2ban getLogs error: '.$e->getMessage());

            return [];
        }
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
            if (($jail['current_banned'] ?? 0) > 0) {
                $stats['jails_active']++;
            }
            $stats['total_bans'] += $jail['total_banned'] ?? 0;
            $stats['bans_by_jail'][$jail['name']] = $jail['current_banned'] ?? 0;
        }

        return $stats;
    }

    /**
     * Whitelist an IP address (add to ignoreip).
     */
    public function whitelistIp(Server $server, string $ip): bool
    {
        // Validate IP address
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception("Invalid IP address: {$ip}");
        }

        try {
            $response = Http::timeout(10)->post("http://{$server->ip}/spikster-api/fail2ban.php", [
                'action' => 'whitelist-ip',
                'server_id' => $server->server_id,
                'ip' => $ip,
            ]);

            if (! $response->successful()) {
                throw new \Exception('HTTP request failed');
            }

            $data = $response->json();

            // Log the action
            if (class_exists(AuditService::class)) {
                AuditService::log(
                    eventType: 'fail2ban_whitelist_ip',
                    description: "Whitelisted IP {$ip}",
                    severity: 'info'
                );
            }

            return $data['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('Fail2ban whitelistIp error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Get whitelisted IPs.
     */
    public function getWhitelistedIps(Server $server): array
    {
        try {
            $response = Http::timeout(10)->get("http://{$server->ip}/spikster-api/fail2ban.php", [
                'action' => 'get-whitelist',
                'server_id' => $server->server_id,
            ]);

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();

            return $data['whitelist'] ?? [];
        } catch (\Exception $e) {
            Log::error('Fail2ban getWhitelistedIps error: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Add a new jail.
     */
    public function addJail(Server $server, array $jailConfig): bool
    {
        try {
            $response = Http::timeout(10)->post("http://{$server->ip}/spikster-api/fail2ban.php", [
                'action' => 'add-jail',
                'server_id' => $server->server_id,
                'jail_config' => $jailConfig,
            ]);

            if (! $response->successful()) {
                throw new \Exception('HTTP request failed');
            }

            $data = $response->json();

            // Log the action
            if (class_exists(AuditService::class)) {
                AuditService::log(
                    eventType: 'fail2ban_add_jail',
                    description: "Added jail: {$jailConfig['name']}",
                    severity: 'info'
                );
            }

            return $data['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('Fail2ban addJail error: '.$e->getMessage());
            throw $e;
        }
    }
}

<?php

namespace App\Services;

use App\Models\Server;
use phpseclib3\Net\SSH2;
use phpseclib3\Net\SFTP;

/**
 * SSH Service
 *
 * Handles all SSH-related operations with servers.
 */
class SSHService
{
    /**
     * Create SSH connection to a server.
     */
    public function connect(Server $server): SSH2
    {
        $ssh = new SSH2($server->ip, 22);

        if (! $ssh->login('spikster', $server->password)) {
            throw new \Exception('SSH authentication failed');
        }

        return $ssh;
    }

    /**
     * Execute a command on a server via SSH.
     */
    public function executeCommand(Server $server, string $command, bool $sanitize = false): string
    {
        if ($sanitize) {
            $command = $this->sanitizeCommand($command);
        }

        $ssh = $this->connect($server);
        $output = $ssh->exec($command);

        // Log the command execution
        if (class_exists(\App\Services\AuditService::class)) {
            \App\Services\AuditService::logSshCommand(
                command: $command,
                server: $server,
                result: substr($output, 0, 500) // Limit result length
            );
        }

        return $output;
    }

    /**
     * Test SSH connectivity to a server.
     */
    public function testConnection(string $ip, string $password): bool
    {
        try {
            $ssh = new SSH2($ip, 22);

            return $ssh->login('spikster', $password);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get server load average.
     */
    public function getLoadAverage(Server $server): array
    {
        $output = $this->executeCommand($server, 'cat /proc/loadavg');
        $parts = explode(' ', trim($output));

        return [
            '1min' => $parts[0] ?? 0,
            '5min' => $parts[1] ?? 0,
            '15min' => $parts[2] ?? 0,
        ];
    }

    /**
     * Get server CPU usage.
     */
    public function getCPUUsage(Server $server): float
    {
        $output = $this->executeCommand($server, "top -bn1 | grep 'Cpu(s)' | sed 's/.*, *\\([0-9.]*\\)%* id.*/\\1/' | awk '{print 100 - $1}'");

        return (float) trim($output);
    }

    /**
     * Get server memory usage.
     */
    public function getMemoryUsage(Server $server): array
    {
        $output = $this->executeCommand($server, 'free -m | grep Mem');
        $parts = preg_split('/\s+/', trim($output));

        $total = (int) ($parts[1] ?? 0);
        $used = (int) ($parts[2] ?? 0);
        $free = (int) ($parts[3] ?? 0);

        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'percentage' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get server disk usage.
     */
    public function getDiskUsage(Server $server): array
    {
        $output = $this->executeCommand($server, "df -h / | tail -1 | awk '{print $2,$3,$4,$5}'");
        $parts = explode(' ', trim($output));

        return [
            'total' => $parts[0] ?? '0G',
            'used' => $parts[1] ?? '0G',
            'available' => $parts[2] ?? '0G',
            'percentage' => rtrim($parts[3] ?? '0%', '%'),
        ];
    }

    /**
     * Check if a service is running.
     */
    public function isServiceRunning(Server $server, string $service): bool
    {
        $output = $this->executeCommand($server, "systemctl is-active {$service}");

        return trim($output) === 'active';
    }

    /**
     * Restart a service.
     */
    public function restartService(Server $server, string $service): bool
    {
        $this->executeCommand($server, "systemctl restart {$service}");

        return $this->isServiceRunning($server, $service);
    }

    /**
     * Get list of running processes.
     */
    public function getProcesses(Server $server, ?string $filter = null): array
    {
        $command = 'ps aux';
        if ($filter) {
            // Escape single quotes to prevent SSH command injection
            $safeFilter = str_replace("'", "'\\'''", $filter);
            $command .= " | grep -F '{$safeFilter}'";
        }

        $output = $this->executeCommand($server, $command);
        $lines = explode("\n", trim($output));

        $processes = [];
        foreach ($lines as $line) {
            if (empty(trim($line)) || str_contains($line, 'grep')) {
                continue;
            }

            $processes[] = $line;
        }

        return $processes;
    }

    /**
     * Install a package via apt.
     */
    public function installPackage(Server $server, string $package): bool
    {
        // Only allow safe package names (alphanumeric, dash, dot, plus)
        if (! preg_match('/^[a-zA-Z0-9\-\.\+]+$/', $package)) {
            throw new \InvalidArgumentException("Invalid package name: {$package}");
        }
        $this->executeCommand($server, "DEBIAN_FRONTEND=noninteractive apt-get install -y {$package}");

        return true;
    }

    /**
     * Uninstall a package via apt.
     */
    public function uninstallPackage(Server $server, string $package): bool
    {
        // Only allow safe package names (alphanumeric, dash, dot, plus)
        if (! preg_match('/^[a-zA-Z0-9\-\.\+]+$/', $package)) {
            throw new \InvalidArgumentException("Invalid package name: {$package}");
        }
        $this->executeCommand($server, "DEBIAN_FRONTEND=noninteractive apt-get remove -y {$package}");

        return true;
    }

    /**
     * Get installed packages.
     */
    public function getInstalledPackages(Server $server): array
    {
        $output = $this->executeCommand($server, 'dpkg -l | grep ^ii');
        $lines = explode("\n", trim($output));

        $packages = [];
        foreach ($lines as $line) {
            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 3) {
                $packages[] = [
                    'name' => $parts[1],
                    'version' => $parts[2],
                ];
            }
        }

        return $packages;
    }

    /**
     * Create a directory on the server.
     */
    public function createDirectory(Server $server, string $path, int $permissions = 0755): bool
    {
        $this->executeCommand($server, "mkdir -p {$path} && chmod {$permissions} {$path}");

        return true;
    }

    /**
     * Delete a directory on the server.
     */
    public function deleteDirectory(Server $server, string $path): bool
    {
        // Normalize path to prevent traversal attacks before checking
        $normalizedPath = '/' . trim(str_replace(['..', '//'], ['', '/'], $path), '/');

        // Safety check - prevent deleting critical directories or anything beneath them
        $dangerousPrefixes = ['/', '/bin', '/boot', '/dev', '/etc', '/lib', '/proc', '/root', '/sbin', '/sys', '/usr', '/var'];

        foreach ($dangerousPrefixes as $prefix) {
            if ($normalizedPath === $prefix || str_starts_with($normalizedPath . '/', $prefix . '/')) {
                throw new \Exception('Cannot delete critical system directory');
            }
        }

        $this->executeCommand($server, "rm -rf {$normalizedPath}");

        return true;
    }

    /**
     * Change file/directory ownership.
     */
    public function changeOwnership(Server $server, string $path, string $owner, ?string $group = null): bool
    {
        $ownerGroup = $group ? "{$owner}:{$group}" : $owner;
        $this->executeCommand($server, "chown -R {$ownerGroup} {$path}");

        return true;
    }

    /**
     * Sanitize command to prevent injection.
     */
    protected function sanitizeCommand(string $command): string
    {
        if (class_exists(\App\Helpers\SecurityHelper::class)) {
            return \App\Helpers\SecurityHelper::sanitizeSshCommand($command);
        }

        return $command;
    }

    /**
     * Upload a file to the server via SFTP.
     */
    public function uploadFile(Server $server, string $localPath, string $remotePath): bool
    {
        $sftp = new SFTP($server->ip, 22);

        if (! $sftp->login('spikster', $server->password)) {
            throw new \Exception('SFTP authentication failed');
        }

        return (bool) $sftp->put($remotePath, $localPath, SFTP::SOURCE_LOCAL_FILE);
    }

    /**
     * Download a file from the server via SFTP.
     */
    public function downloadFile(Server $server, string $remotePath, string $localPath): bool
    {
        $sftp = new SFTP($server->ip, 22);

        if (! $sftp->login('spikster', $server->password)) {
            throw new \Exception('SFTP authentication failed');
        }

        return (bool) $sftp->get($remotePath, $localPath);
    }
}

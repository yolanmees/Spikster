<?php

namespace App\Services;

use App\Models\Server;

/**
 * SSHService stub — kept for backwards compatibility.
 *
 * @deprecated All SSH operations have been replaced by DaemonService.
 *             This class exists only so legacy code that still type-hints SSHService
 *             can be resolved by the container without crashing.
 */
class SSHService
{
    public function connect(Server $server): static { return $this; }

    public function executeCommand(Server $server, string $command): string
    {
        throw new \RuntimeException('SSHService is deprecated. Use DaemonService instead.');
    }

    public function exec(string $command): string
    {
        throw new \RuntimeException('SSHService is deprecated. Use DaemonService instead.');
    }

    public function restartService(Server $server, string $service): void
    {
        throw new \RuntimeException('SSHService is deprecated. Use DaemonService instead.');
    }

    public function changeOwnership(Server $server, string $path, string $user, string $group): void
    {
        throw new \RuntimeException('SSHService is deprecated. Use DaemonService instead.');
    }

    public function deleteDirectory(Server $server, string $path): void
    {
        throw new \RuntimeException('SSHService is deprecated. Use DaemonService instead.');
    }

    public function uploadFile(Server $server, string $localPath, string $remotePath): void
    {
        throw new \RuntimeException('SSHService is deprecated. Use DaemonService instead.');
    }
}

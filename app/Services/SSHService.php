<?php

namespace App\Services;

use App\Models\Server;

/**
 * SSHService stub — kept for backwards compatibility.
 *
 * @deprecated All SSH operations have been replaced by the Go daemon.
 *             This class exists only so legacy code that still type-hints SSHService
 *             can be resolved by the container without crashing.
 *
 *             TODO: Refactor DeploymentService and any other consumers to use
 *             RemoteDaemonService instead of SSHService for remote server operations.
 */
class SSHService
{
    public function connect(Server $server): static
    {
        return $this;
    }

    public function executeCommand(Server $server, string $command): string
    {
        throw new \RuntimeException('SSHService is deprecated. Use DaemonService via go.sh for remote commands.');
    }

    public function exec(string $command): string
    {
        throw new \RuntimeException('SSHService is deprecated. Use DaemonService via go.sh for remote commands.');
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
        throw new \RuntimeException('SSHService is deprecated. Use RemoteDaemonService instead.');
    }

    public function uploadFile(Server $server, string $localPath, string $remotePath): void
    {
        throw new \RuntimeException('SSHService is deprecated. Use RemoteDaemonService instead.');
    }
}

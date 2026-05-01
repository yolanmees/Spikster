<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\SSHService;
use Illuminate\Console\Command;

class LogRotate extends Command
{
    protected $signature = 'spikster:logrotate';

    protected $description = 'Rotate site access/error logs on all servers';

    public function handle(SSHService $sshService): int
    {
        $day = date('N');

        foreach (Server::all() as $server) {
            foreach ($server->sites as $site) {
                try {
                    $u = $site->username;
                    $ssh = $sshService->connect($server);
                    // passwordless sudo — spikster has NOPASSWD for log ops via go.sh
                    $ssh->exec("sudo unlink /home/{$u}/log/access_bk_{$day}.log 2>/dev/null; true");
                    $ssh->exec("sudo mv /home/{$u}/log/access.log /home/{$u}/log/access_bk_{$day}.log 2>/dev/null; true");
                    $ssh->exec("sudo unlink /home/{$u}/log/error_bk_{$day}.log 2>/dev/null; true");
                    $ssh->exec("sudo mv /home/{$u}/log/error.log /home/{$u}/log/error_bk_{$day}.log 2>/dev/null; true");
                } catch (\Exception $e) {
                    $this->warn("Log rotate failed for {$site->domain}: ".$e->getMessage());
                }
            }
        }

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\RemoteDaemonService;
use Illuminate\Console\Command;

class LogRotate extends Command
{
    protected $signature = 'spikster:logrotate';

    protected $description = 'Rotate site access/error logs on all servers';

    public function handle(RemoteDaemonService $daemon): int
    {
        $day = date('N');

        foreach (Server::all() as $server) {
            try {
                $result = $daemon->rotateLogs($server, $day);

                if ($result['success'] ?? false) {
                    $this->info("{$server->name}: {$result['output']}");
                } else {
                    $this->warn("{$server->name}: {$result['error']}");
                }
            } catch (\Exception $e) {
                $this->warn("{$server->name}: log rotate failed: ".$e->getMessage());
            }
        }

        return 0;
    }
}

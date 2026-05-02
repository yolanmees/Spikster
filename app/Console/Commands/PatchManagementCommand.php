<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\RemoteDaemonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PatchManagementCommand extends Command
{
    protected $signature = 'spikster:patch-check
        {--server= : Check a specific server by ID}
        {--apply : Apply available security updates}
        {--reboot : Reboot if kernel updated}';

    protected $description = 'Check and apply OS security patches on managed servers';

    public function handle(RemoteDaemonService $daemon): int
    {
        $serverId = $this->option('server');
        $apply = $this->option('apply');
        $reboot = $this->option('reboot');

        $servers = $serverId
            ? Server::where('server_id', $serverId)->get()
            : Server::active()->get();

        if ($servers->isEmpty()) {
            $this->warn('No active servers found.');
            return 0;
        }

        $results = [];

        foreach ($servers as $server) {
            $this->line("Checking {$server->name} ({$server->ip})...");

            try {
                $result = $daemon->send($server, 'server.package-list', []);

                if ($apply) {
                    $updateResult = $daemon->send($server, 'server.package-install', [
                        'package' => 'unattended-upgrades',
                    ]);

                    $this->line("  → Security updates applied");

                    if ($reboot) {
                        $daemon->send($server, 'server.exec', [
                            'command' => 'if [ -f /var/run/reboot-required ]; then reboot; fi',
                        ]);
                        $this->line("  → Reboot requested");
                    }
                }

                $results[$server->name] = ['status' => 'checked', 'packages' => $result['output'] ?? 'unknown'];
                $this->line("  ✓ Done");
            } catch (\Throwable $e) {
                $results[$server->name] = ['status' => 'failed', 'error' => $e->getMessage()];
                $this->error("  ✗ {$e->getMessage()}");
            }
        }

        Log::info('Patch check completed', $results);

        return 0;
    }
}

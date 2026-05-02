<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\RemoteDaemonService;
use Illuminate\Console\Command;

class ReinstallServerCommand extends Command
{
    protected $signature = 'spikster:server-reinstall
        {server_id : Server UUID to reinstall}
        {--force : Skip confirmation}';

    protected $description = 'Reinstall Spikster daemon on a server without affecting the control plane';

    public function handle(RemoteDaemonService $daemon): int
    {
        $serverId = $this->argument('server_id');
        $server = Server::where('server_id', $serverId)->firstOrFail();

        if (! $this->option('force')) {
            $this->warn("This will reinstall the Spikster daemon on {$server->name} ({$server->ip})");
            $this->warn('Site data, databases, and email accounts will NOT be affected.');
            if (! $this->confirm('Continue?')) {
                return 0;
            }
        }

        $this->info("Reinstalling daemon on {$server->name}...");

        try {
            $daemon->bootstrapServer($server);
            $server->update(['status' => 1, 'build' => time()]);
            $this->info('Reinstallation completed successfully.');
        } catch (\Throwable $e) {
            $this->error("Reinstallation failed: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}

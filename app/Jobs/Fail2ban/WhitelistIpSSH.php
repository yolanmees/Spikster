<?php

namespace App\Jobs\Fail2ban;

use App\Models\Server;
use App\Services\RemoteDaemonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WhitelistIpSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;

    public $tries = 3;

    public function __construct(
        protected Server $server,
        protected string $ip
    ) {}

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            $success = $daemon->fail2banWhitelist($this->server, $this->ip);

            if (! $success) {
                throw new \RuntimeException("Daemon returned failure for fail2ban.whitelist: {$this->ip}");
            }

            Log::info("Fail2ban: Whitelisted IP {$this->ip} on server {$this->server->name}");
        } catch (\Throwable $e) {
            Log::error("Fail2ban: Failed to whitelist IP {$this->ip}: ".$e->getMessage());
            throw $e;
        }
    }
}

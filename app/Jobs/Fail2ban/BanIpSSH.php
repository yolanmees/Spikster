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

class BanIpSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    public function __construct(
        protected Server $server,
        protected string $ip,
        protected string $jail = 'sshd'
    ) {}

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            $success = $daemon->fail2banBan($this->server, $this->ip, $this->jail);

            if (! $success) {
                throw new \RuntimeException("Daemon returned failure for fail2ban.ban: {$this->ip}");
            }

            Log::info("Fail2ban: Banned IP {$this->ip} in jail {$this->jail} on server {$this->server->name}");
        } catch (\Throwable $e) {
            Log::error("Fail2ban: Failed to ban IP {$this->ip}: " . $e->getMessage());
            throw $e;
        }
    }
}

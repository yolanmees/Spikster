<?php

namespace App\Jobs\Fail2ban;

use App\Models\Server;
use App\Services\Fail2banService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WhitelistIpSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    protected Server $server;
    protected string $ip;

    /**
     * Create a new job instance.
     */
    public function __construct(Server $server, string $ip)
    {
        $this->server = $server;
        $this->ip = $ip;
    }

    /**
     * Execute the job.
     */
    public function handle(Fail2banService $fail2banService): void
    {
        try {
            $fail2banService->whitelistIp($this->server, $this->ip);
            
            Log::info("Fail2ban: Whitelisted IP {$this->ip} on server {$this->server->name}");
        } catch (\Throwable $e) {
            Log::error("Fail2ban: Failed to whitelist IP {$this->ip}: " . $e->getMessage());
            throw $e;
        }
    }
}

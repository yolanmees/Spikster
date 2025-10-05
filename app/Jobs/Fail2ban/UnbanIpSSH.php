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

class UnbanIpSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    protected Server $server;
    protected string $ip;
    protected ?string $jail;

    /**
     * Create a new job instance.
     */
    public function __construct(Server $server, string $ip, ?string $jail = null)
    {
        $this->server = $server;
        $this->ip = $ip;
        $this->jail = $jail;
    }

    /**
     * Execute the job.
     */
    public function handle(Fail2banService $fail2banService): void
    {
        try {
            $fail2banService->unbanIp($this->server, $this->ip, $this->jail);

            $jailInfo = $this->jail ? "from jail {$this->jail}" : "from all jails";
            Log::info("Fail2ban: Unbanned IP {$this->ip} {$jailInfo} on server {$this->server->name}");
        } catch (\Throwable $e) {
            Log::error("Fail2ban: Failed to unban IP {$this->ip}: " . $e->getMessage());
            throw $e;
        }
    }
}

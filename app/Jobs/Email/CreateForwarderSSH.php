<?php

namespace App\Jobs\Email;

use App\Models\EmailForwarder;
use App\Models\Server;
use App\Services\SSHService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateForwarderSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Server $server,
        public EmailForwarder $forwarder
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SSHService $sshService): void
    {
        try {
            $ssh = $sshService->connect($this->server);

            $source = $this->forwarder->source;
            $destination = $this->forwarder->destination;

            // Add to Postfix virtual alias map
            $ssh->exec("echo '{$source} {$destination}' >> /etc/postfix/virtual");
            $ssh->exec("postmap /etc/postfix/virtual");

            // Reload Postfix
            $ssh->exec("systemctl reload postfix");

            $sshService->disconnect();

            Log::info("Email forwarder created: {$source} → {$destination}");

        } catch (\Exception $e) {
            Log::error("Failed to create email forwarder: {$this->forwarder->source}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

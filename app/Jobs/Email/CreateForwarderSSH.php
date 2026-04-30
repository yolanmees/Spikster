<?php

namespace App\Jobs\Email;

use App\Models\EmailForwarder;
use App\Models\Server;
use App\Services\RemoteDaemonService;
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

    public function __construct(
        public Server $server,
        public EmailForwarder $forwarder
    ) {}

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            $success = $daemon->createEmailForwarder(
                $this->server,
                $this->forwarder->source,
                $this->forwarder->destination
            );

            if (! $success) {
                throw new \RuntimeException("Daemon returned failure for email.forwarder-create: {$this->forwarder->source}");
            }

            Log::info("Email forwarder created via daemon: {$this->forwarder->source} → {$this->forwarder->destination}");
        } catch (\Exception $e) {
            Log::error("Failed to create email forwarder: {$this->forwarder->source}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

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

class DeleteForwarderSSH implements ShouldQueue
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
            $success = $daemon->deleteEmailForwarder(
                $this->server,
                $this->forwarder->source
            );

            if (! $success) {
                throw new \RuntimeException("Daemon returned failure for email.forwarder-delete: {$this->forwarder->source}");
            }

            Log::info("Email forwarder deleted via daemon: {$this->forwarder->source}");
        } catch (\Exception $e) {
            Log::error("Failed to delete email forwarder: {$this->forwarder->source}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

<?php

namespace App\Jobs\Email;

use App\Models\EmailAccount;
use App\Models\Server;
use App\Services\RemoteDaemonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateEmailPasswordSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    public $tries = 3;

    public function __construct(
        public Server $server,
        public EmailAccount $account
    ) {}

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            $success = $daemon->updateEmailPassword(
                $this->server,
                $this->account->email,
                $this->account->getAttributes()['password'] // pre-hashed
            );

            if (! $success) {
                throw new \RuntimeException("Daemon returned failure for email.update-password: {$this->account->email}");
            }

            Log::info("Email password updated via daemon: {$this->account->email}");
        } catch (\Exception $e) {
            Log::error("Failed to update email password: {$this->account->email}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

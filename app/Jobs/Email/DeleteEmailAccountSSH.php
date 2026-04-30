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

class DeleteEmailAccountSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    public function __construct(
        public Server $server,
        public EmailAccount $account
    ) {}

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            $success = $daemon->deleteEmailAccount(
                $this->server,
                $this->account->domain,
                $this->account->username,
                $this->account->email
            );

            if (! $success) {
                throw new \RuntimeException("Daemon returned failure for email.delete: {$this->account->email}");
            }

            Log::info("Email account deleted via daemon: {$this->account->email}");
        } catch (\Exception $e) {
            Log::error("Failed to delete email account: {$this->account->email}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

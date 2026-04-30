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

class CreateEmailAccountSSH implements ShouldQueue
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
            $success = $daemon->createEmailAccount(
                $this->server,
                $this->account->domain,
                $this->account->username,
                $this->account->email,
                $this->account->getAttributes()['password'], // pre-hashed
                (int) ($this->account->quota_mb ?? 0)
            );

            if (! $success) {
                throw new \RuntimeException("Daemon returned failure for email.create: {$this->account->email}");
            }

            Log::info("Email account created via daemon: {$this->account->email}");
        } catch (\Exception $e) {
            Log::error("Failed to create email account: {$this->account->email}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

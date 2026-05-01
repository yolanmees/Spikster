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

class UpdateEmailQuotaSSH implements ShouldQueue
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
            $success = $daemon->updateEmailQuota(
                $this->server,
                $this->account->email,
                (int) ($this->account->quota_mb ?? 0)
            );

            if (! $success) {
                throw new \RuntimeException("Daemon returned failure for email.update-quota: {$this->account->email}");
            }

            Log::info("Email quota updated via daemon: {$this->account->email} = {$this->account->quota_mb}MB");
        } catch (\Exception $e) {
            Log::error("Failed to update email quota: {$this->account->email}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

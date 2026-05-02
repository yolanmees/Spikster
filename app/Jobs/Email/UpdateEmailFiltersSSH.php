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

class UpdateEmailFiltersSSH implements ShouldQueue
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
            $daemon->setEmailFilters(
                $this->server,
                $this->account->email,
                (bool) $this->account->spam_filter,
                (float) ($this->account->spam_score ?? 5.0),
                (bool) $this->account->antivirus,
            );

            Log::info("Email filters updated via daemon: {$this->account->email}", [
                'spam_filter' => $this->account->spam_filter,
                'antivirus' => $this->account->antivirus,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update email filters: {$this->account->email}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

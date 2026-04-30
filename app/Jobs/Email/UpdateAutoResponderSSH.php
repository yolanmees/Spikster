<?php

namespace App\Jobs\Email;

use App\Models\EmailAutoresponder;
use App\Models\Server;
use App\Services\RemoteDaemonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateAutoResponderSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;

    public function __construct(
        public Server $server,
        public EmailAutoresponder $autoresponder
    ) {}

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            $account = $this->autoresponder->emailAccount;
            $enabled = $this->autoresponder->enabled && $this->autoresponder->isActive();

            $success = $daemon->updateAutoresponder(
                $this->server,
                $account->domain,
                $account->username,
                $enabled,
                $this->autoresponder->subject ?? 'Out of Office',
                $this->autoresponder->message ?? 'I am currently out of office.',
                $this->autoresponder->start_date?->format('Y-m-d') ?? '',
                $this->autoresponder->end_date?->format('Y-m-d') ?? ''
            );

            if (! $success) {
                throw new \RuntimeException("Daemon returned failure for email.autoresponder-update: {$account->email}");
            }

            Log::info("Autoresponder updated via daemon for: {$account->email}");
        } catch (\Exception $e) {
            Log::error("Failed to update autoresponder: {$this->autoresponder->emailAccount->email}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

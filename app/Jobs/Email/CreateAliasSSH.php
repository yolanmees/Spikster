<?php

namespace App\Jobs\Email;

use App\Models\EmailAlias;
use App\Models\Server;
use App\Services\RemoteDaemonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateAliasSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;

    public function __construct(
        public Server $server,
        public EmailAlias $alias
    ) {}

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            $aliasEmail  = $this->alias->alias;
            $targetEmail = $this->alias->emailAccount->email;

            $success = $daemon->createEmailAlias($this->server, $aliasEmail, $targetEmail);

            if (! $success) {
                throw new \RuntimeException("Daemon returned failure for email.alias-create: {$aliasEmail}");
            }

            Log::info("Email alias created via daemon: {$aliasEmail} → {$targetEmail}");
        } catch (\Exception $e) {
            Log::error("Failed to create email alias: {$this->alias->alias}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

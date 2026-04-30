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

class DeleteAliasSSH implements ShouldQueue
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
            $success = $daemon->deleteEmailAlias($this->server, $this->alias->alias);

            if (! $success) {
                throw new \RuntimeException("Daemon returned failure for email.alias-delete: {$this->alias->alias}");
            }

            Log::info("Email alias deleted via daemon: {$this->alias->alias}");
        } catch (\Exception $e) {
            Log::error("Failed to delete email alias: {$this->alias->alias}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

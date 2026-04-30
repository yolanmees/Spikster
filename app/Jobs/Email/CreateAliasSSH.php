<?php

namespace App\Jobs\Email;

use App\Models\EmailAlias;
use App\Models\Server;
use App\Services\SSHService;
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

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Server $server,
        public EmailAlias $alias
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SSHService $sshService): void
    {
        try {
            $ssh = $sshService->connect($this->server);

            $aliasEmail = preg_replace('/[^a-zA-Z0-9@._+-]/', '', $this->alias->alias);
            $targetEmail = preg_replace('/[^a-zA-Z0-9@._+-]/', '', $this->alias->emailAccount->email);

            // Add to Postfix virtual alias map
            // Use printf to safely write without shell injection
            $ssh->exec('printf "%s %s\n" ' . escapeshellarg($aliasEmail) . ' ' . escapeshellarg($targetEmail) . ' >> /etc/postfix/virtual');
            $ssh->exec("postmap /etc/postfix/virtual");

            // Reload Postfix
            $ssh->exec("systemctl reload postfix");

            $sshService->disconnect();

            Log::info("Email alias created: {$aliasEmail} → {$targetEmail}");

        } catch (\Exception $e) {
            Log::error("Failed to create email alias: {$this->alias->alias}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

<?php

namespace App\Jobs\Email;

use App\Models\EmailAccount;
use App\Models\Server;
use App\Services\SSHService;
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

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Server $server,
        public EmailAccount $account
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SSHService $sshService): void
    {
        try {
            $ssh = $sshService->connect($this->server);

            $email = $this->account->email;
            $quota = $this->account->quota_mb;

            // Update quota in Dovecot quota file
            $ssh->exec("sed -i '/^{$email}:/d' /etc/dovecot/quota 2>/dev/null || true");

            if ($quota > 0) {
                $ssh->exec("echo '{$email}:storage={$quota}M' >> /etc/dovecot/quota");
            }

            // Reload Dovecot
            $ssh->exec("systemctl reload dovecot");

            $sshService->disconnect();

            Log::info("Email quota updated: {$email} = {$quota}MB");

        } catch (\Exception $e) {
            Log::error("Failed to update email quota: {$this->account->email}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

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

class UpdateEmailPasswordSSH implements ShouldQueue
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
            $password = $this->account->getAttributes()['password'];

            // Update password in Dovecot users file
            $ssh->exec("sed -i '/^{$email}:/d' /etc/dovecot/users");
            $ssh->exec("echo '{$email}:{$password}' >> /etc/dovecot/users");

            // Reload Dovecot
            $ssh->exec("systemctl reload dovecot");

            $sshService->disconnect();

            Log::info("Email password updated: {$email}");

        } catch (\Exception $e) {
            Log::error("Failed to update email password: {$this->account->email}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

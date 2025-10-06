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

class CreateEmailAccountSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
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

            $domain = $this->account->domain;
            $username = $this->account->username;
            $email = $this->account->email;
            $password = $this->account->getAttributes()['password']; // Get hashed password
            $quota = $this->account->quota_mb;

            // Create mail directory structure
            $ssh->exec("mkdir -p /var/mail/vhosts/{$domain}/{$username}/{cur,new,tmp}");
            $ssh->exec("chown -R vmail:vmail /var/mail/vhosts/{$domain}");
            $ssh->exec("chmod -R 770 /var/mail/vhosts/{$domain}");

            // Add to Postfix virtual mailbox
            $ssh->exec("echo '{$email} {$domain}/{$username}/' >> /etc/postfix/vmailbox");
            $ssh->exec("postmap /etc/postfix/vmailbox");

            // Add to Dovecot users (with hashed password)
            $ssh->exec("echo '{$email}:{$password}' >> /etc/dovecot/users");

            // Set quota if not unlimited
            if ($quota > 0) {
                $ssh->exec("echo '{$email}:storage={$quota}M' >> /etc/dovecot/quota");
            }

            // Reload services
            $ssh->exec("systemctl reload postfix");
            $ssh->exec("systemctl reload dovecot");

            $sshService->disconnect();

            Log::info("Email account created: {$email}");

        } catch (\Exception $e) {
            Log::error("Failed to create email account: {$this->account->email}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

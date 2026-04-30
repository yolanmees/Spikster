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

            // Sanitize all values used in shell commands
            $domain   = preg_replace('/[^a-zA-Z0-9._-]/', '', $this->account->domain);
            $username = preg_replace('/[^a-zA-Z0-9._-]/', '', $this->account->username);
            $email    = preg_replace('/[^a-zA-Z0-9@._+-]/', '', $this->account->email);
            $password = $this->account->getAttributes()['password']; // pre-hashed
            $quota    = (int) $this->account->quota_mb;

            // Create mail directory structure
            $ssh->exec("mkdir -p /var/mail/vhosts/{$domain}/{$username}/{cur,new,tmp}");
            $ssh->exec("chown -R vmail:vmail /var/mail/vhosts/{$domain}");
            $ssh->exec("chmod -R 770 /var/mail/vhosts/{$domain}");

            // Add to Postfix virtual mailbox using printf (safe)
            $ssh->exec('printf "%s %s/%s/\n" ' . escapeshellarg($email) . ' ' . escapeshellarg($domain) . ' ' . escapeshellarg($username) . ' >> /etc/postfix/vmailbox');
            $ssh->exec("postmap /etc/postfix/vmailbox");

            // Add to Dovecot users
            $ssh->exec('printf "%s:%s\n" ' . escapeshellarg($email) . ' ' . escapeshellarg($password) . ' >> /etc/dovecot/users');

            // Set quota if not unlimited
            if ($quota > 0) {
                $ssh->exec('printf "%s:storage=%dM\n" ' . escapeshellarg($email) . ' ' . $quota . ' >> /etc/dovecot/quota');
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

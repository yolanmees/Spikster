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

class DeleteEmailAccountSSH implements ShouldQueue
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

            // Backup maildir before deletion
            $backupDate = date('Y-m-d-His');
            $ssh->exec("mkdir -p /backups/email");
            $ssh->exec("tar -czf /backups/email/{$email}-{$backupDate}.tar.gz -C /var/mail/vhosts/{$domain} {$username}/ 2>/dev/null || true");

            // Remove from Postfix virtual mailbox
            $ssh->exec("sed -i '/{$email}/d' /etc/postfix/vmailbox");
            $ssh->exec("postmap /etc/postfix/vmailbox");

            // Remove from Dovecot users
            $ssh->exec("sed -i '/{$email}/d' /etc/dovecot/users");

            // Remove from quota file
            $ssh->exec("sed -i '/{$email}/d' /etc/dovecot/quota 2>/dev/null || true");

            // Remove maildir
            $ssh->exec("rm -rf /var/mail/vhosts/{$domain}/{$username}");

            // Reload services
            $ssh->exec("systemctl reload postfix");
            $ssh->exec("systemctl reload dovecot");

            $sshService->disconnect();

            Log::info("Email account deleted: {$email}");

        } catch (\Exception $e) {
            Log::error("Failed to delete email account: {$this->account->email}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

<?php

namespace App\Jobs\Email;

use App\Models\EmailDkimKey;
use App\Models\Server;
use App\Services\SSHService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SetupDKIMSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Server $server,
        public EmailDkimKey $dkimKey
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SSHService $sshService): void
    {
        try {
            $ssh = $sshService->connect($this->server);

            $domain = $this->dkimKey->domain;
            $selector = $this->dkimKey->selector;

            // Create DKIM keys directory
            $ssh->exec("mkdir -p /etc/opendkim/keys/{$domain}");

            // Generate DKIM keypair
            $ssh->exec("cd /etc/opendkim/keys/{$domain} && opendkim-genkey -b 2048 -d {$domain} -s {$selector}");
            $ssh->exec("chown opendkim:opendkim /etc/opendkim/keys/{$domain}/{$selector}.private");

            // Read generated keys
            $privateKey = $ssh->exec("cat /etc/opendkim/keys/{$domain}/{$selector}.private");
            $publicKeyRaw = $ssh->exec("cat /etc/opendkim/keys/{$domain}/{$selector}.txt");

            // Extract public key (remove DKIM TXT record formatting)
            preg_match('/p=([A-Za-z0-9+\/=\s]+)/', $publicKeyRaw, $matches);
            $publicKey = isset($matches[1]) ? str_replace([' ', "\n", "\t"], '', $matches[1]) : '';

            // Update database with keys
            $this->dkimKey->update([
                'private_key' => $privateKey,
                'public_key' => $publicKey,
                'active' => true,
            ]);

            // Add to OpenDKIM KeyTable
            $ssh->exec("echo '{$selector}._domainkey.{$domain} {$domain}:{$selector}:/etc/opendkim/keys/{$domain}/{$selector}.private' >> /etc/opendkim/KeyTable");

            // Add to OpenDKIM SigningTable
            $ssh->exec("echo '*@{$domain} {$selector}._domainkey.{$domain}' >> /etc/opendkim/SigningTable");

            // Reload OpenDKIM
            $ssh->exec("systemctl reload opendkim");

            $sshService->disconnect();

            Log::info("DKIM setup completed for domain: {$domain}");

        } catch (\Exception $e) {
            Log::error("Failed to setup DKIM: {$this->dkimKey->domain}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

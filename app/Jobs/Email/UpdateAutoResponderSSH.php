<?php

namespace App\Jobs\Email;

use App\Models\EmailAutoresponder;
use App\Models\Server;
use App\Services\SSHService;
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

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Server $server,
        public EmailAutoresponder $autoresponder
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SSHService $sshService): void
    {
        try {
            $ssh = $sshService->connect($this->server);

            $email = $this->autoresponder->emailAccount->email;
            $domain = $this->autoresponder->emailAccount->domain;
            $username = $this->autoresponder->emailAccount->username;

            $sieveDir = "/var/mail/vhosts/{$domain}/{$username}/sieve";
            $ssh->exec("mkdir -p {$sieveDir}");

            if ($this->autoresponder->enabled && $this->autoresponder->isActive()) {
                // Create Sieve script
                $subject = addslashes($this->autoresponder->subject ?? 'Out of Office');
                $message = addslashes($this->autoresponder->message ?? 'I am currently out of office.');
                $startDate = $this->autoresponder->start_date?->format('Y-m-d') ?? '';
                $endDate = $this->autoresponder->end_date?->format('Y-m-d') ?? '';

                $sieveScript = <<<SIEVE
require ["vacation", "date", "relational"];

if allof (
SIEVE;

                if ($startDate && $endDate) {
                    $sieveScript .= <<<SIEVE

  currentdate :value "ge" "date" "{$startDate}",
  currentdate :value "le" "date" "{$endDate}"
SIEVE;
                }

                $sieveScript .= <<<SIEVE

) {
  vacation :days 1 :subject "{$subject}" "{$message}";
}
SIEVE;

                // Write Sieve script
                $escapedScript = str_replace('"', '\\"', $sieveScript);
                $ssh->exec("echo \"{$escapedScript}\" > {$sieveDir}/vacation.sieve");

                // Compile Sieve script
                $ssh->exec("sievec {$sieveDir}/vacation.sieve");

                // Set as active script
                $ssh->exec("ln -sf {$sieveDir}/vacation.svbin {$sieveDir}/active.svbin");

            } else {
                // Disable autoresponder
                $ssh->exec("rm -f {$sieveDir}/active.svbin");
            }

            // Set permissions
            $ssh->exec("chown -R vmail:vmail {$sieveDir}");

            $sshService->disconnect();

            Log::info("Autoresponder updated for: {$email}");

        } catch (\Exception $e) {
            Log::error("Failed to update autoresponder: {$this->autoresponder->emailAccount->email}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

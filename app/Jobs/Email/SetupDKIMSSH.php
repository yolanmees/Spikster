<?php

namespace App\Jobs\Email;

use App\Models\EmailDkimKey;
use App\Models\Server;
use App\Services\RemoteDaemonService;
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

    public function __construct(
        public Server $server,
        public EmailDkimKey $dkimKey
    ) {}

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            $keys = $daemon->setupDKIM($this->server, $this->dkimKey->domain, $this->dkimKey->selector);

            if (empty($keys)) {
                throw new \RuntimeException("Daemon returned failure for email.dkim-setup: {$this->dkimKey->domain}");
            }

            $this->dkimKey->update([
                'private_key' => $keys['PrivateKey'] ?? $keys['private_key'] ?? '',
                'public_key' => $keys['PublicKey'] ?? $keys['public_key'] ?? '',
                'active' => true,
            ]);

            Log::info("DKIM setup completed via daemon for domain: {$this->dkimKey->domain}");
        } catch (\Exception $e) {
            Log::error("Failed to setup DKIM: {$this->dkimKey->domain}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

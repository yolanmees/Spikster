<?php

namespace App\Jobs\Email;

use App\Models\Site;
use App\Services\RemoteDaemonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InstallRoundcubeSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public $tries = 2;

    public function __construct(
        public Site $site
    ) {}

    public function handle(RemoteDaemonService $daemon): void
    {
        $server = $this->site->server;

        if (! $server) {
            throw new \RuntimeException('Server not found for site');
        }

        Log::info('Installing Roundcube webmail via daemon', [
            'site_id' => $this->site->site_id,
            'domain' => $this->site->domain,
        ]);

        $siteRoot = "/home/{$this->site->username}/web/{$this->site->domain}/public_html";
        $dbName = $this->site->database_name ?? "site_{$this->site->site_id}";
        $dbUser = $this->site->username;
        $dbPass = $this->site->database_password ?? Str::random(32);
        $php = $this->site->php ?? '8.3';

        $success = $daemon->installRoundcube(
            $server,
            $this->site->domain,
            $siteRoot,
            $dbName,
            $dbUser,
            $dbPass,
            $php
        );

        if (! $success) {
            throw new \RuntimeException("Daemon returned failure for email.roundcube-install on {$this->site->domain}");
        }

        Log::info('Roundcube installation completed via daemon', [
            'site_id' => $this->site->site_id,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Roundcube installation failed', [
            'site_id' => $this->site->site_id,
            'error' => $exception->getMessage(),
        ]);
    }
}

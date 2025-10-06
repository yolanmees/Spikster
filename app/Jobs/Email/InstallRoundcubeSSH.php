<?php

namespace App\Jobs\Email;

use App\Models\Site;
use App\Services\SSHService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InstallRoundcubeSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Site $site
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SSHService $sshService): void
    {
        Log::info('Installing Roundcube webmail', [
            'site_id' => $this->site->site_id,
            'domain' => $this->site->domain,
        ]);

        $server = $this->site->server;

        if (! $server) {
            throw new \Exception('Server not found for site');
        }

        $siteRoot = "/home/{$this->site->username}/web/{$this->site->domain}/public_html";
        $dbName = $this->site->database_name ?? "site_{$this->site->site_id}";
        $dbUser = $this->site->username;
        $dbPass = $this->site->database_password ?? \Illuminate\Support\Str::random(32);

        // Upload installation script
        $scriptPath = storage_path('scripts/install-roundcube.sh');
        $remoteScriptPath = "/tmp/install-roundcube-{$this->site->site_id}.sh";

        $sshService->uploadFile($server, $scriptPath, $remoteScriptPath);

        // Execute installation script
        $command = sprintf(
            'sudo bash %s %s %s %s %s %s',
            escapeshellarg($remoteScriptPath),
            escapeshellarg($this->site->domain),
            escapeshellarg($siteRoot),
            escapeshellarg($dbName),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass)
        );

        $output = $sshService->executeCommand($server, $command);

        // Clean up
        $sshService->executeCommand($server, "rm -f {$remoteScriptPath}");

        // Create nginx configuration for webmail
        $this->createNginxConfig($sshService, $server);

        Log::info('Roundcube installation completed', [
            'site_id' => $this->site->site_id,
            'output' => $output,
        ]);
    }

    /**
     * Create nginx configuration for Roundcube.
     */
    protected function createNginxConfig(SSHService $sshService, $server): void
    {
        $webmailPath = "/home/{$this->site->username}/web/{$this->site->domain}/public_html/webmail";

        $nginxConfig = <<<NGINX
# Roundcube Webmail Configuration for {$this->site->domain}
location ^~ /webmail {
    alias {$webmailPath};
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Deny access to .htaccess files
    location ~ /\.ht {
        deny all;
    }

    # Deny access to sensitive directories
    location ~ ^/webmail/(config|temp|logs|bin|SQL|plugins/.*/config\.inc\.php) {
        deny all;
    }

    # PHP handler
    location ~ ^/webmail/(.+\.php)$ {
        alias {$webmailPath};
        try_files /\$1 =404;

        fastcgi_pass unix:/var/run/php/php{$this->site->php}-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$request_filename;
        include fastcgi_params;

        # Increase timeouts for large attachments
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Static files
    location ~* ^/webmail/.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt)$ {
        alias {$webmailPath};
        access_log off;
        expires 30d;
    }
}
NGINX;

        $configPath = "/etc/nginx/sites-available/{$this->site->domain}-webmail.conf";

        // Upload nginx config
        $tempConfig = tempnam(sys_get_temp_dir(), 'roundcube-nginx');
        file_put_contents($tempConfig, $nginxConfig);

        $sshService->uploadFile($server, $tempConfig, $configPath);
        unlink($tempConfig);

        // Enable site and reload nginx
        $commands = [
            "sudo ln -sf {$configPath} /etc/nginx/sites-enabled/{$this->site->domain}-webmail.conf",
            'sudo nginx -t',
            'sudo systemctl reload nginx',
        ];

        foreach ($commands as $command) {
            $sshService->executeCommand($server, $command);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Roundcube installation failed', [
            'site_id' => $this->site->site_id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

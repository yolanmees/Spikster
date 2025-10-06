<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SSH2;

class ResetSitePermissionsSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $site;

    /**
     * Create a new job instance.
     */
    public function __construct($site)
    {
        $this->site = $site;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('ResetSitePermissionsSSH: Starting permission reset', [
            'site_id' => $this->site->site_id,
            'username' => $this->site->username,
            'server_ip' => $this->site->server->ip,
        ]);

        try {
            $ssh = new SSH2($this->site->server->ip, 22);
            
            if (!$ssh->login('spikster', $this->site->server->password)) {
                throw new \Exception('SSH login failed');
            }

            $ssh->setTimeout(360);

            // Fix ownership: user owns files, www-data is group
            Log::info('ResetSitePermissionsSSH: Setting ownership');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S chown -R '.$this->site->username.':www-data /home/'.$this->site->username);

            // Set directory permissions (755 = rwxr-xr-x)
            Log::info('ResetSitePermissionsSSH: Setting directory permissions');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S chmod 755 /home/'.$this->site->username);
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S chmod 755 /home/'.$this->site->username.'/web');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S chmod 755 /home/'.$this->site->username.'/log');
            
            // Set all subdirectories to 755
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S find /home/'.$this->site->username.'/web -type d -exec chmod 755 {} \;');

            // Set all files to 644 (rw-r--r--)
            Log::info('ResetSitePermissionsSSH: Setting file permissions');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S find /home/'.$this->site->username.'/web -type f -exec chmod 644 {} \;');

            // Restart nginx to apply changes
            Log::info('ResetSitePermissionsSSH: Restarting nginx');
            $ssh->exec('echo '.$this->site->server->password.' | sudo -S systemctl restart nginx');

            $ssh->exec('exit');

            Log::info('ResetSitePermissionsSSH: Permission reset completed successfully', [
                'site_id' => $this->site->site_id,
            ]);

        } catch (\Exception $e) {
            Log::error('ResetSitePermissionsSSH: Exception occurred', [
                'site_id' => $this->site->site_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('ResetSitePermissionsSSH: Job failed', [
            'site_id' => $this->site->site_id,
            'username' => $this->site->username,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

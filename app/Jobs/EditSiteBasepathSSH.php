<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SSH2;

class EditSiteBasepathSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $site;

    protected $oldbasepath;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($site, $oldbasepath)
    {
        $this->site = $site;
        $this->oldbasepath = $oldbasepath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Clean old basepath (remove leading slash)
        $cleanOldBasepath = $this->oldbasepath ? ltrim($this->oldbasepath, '/') : '';
        
        if ($cleanOldBasepath) {
            $oldbasepath = '/home/'.$this->site->username.'/web/'.$cleanOldBasepath;
        } else {
            $oldbasepath = '/home/'.$this->site->username.'/web';
        }

        // Clean new basepath (remove leading slash)
        $cleanNewBasepath = $this->site->basepath ? ltrim($this->site->basepath, '/') : '';
        
        if ($cleanNewBasepath) {
            $basepath = '/home/'.$this->site->username.'/web/'.$cleanNewBasepath;
        } else {
            $basepath = '/home/'.$this->site->username.'/web';
        }

        Log::info('EditSiteBasepathSSH: Updating nginx config', [
            'site_id' => $this->site->site_id,
            'domain' => $this->site->domain,
            'username' => $this->site->username,
            'old_basepath' => $oldbasepath,
            'new_basepath' => $basepath,
        ]);

        $ssh = new SSH2($this->site->server->ip, 22);
        $ssh->login('spikster', $this->site->server->password);
        $ssh->setTimeout(360);
        
        // Update both sites-available and sites-enabled configs
        $availableConfig = '/etc/nginx/sites-available/'.$this->site->username.'.conf';
        $enabledConfig = '/etc/nginx/sites-enabled/'.$this->site->username.'.conf';
        
        // Use rpl like other jobs (with -i for in-place and -w for whole words)
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo rpl -i -w "root '.$oldbasepath.';" "root '.$basepath.';" '.$availableConfig);
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo rpl -i -w "root '.$oldbasepath.';" "root '.$basepath.';" '.$enabledConfig);
        
        // Verify the change
        $verifyOutput = $ssh->exec('echo '.$this->site->server->password.' | sudo -S cat '.$availableConfig.' | grep "root "');
        Log::info('EditSiteBasepathSSH: Nginx config after update', [
            'site_id' => $this->site->site_id,
            'config_file' => $availableConfig,
            'root_line' => trim($verifyOutput),
        ]);
        
        // Test nginx config
        $testOutput = $ssh->exec('echo '.$this->site->server->password.' | sudo -S nginx -t 2>&1');
        Log::info('EditSiteBasepathSSH: Nginx config test', [
            'site_id' => $this->site->site_id,
            'test_output' => trim($testOutput),
        ]);
        
        // Restart nginx (like other jobs do)
        $ssh->exec('echo '.$this->site->server->password.' | sudo -S sudo systemctl restart nginx.service');
        
        Log::info('EditSiteBasepathSSH: Nginx restarted successfully', [
            'site_id' => $this->site->site_id,
        ]);
        
        $ssh->exec('exit');
    }
}

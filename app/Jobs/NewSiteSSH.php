<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Net\SSH2;
use phpseclib3\Net\SFTP;

class NewSiteSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $server;

    protected $site;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($server, $site)
    {
        $this->server = $server;
        $this->site = $site;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('NewSiteSSH: Starting site creation', [
            'site_id' => $this->site->site_id,
            'domain' => $this->site->domain,
            'username' => $this->site->username,
            'server_ip' => $this->server->ip,
        ]);

        try {
            Log::info('NewSiteSSH: Connecting to SSH', [
                'server_ip' => $this->server->ip,
            ]);

            $ssh = new SSH2($this->server->ip, 22);
            
            if (!$ssh->login('spikster', $this->server->password)) {
                Log::error('NewSiteSSH: SSH login failed', [
                    'server_ip' => $this->server->ip,
                ]);
                throw new \Exception('SSH login failed');
            }

            Log::info('NewSiteSSH: SSH connected successfully');
            
            $ssh->setTimeout(360);
            
            // Step 1: Get the newsite script content from storage
            Log::info('NewSiteSSH: Loading newsite script from storage');
            $scriptContent = Storage::get('cipi/newsite.sh');
            
            if (empty($scriptContent)) {
                throw new \Exception('Newsite script not found in storage/app/cipi/newsite.sh');
            }
            
            // Step 2: Upload script via SFTP
            Log::info('NewSiteSSH: Uploading script via SFTP');
            $sftp = new SFTP($this->server->ip, 22);
            
            if (!$sftp->login('spikster', $this->server->password)) {
                throw new \Exception('SFTP login failed');
            }
            
            // Upload script to /tmp/newsite
            $sftp->put('/tmp/newsite_'.$this->site->username.'.sh', $scriptContent);
            Log::info('NewSiteSSH: Script uploaded successfully');
            
            // Step 3: Make script executable and convert line endings
            Log::info('NewSiteSSH: Making script executable and converting line endings');
            $ssh->exec('echo '.$this->server->password.' | sudo -S chmod +x /tmp/newsite_'.$this->site->username.'.sh');
            $output = $ssh->exec('echo '.$this->server->password.' | sudo -S dos2unix /tmp/newsite_'.$this->site->username.'.sh');
            Log::debug('NewSiteSSH: dos2unix output', ['output' => $output]);
            
            // Step 4: Execute newsite script
            $command = 'echo '.$this->server->password.' | sudo -S sudo bash /tmp/newsite_'.$this->site->username.'.sh '.
                '-dbr '.$this->server->database.' '.
                '-u '.$this->site->username.' '.
                '-p '.$this->site->password.' '.
                '-dbp '.$this->site->database.' '.
                '-php '.$this->site->php.' '.
                '-id '.$this->site->site_id.' '.
                '-r '.config('app.url').' '.
                '-b '.$this->site->basepath;
            
            Log::info('NewSiteSSH: Executing newsite script', [
                'username' => $this->site->username,
                'php' => $this->site->php,
                'basepath' => $this->site->basepath,
            ]);
            
            $output = $ssh->exec($command);
            Log::info('NewSiteSSH: Script execution completed', ['output' => $output]);
            
            // Step 5: Cleanup
            Log::info('NewSiteSSH: Cleaning up script');
            $output = $ssh->exec('echo '.$this->server->password.' | sudo -S sudo rm /tmp/newsite_'.$this->site->username.'.sh');
            Log::debug('NewSiteSSH: Cleanup output', ['output' => $output]);
            
            $ssh->exec('exit');
            
            Log::info('NewSiteSSH: Site creation completed successfully', [
                'site_id' => $this->site->site_id,
            ]);

        } catch (\Exception $e) {
            Log::error('NewSiteSSH: Exception occurred', [
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
        Log::error('NewSiteSSH: Job failed', [
            'site_id' => $this->site->site_id,
            'domain' => $this->site->domain,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

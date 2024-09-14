<?php

namespace App\Jobs;

use phpseclib3\Net\SSH2;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class PanelDomainSslSSH implements ShouldQueue
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
        $this->server   = $server;
        $this->site     = $site;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ssh = new SSH2($this->server->ip, 22);
        $ssh->login('spikster', $this->server->password);
        $ssh->setTimeout(360);
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo certbot --nginx -d '.$this->site->domain.' --non-interactive --agree-tos --register-unsafely-without-email');
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo systemctl restart nginx.service');
        $ssh->exec("echo ".$this->server->password." | sudo -S sed -i 's/443 ssl/443 ssl http2/g' /etc/nginx/sites-enabled/panel.conf");
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo systemctl restart nginx.service');
        $ssh->exec('exit');
    }
}

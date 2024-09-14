<?php

namespace App\Jobs;

use phpseclib3\Net\SSH2;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class CronSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $server;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($server)
    {
        $this->server   = $server;
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
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo unlink /etc/cron.d/cipi.crontab');
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo wget '.config('app.url').'/conf/cron/'.$this->server->server_id.' -O /etc/cron.d/cipi.crontab');
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo crontab /etc/cron.d/cipi.crontab');
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo service cron reload o /etc/init.d/cron reload');
        $ssh->exec('exit');
    }
}

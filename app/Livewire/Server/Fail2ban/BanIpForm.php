<?php

namespace App\Livewire\Server\Fail2ban;

use App\Models\Server;
use App\Services\DaemonService;
use App\Services\Fail2banService;
use Livewire\Component;

class BanIpForm extends Component
{
    public $server_id;
    public $server;
    public $ip = '';
    public $jail = 'sshd';
    public $jails = [];
    public $action = 'ban';

    protected $rules = [
        'ip'   => 'required|ip',
        'jail' => 'required|string',
    ];

    protected $messages = [
        'ip.required' => 'IP address is required',
        'ip.ip'       => 'Please enter a valid IP address',
        'jail.required' => 'Please select a jail',
    ];

    public function mount($server_id)
    {
        $this->server_id = $server_id;
        $this->server    = Server::where('server_id', $server_id)->first();
        $this->loadJails();
    }

    public function loadJails()
    {
        try {
            $service    = app(Fail2banService::class);
            $this->jails = $service->getJails($this->server);
        } catch (\Throwable) {
            $this->jails = [['name' => 'sshd']];
        }
    }

    public function banIp()
    {
        $this->validate();

        try {
            $service = app(Fail2banService::class);
            $result  = $service->banIp($this->server, $this->ip, $this->jail);

            if ($result) {
                session()->flash('success', "IP {$this->ip} banned successfully in jail {$this->jail}.");
                $this->reset('ip');
                $this->dispatch('refreshIptables');
            } else {
                session()->flash('error', 'Failed to ban IP address.');
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function whitelistIp()
    {
        $this->validate(['ip' => 'required|ip']);

        try {
            $service = app(Fail2banService::class);
            $result  = $service->whitelistIp($this->server, $this->ip);

            if ($result) {
                session()->flash('success', "IP {$this->ip} whitelisted successfully.");
                $this->reset('ip');
                $this->dispatch('refreshIptables');
            } else {
                session()->flash('error', 'Failed to whitelist IP.');
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function submit()
    {
        if ($this->action === 'whitelist') {
            $this->whitelistIp();
        } else {
            $this->banIp();
        }
    }

    public function checkIpStatus()
    {
        if (empty($this->ip) || ! filter_var($this->ip, FILTER_VALIDATE_IP)) {
            return;
        }

        try {
            $service = app(Fail2banService::class);
            $banned  = $service->isIpBanned($this->server, $this->ip);

            if (! empty($banned)) {
                session()->flash('info', "IP {$this->ip} is currently banned in " . count($banned) . ' jail(s).');
            } else {
                session()->flash('info', "IP {$this->ip} is not currently banned.");
            }
        } catch (\Throwable) {
            // silently fail
        }
    }

    public function render()
    {
        return view('livewire.server.fail2ban.ban-ip-form');
    }
}

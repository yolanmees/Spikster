<?php

namespace App\Livewire\Server\Fail2ban;

use App\Models\Server;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class BanIpForm extends Component
{
    public $server_id;
    public $server;
    public $ip = '';
    public $jail = 'sshd';
    public $jails = [];
    public $action = 'ban'; // 'ban' or 'whitelist'

    protected $rules = [
        'ip' => 'required|ip',
        'jail' => 'required|string',
    ];

    protected $messages = [
        'ip.required' => 'IP address is required',
        'ip.ip' => 'Please enter a valid IP address',
        'jail.required' => 'Please select a jail',
    ];

    public function render()
    {
        return view('livewire.server.fail2ban.ban-ip-form');
    }

    public function mount($server_id)
    {
        $this->server_id = $server_id;
        $this->server = Server::where(['server_id' => $server_id])->first();
        $this->loadJails();
    }

    public function loadJails()
    {
        try {
            $url = config('app.url') . '/api/servers/' . $this->server->server_id . '/fail2ban/jails';
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();
                $this->jails = $data['jails'] ?? [];
            }
        } catch (\Exception $e) {
            // Use default jail if can't load
            $this->jails = [['name' => 'sshd']];
        }
    }

    public function banIp()
    {
        $this->validate();

        try {
            $url = config('app.url') . '/api/servers/' . $this->server->server_id . '/fail2ban/ban';
            $response = Http::post($url, [
                'ip' => $this->ip,
                'jail' => $this->jail,
            ]);

            if ($response->successful()) {
                session()->flash('success', "IP {$this->ip} has been banned successfully in jail {$this->jail}.");
                $this->reset('ip');
                $this->dispatch('refreshIptables');
            } else {
                $data = $response->json();
                session()->flash('error', $data['message'] ?? 'Failed to ban IP address.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function whitelistIp()
    {
        $this->validate(['ip' => 'required|ip']);

        try {
            $url = config('app.url') . '/api/servers/' . $this->server->server_id . '/fail2ban/whitelist';
            $response = Http::post($url, [
                'ip' => $this->ip,
            ]);

            if ($response->successful()) {
                session()->flash('success', "IP {$this->ip} has been whitelisted successfully.");
                $this->reset('ip');
                $this->dispatch('refreshIptables');
            } else {
                $data = $response->json();
                session()->flash('error', $data['message'] ?? 'Failed to whitelist IP address.');
            }
        } catch (\Exception $e) {
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
        if (empty($this->ip) || !filter_var($this->ip, FILTER_VALIDATE_IP)) {
            return;
        }

        try {
            $url = config('app.url') . '/api/servers/' . $this->server->server_id . '/fail2ban/check/' . $this->ip;
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['is_banned']) {
                    session()->flash('info', "IP {$this->ip} is currently banned in " . count($data['ban_details']) . " jail(s).");
                } else {
                    session()->flash('info', "IP {$this->ip} is not currently banned.");
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }
    }
}

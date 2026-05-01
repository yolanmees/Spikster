<?php

namespace App\Livewire\Server\Fail2ban;

use App\Models\Server;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;
use Livewire\Component;

class Iptables extends Component
{
    public $server_id;

    public $server;

    public $iptables;

    public $selectedJail = 'all';

    public $searchIp = '';

    public function render()
    {
        return view('livewire.server.fail2ban.iptables');
    }

    public function mount($server_id)
    {
        $this->server_id = $server_id;
        $this->server = Server::where(['server_id' => $server_id])->first();
        $this->iptables = $this->getIptables();
    }

    public function getIptables()
    {
        $url = config('app.url').'/api/servers/'.$this->server->server_id.'/fail2ban';
        $response = Http::get($url);

        return $response->json();
    }

    #[On('refreshIptables')]
    public function refresh()
    {
        $this->iptables = $this->getIptables();
    }

    public function unbanIp($ip, $jail = null)
    {
        try {
            $url = config('app.url').'/api/servers/'.$this->server->server_id.'/fail2ban/unban';
            $response = Http::post($url, [
                'ip' => $ip,
                'jail' => $jail,
            ]);

            if ($response->successful()) {
                session()->flash('success', "IP {$ip} has been unbanned successfully.");
                $this->refresh();
            } else {
                session()->flash('error', "Failed to unban IP {$ip}.");
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error: '.$e->getMessage());
        }
    }

    public function getFilteredIptables()
    {
        if (! isset($this->iptables[0]) || ! is_array($this->iptables[0])) {
            return [];
        }

        $filtered = $this->iptables[0];

        // Filter by jail
        if ($this->selectedJail !== 'all') {
            $filtered = array_filter($filtered, function ($ip) {
                return isset($ip[1]) && $ip[1] === $this->selectedJail;
            });
        }

        // Filter by IP search
        if (! empty($this->searchIp)) {
            $filtered = array_filter($filtered, function ($ip) {
                return isset($ip[0]) && str_contains($ip[0], $this->searchIp);
            });
        }

        return $filtered;
    }
}

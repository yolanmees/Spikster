<?php

namespace App\Livewire\Server\Fail2ban;

use App\Models\Server;
use App\Services\Fail2banService;
use Livewire\Attributes\On;
use Livewire\Component;

class Iptables extends Component
{
    public $server_id;
    public $server;
    public $iptables;
    public $selectedJail = 'all';
    public $searchIp = '';

    public function mount($server_id)
    {
        $this->server_id = $server_id;
        $this->server    = Server::where('server_id', $server_id)->first();
        $this->iptables  = $this->getIptables();
    }

    public function getIptables()
    {
        try {
            $service = app(Fail2banService::class);
            return $service->getBannedIps($this->server);
        } catch (\Throwable) {
            return [];
        }
    }

    #[On('refreshIptables')]
    public function refresh()
    {
        $this->iptables = $this->getIptables();
    }

    public function unbanIp($ip, $jail = null)
    {
        try {
            $service = app(Fail2banService::class);
            $result  = $service->unbanIp($this->server, $ip, $jail);

            if ($result) {
                session()->flash('success', "IP {$ip} unbanned successfully.");
                $this->refresh();
            } else {
                session()->flash('error', "Failed to unban IP {$ip}.");
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getFilteredIptables()
    {
        if (empty($this->iptables) || ! is_array($this->iptables)) {
            return [];
        }

        $filtered = $this->iptables;

        if ($this->selectedJail !== 'all') {
            $filtered = array_filter($filtered, fn($ip) => isset($ip[1]) && $ip[1] === $this->selectedJail);
        }

        if (! empty($this->searchIp)) {
            $filtered = array_filter($filtered, fn($ip) => isset($ip[0]) && str_contains($ip[0], $this->searchIp));
        }

        return $filtered;
    }

    public function render()
    {
        return view('livewire.server.fail2ban.iptables');
    }
}

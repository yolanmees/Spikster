<?php

namespace App\Livewire\Server\Fail2ban;

use Livewire\Component;
use App\Models\Server;
use Http;

class Iptables extends Component
{
    public $server_id;
    public $server;
    public $iptables;
    
    public function render()
    {
        return view('livewire.server.fail2ban.iptables');
    }

    public function mount($server_id)
    {
        $this->server_id = $server_id;
        $this->server = Server::where(['server_id' => $server_id])->first();
        $this->iptables = $this->getIptables();
        // dd($this->iptables);
    }

    public function getIptables()
    {
        $url = $this->server->ip . '/api/servers/' . $this->server->server_id . '/fail2ban';
        $response = Http::get($url);
        return $response->json();
    }
}

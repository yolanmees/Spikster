<?php

namespace App\Livewire\Server;

use Livewire\Component;
use App\Models\Server;

class NewServer extends Component
{
    public $serverName;
    public $serverIp;
    public $serverProvider;
    public $serverApiKey;
    public $serverLocation;
    public $serverSshPort = 22;
    public $serverSshPassword;

    public function render()
    {
        return view('livewire.server.new-server');
    }

    public function submit()
    {
        $server = new Server;
        $server->server_id = uniqid();
        $server->database = 'spikster';
        $server->name = $this->serverName;
        $server->ip = $this->serverIp;
        $server->provider = $this->serverProvider;
        $server->location = $this->serverLocation;
        $server->api_key = $this->serverApiKey;
        $server->ssh_port = $this->serverSshPort;
        $server->password = $this->serverSshPassword;
        $server->save();
    }
}

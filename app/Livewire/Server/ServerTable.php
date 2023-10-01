<?php

namespace App\Livewire\Server;

use Livewire\Component;
use App\Models\Server;

class ServerTable extends Component
{
    public $servers;

    public function render()
    {
        return view('livewire.server.server-table');
    }

    public function mount()
    {
        $this->servers = Server::all();
    }

    public function delete($server_id)
    {
        $server = Server::where('server_id', $server_id)->first();
        $server->delete();
        $this->servers = Server::all();
    }
}

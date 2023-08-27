<?php

namespace App\Livewire\Server;

use Livewire\Component;
use App\Models\Server;
use Http;

class PackagesInstalled extends Component
{
    public $server_id;
    public $server;
    public $packages;

    public function render()
    {
        return view('livewire.server.packages-installed');
    }

    public function mount($server_id)
    {
        $this->server_id = $server_id;
        $this->server = Server::where(['server_id' => $server_id])->first();
        $this->packages = $this->getPackages();
        // dd($this->packages);
    }

    public function getPackages()
    {
        $url = $this->server->ip . '/api/servers/' . $this->server->server_id . '/packages';
        $response = Http::get($url);
        return $response->json();
    }

    public function openInstaller()
    {
        
    }
}

<?php

namespace App\Livewire;

use App\Models\Server;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class ManageService extends Component
{
    public $server_id;
    public $services = [];
    public $message;
    private $server;

    public function mount()
    {
        $this->server = Server::where('server_id', $this->server_id)->first();
        $this->fetchServicesStatus();
    }

    public function fetchServicesStatus()
    {
        $response = Http::get('http://'.$this->server->ip.'/api/services', [
            'format' => 'json'
        ]);

        if ($response->successful()) {
            $this->services = $response->json()['services'];
        } else {
            $this->message = 'Failed to fetch services status';
        }
    }

    public function restartService($service)
    {
        $response = Http::post('http://'.$this->server->ip.'/api/services/manage', [
            'format' => 'json',
            'action' => 'restart',
            'service' => $service,
        ]);

        if ($response->successful()) {
            $this->message = $response->json()['message'];
        } else {
            $this->message = 'Failed to manage service';
        }

        $this->fetchServicesStatus();
    }

    public function render()
    {
        return view('livewire.manage-service');
    }
}

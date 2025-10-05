<?php

namespace App\Livewire\Server;

use App\Models\Server;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class SystemServices extends Component
{
    public $server_id;
    public $services = [
        'nginx' => ['name' => 'nginx', 'status' => 'unknown'],
        'php' => ['name' => 'PHP-FPM', 'status' => 'unknown'],
        'mysql' => ['name' => 'MySql', 'status' => 'unknown'],
        'redis' => ['name' => 'Redis', 'status' => 'unknown'],
        'supervisor' => ['name' => 'Supervisor', 'status' => 'unknown'],
    ];
    public $restarting = [];
    private $server;

    public function mount($server_id)
    {
        $this->server_id = $server_id;
        $this->server = Server::where('server_id', $this->server_id)->first();
    }

    public function restartService($service)
    {
        if (!$this->server) {
            session()->flash('error', 'Server not found.');
            return;
        }

        $this->restarting[$service] = true;

        try {
            $response = Http::timeout(30)->post(
                'http://' . $this->server->ip . '/api/servers/' . $this->server_id . '/servicerestart/' . $service,
                ['format' => 'json']
            );

            if ($response->successful()) {
                session()->flash('success', ucfirst($service) . ' has been restarted successfully.');
            } else {
                session()->flash('error', 'Failed to restart ' . $service . '.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error restarting ' . $service . ': ' . $e->getMessage());
        } finally {
            unset($this->restarting[$service]);
        }
    }

    public function render()
    {
        return view('livewire.server.system-services');
    }
}

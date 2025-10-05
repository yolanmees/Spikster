<?php

namespace App\Livewire\Server;

use App\Models\Server;
use App\Services\ServerService;
use Livewire\Component;

class ServerInformation extends Component
{
    public $server_id;
    public $serverName = '';
    public $serverIp = '';
    public $serverProvider = '';
    public $serverLocation = '';
    public $isSubmitting = false;

    protected $rules = [
        'serverName' => 'required|string|min:3|max:255',
        'serverIp' => 'required|ip',
        'serverProvider' => 'nullable|string|max:255',
        'serverLocation' => 'nullable|string|max:255',
    ];

    public function mount($server_id)
    {
        $this->server_id = $server_id;
        $this->loadServerData();
    }

    public function loadServerData()
    {
        $server = Server::where('server_id', $this->server_id)->first();
        
        if ($server) {
            $this->serverName = $server->name;
            $this->serverIp = $server->ip;
            $this->serverProvider = $server->provider ?? '';
            $this->serverLocation = $server->location ?? '';
            
            // Force Livewire to update
            $this->dispatch('server-data-loaded');
        }
    }

    public function submit(ServerService $serverService)
    {
        $this->validate();

        $this->isSubmitting = true;

        try {
            $server = Server::where('server_id', $this->server_id)->first();

            if (!$server) {
                session()->flash('error', 'Server not found.');
                return;
            }

            // Check for IP conflict
            $ipConflict = Server::where('ip', $this->serverIp)
                ->where('server_id', '!=', $this->server_id)
                ->exists();

            if ($ipConflict) {
                $this->addError('serverIp', 'This IP address is already in use by another server.');
                $this->isSubmitting = false;
                return;
            }

            $server->update([
                'name' => $this->serverName,
                'ip' => $this->serverIp,
                'provider' => $this->serverProvider,
                'location' => $this->serverLocation,
            ]);

            session()->flash('success', 'Server information updated successfully.');
            
            $this->dispatch('server-updated');
            $this->loadServerData();
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating server: ' . $e->getMessage());
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function render()
    {
        return view('livewire.server.server-information');
    }
}

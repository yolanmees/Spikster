<?php

namespace App\Livewire\Server\Fail2ban;

use App\Models\Server;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;
use Livewire\Component;

class Jails extends Component
{
    public $server_id;

    public $server;

    public $jails = [];

    public $statistics = [];

    public $isLoading = true;

    public function render()
    {
        return view('livewire.server.fail2ban.jails');
    }

    public function mount($server_id)
    {
        $this->server_id = $server_id;
        $this->server = Server::where(['server_id' => $server_id])->first();
        $this->loadJails();
        $this->loadStatistics();
    }

    public function loadJails()
    {
        try {
            $url = config('app.url').'/api/servers/'.$this->server->server_id.'/fail2ban/jails';
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();
                $this->jails = $data['jails'] ?? [];
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load jails: '.$e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function loadStatistics()
    {
        try {
            $url = config('app.url').'/api/servers/'.$this->server->server_id.'/fail2ban/stats';
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();
                $this->statistics = $data['statistics'] ?? [];
            }
        } catch (\Exception $e) {
            // Silently fail for stats
        }
    }

    #[On('refreshJails')]
    public function refresh()
    {
        $this->isLoading = true;
        $this->loadJails();
        $this->loadStatistics();
    }

    public function viewJailDetails($jailName)
    {
        $this->dispatch('showJailDetails', jailName: $jailName);
    }
}

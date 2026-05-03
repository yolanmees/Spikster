<?php

namespace App\Livewire\Server\Fail2ban;

use App\Models\Server;
use App\Services\Fail2banService;
use Livewire\Attributes\On;
use Livewire\Component;

class Jails extends Component
{
    public $server_id;
    public $server;
    public $jails      = [];
    public $statistics = [];
    public $isLoading  = true;

    public function mount($server_id)
    {
        $this->server_id = $server_id;
        $this->server    = Server::where('server_id', $server_id)->first();
        $this->loadJails();
        $this->loadStatistics();
    }

    public function loadJails()
    {
        try {
            $service     = app(Fail2banService::class);
            $this->jails = $service->getJails($this->server);
        } catch (\Throwable $e) {
            session()->flash('error', 'Failed to load jails: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function loadStatistics()
    {
        try {
            $service          = app(Fail2banService::class);
            $this->statistics = $service->getStatistics($this->server);
        } catch (\Throwable) {
            // silently fail
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

    public function render()
    {
        return view('livewire.server.fail2ban.jails');
    }
}

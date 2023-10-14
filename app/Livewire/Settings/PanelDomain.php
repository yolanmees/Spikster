<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class PanelDomain extends Component
{
    public $panel_domain;
    
    public function mount()
    {
        $this->panel_domain = config('app.url');
    }

    public function render()
    {
        return view('livewire.settings.panel-domain');
    }

    public function updatePanelDomain()
    {
        // send update over api to server
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . auth()->user()->tokens->first()->token,
        ])->patch(config('app.url') . 'api/servers/panel/domain', [
            'domain' => $this->panel_domain,
        ]);
        dd($response->json());
    }
}

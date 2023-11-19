<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Models\Server;
use App\Models\Site;

class PanelDomain extends Component
{
    public $panel_domain;
    
    public function mount()
    {
        $server = Server::where('default', 1)->first();
        $site = Site::where('server_id', $server->id)->where('panel', 1)->first();
        if (!$site) {
            $domain = '';
        } else {
            $domain = $site->domain;
        }
        $this->panel_domain = $domain;
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
        putenv("app.url=".$this->panel_domain);
    }
}

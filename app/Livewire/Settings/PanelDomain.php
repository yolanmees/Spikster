<?php

namespace App\Livewire\Settings;

use App\Models\Server;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class PanelDomain extends Component
{
    public $panel_domain;

    public function mount()
    {
        $server = Server::where('default', 1)->first();
        $site = $server ? Site::where('server_id', $server->id)->where('panel', 1)->first() : null;
        $this->panel_domain = $site ? $site->domain : '';
    }

    public function render()
    {
        return view('livewire.settings.panel-domain');
    }

    public function updatePanelDomain()
    {
        $token = Auth::user()->createToken('panel-domain')->plainTextToken;

        try {
            Http::withToken($token)->patch(config('app.url').'/api/servers/panel/domain', [
                'domain' => $this->panel_domain,
            ]);

            session()->flash('message', 'Panel domain updated successfully.');
        } finally {
            Auth::user()->tokens()->where('name', 'panel-domain')->delete();
        }
    }

    public function sslPanelDomain()
    {
        $token = Auth::user()->createToken('panel-ssl')->plainTextToken;

        try {
            Http::withToken($token)->post(config('app.url').'/api/servers/panel/ssl');

            session()->flash('message', 'SSL configured for panel domain.');
        } finally {
            Auth::user()->tokens()->where('name', 'panel-ssl')->delete();
        }
    }
}

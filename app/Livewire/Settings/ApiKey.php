<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Server;
use App\Models\Site;
use Auth;

class ApiKey extends Component
{
    public $api_endpoint;
    public $api_key;
    public $panel_domain;
    public $show_api_key = false;

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
        $this->api_endpoint = $this->panel_domain . 'api';
    }

    public function render()
    {
        return view('livewire.settings.api-key');
    }

    public function generateApiKey()
    {
        $this->api_key = Auth::user()->createToken('API Token')->plainTextToken;
        $this->show_api_key = true;
    }
}

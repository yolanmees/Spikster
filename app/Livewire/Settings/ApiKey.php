<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Auth;

class ApiKey extends Component
{
    public $api_endpoint;
    public $api_key;
    public $show_api_key = false;

    public function mount()
    {
        $this->api_endpoint = config('app.url') . 'api';
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

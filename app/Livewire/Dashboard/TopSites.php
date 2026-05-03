<?php

namespace App\Livewire\Dashboard;

use App\Models\Site;
use Livewire\Component;

class TopSites extends Component
{
    public $sites;

    public function mount()
    {
        $this->sites = Site::with('server')->take(5)->get();
    }

    public function render()
    {
        return view('livewire.dashboard.top-sites');
    }
}

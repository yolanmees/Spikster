<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Site;

class TopSites extends Component
{
    public $sites;

    public function mount()
    {
        $this->sites = Site::take(5)->get();
    }

    public function render()
    {
        return view('livewire.dashboard.top-sites');
    }
}

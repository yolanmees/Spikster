<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Site;

class SiteTable extends Component
{
    public $sites;
    public function render()
    {
        return view('livewire.site.site-table');
    }

    public function mount()
    {
        $this->sites = Site::all();
        // dd($this->sites);
    }

    public function delete($site_id)
    {
        $site = Site::where('site_id', $site_id)->first();
        $site->delete();
        $this->sites = Site::all();
    }
}

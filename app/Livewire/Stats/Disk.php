<?php

namespace App\Livewire\Stats;

use Http;
use App\Models\Server;
use Livewire\Component;

class Disk extends Component
{
    private $server;
    public array $dataset = [];
    public array $labels = [];
    public $disk;
    public $total;

    public function mount($server_id)
    {
        $this->server = Server::where('server_id', $server_id)->first();
        try {
            $disk = Http::get($this->server->ip . '/api/servers/' . $this->server->server_id . '/stats/disk');
            $this->disk = $disk->json()['disk'];
            $this->labels = $this->getLabels();
            $this->total = $this->disk[0]['write_bytes'] / 1024 / 1024;
            
            foreach ($this->disk as $key => $disk) {
                if (!isset($this->dataset[$disk['disk_name'] ])) {
                    $this->dataset[$disk['disk_name'] ]['label'] = $disk['disk_name'];
                    $this->dataset[$disk['disk_name'] ]['backgroundColor'] = 'rgba(15,64,97,255)';
                    $this->dataset[$disk['disk_name'] ]['borderColor'] = 'rgba(15,64,97,255)';
                }
                $this->dataset[$disk['disk_name'] ]['data'][] = $disk['read_bytes'];
            }
        } catch (\Throwable $th) {
        }
    }

    private function getLabels()
    {
        $labels = [];
        foreach ($this->disk as $disk) {
            $labels[] = date('H:i', strtotime($disk['created_at']));
        }
        return $labels;
    }

    public function render()
    {
        return view('livewire.stats.disk');
    }
}

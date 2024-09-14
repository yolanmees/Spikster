<?php

namespace App\Livewire\Stats;

use App\Models\Server;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class Cpu extends Component
{
    private $server;
    public array $dataset = [];
    public array $labels = [];
    public $cpu;

    public function mount($server_id)
    {
        $this->server = Server::where('server_id', $server_id)->first();
        try {
            $cpu = Http::get($this->server->ip . '/api/servers/' . $this->server->server_id . '/stats/cpu');

            $this->cpu = $cpu->json()['cpu'];

            $this->labels = $this->getLabels();
            $this->dataset = [
                [
                    'label' => "Total",
                    'backgroundColor' => 'rgba(15,64,97,255)',
                    'borderColor' => 'rgba(15,64,97,255)',
                ],
            ];
            foreach ($this->cpu as $key => $cpu) {
                $this->dataset[0]['data'][] = $cpu['total'];
            }
        } catch (\Throwable $th) {
        }

    }

    private function getLabels()
    {
        $labels = [];
        foreach ($this->cpu as $cpu) {
            $labels[] = date('H:i', strtotime($cpu['created_at']));
        }
        return $labels;
    }

    public function render()
    {
        return view('livewire.stats.cpu');
    }
}

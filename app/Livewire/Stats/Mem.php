<?php

namespace App\Livewire\Stats;

use Http;
use App\Models\Server;
use Livewire\Component;

class Mem extends Component
{
    private $server;
    public array $dataset = [];
    public array $labels = [];
    public $total;
    public $mem;

    public function mount($server_id)
    {
        $this->server = Server::where('server_id', $server_id)->first();
        try {
            $mem = Http::get($this->server->ip . '/api/servers/' . $this->server->server_id . '/stats/mem');
            // dd($mem->json());
            $this->mem = $mem->json()['mem'];
            $this->total = $this->mem[0]['total'] / 1024 / 1024;
            $this->labels = $this->getLabels();
            $this->dataset = [
                [
                    'label' => "Total",
                    'backgroundColor' => 'rgba(15,64,97,255)',
                    'borderColor' => 'rgba(15,64,97,255)',
                ],
            ];
            foreach ($this->mem as $key => $mem) {
                $this->dataset[0]['data'][] = $mem['used'] / 1024 / 1024;
            }
        } catch (\Throwable $th) {
        }
    }

    private function getLabels()
    {
        $labels = [];
        foreach ($this->mem as $mem) {
            $labels[] = date('H:i', strtotime($mem['created_at']));
        }
        return $labels;
    }

    public function render()
    {
        return view('livewire.stats.mem');
    }
}

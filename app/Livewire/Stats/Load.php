<?php

namespace App\Livewire\Stats;

use Http;
use App\Models\Server;
use Livewire\Component;

class Load extends Component
{
    private $server;
    public array $dataset = [];
    public array $labels = [];
    public $load;

    public function mount($server_id)
    {
        $this->server = Server::where('server_id', $server_id)->first();
        try {
            $load = Http::get($this->server->ip . '/api/servers/' . $this->server->server_id . '/stats/load');
            $this->load = $load->json()['load'];
            $this->labels = $this->getLabels();
            $this->dataset = [
                [
                    'label' => "Total",
                    'backgroundColor' => 'rgba(15,64,97,255)',
                    'borderColor' => 'rgba(15,64,97,255)',
                ],
            ];
            foreach ($this->load as $key => $load) {
                $this->dataset[0]['data'][] = $load['min1'];
            }
        } catch (\Throwable $th) {
        }
    }

    private function getLabels()
    {
        $labels = [];
        foreach ($this->load as $load) {
            $labels[] = date('H:i', strtotime($load['created_at']));
        }
        return $labels;
    }

    public function render()
    {
        return view('livewire.stats.load');
    }
}

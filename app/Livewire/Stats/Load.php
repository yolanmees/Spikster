<?php

namespace App\Livewire\Stats;

use App\Models\Server;
use App\Services\MonitoringService;
use Livewire\Component;

class Load extends Component
{
    private $server;

    public array $dataset = [];

    public array $labels = [];

    public $load;

    public function mount($server_id, MonitoringService $monitoringService)
    {
        $this->server = Server::where('server_id', $server_id)->first();
        
        if (!$this->server) {
            return;
        }

        try {
            // Get chart data from new monitoring system
            $chartData = $monitoringService->getChartData($this->server, 24);
            
            $this->labels = $chartData['labels'] ?? [];
            $this->dataset = [
                [
                    'label' => 'Load Average (1 min)',
                    'backgroundColor' => 'rgba(15,64,97,255)',
                    'borderColor' => 'rgba(15,64,97,255)',
                    'data' => $chartData['load'] ?? [],
                ],
            ];
        } catch (\Throwable $th) {
            // Fallback to empty data
            $this->labels = [];
            $this->dataset = [];
        }
    }

    public function render()
    {
        return view('livewire.stats.load');
    }
}

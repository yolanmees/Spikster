<?php

namespace App\Livewire\Stats;

use App\Models\Server;
use App\Services\MonitoringService;
use Livewire\Component;

class Mem extends Component
{
    private $server;

    public array $dataset = [];

    public array $labels = [];

    public $total;

    public $mem;

    public function mount($server_id, MonitoringService $monitoringService)
    {
        $this->server = Server::where('server_id', $server_id)->first();
        
        if (!$this->server) {
            return;
        }

        try {
            // Get chart data from new monitoring system
            $chartData = $monitoringService->getChartData($this->server, 24);
            
            // Get latest metrics for total memory
            $latest = $monitoringService->getLatestMetrics($this->server);
            $this->total = $latest ? $latest['memory']['total'] / 1024 / 1024 : 0;
            
            $this->labels = $chartData['labels'] ?? [];
            $this->dataset = [
                [
                    'label' => 'Memory Usage (%)',
                    'backgroundColor' => 'rgba(15,64,97,255)',
                    'borderColor' => 'rgba(15,64,97,255)',
                    'data' => $chartData['memory'] ?? [],
                ],
            ];
        } catch (\Throwable $th) {
            // Fallback to empty data
            $this->total = 0;
            $this->labels = [];
            $this->dataset = [];
        }
    }

    public function render()
    {
        return view('livewire.stats.mem');
    }
}

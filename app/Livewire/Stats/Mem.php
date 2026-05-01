<?php

namespace App\Livewire\Stats;

use App\Models\Server;
use App\Services\MonitoringService;
use Livewire\Component;

class Mem extends Component
{
    protected $server;

    protected $monitoringService;

    public array $dataset = [];

    public array $labels = [];

    public $total;

    public $mem;

    // Time range selector
    public string $timeRange = '24h';

    public array $timeRangeOptions = [
        '1h' => 'Last Hour',
        '6h' => 'Last 6 Hours',
        '12h' => 'Last 12 Hours',
        '24h' => 'Last 24 Hours',
        '7d' => 'Last 7 Days',
        '30d' => 'Last 30 Days',
    ];

    public function boot(MonitoringService $monitoringService): void
    {
        $this->monitoringService = $monitoringService;
    }

    public function mount($server_id): void
    {
        $this->server = Server::where('server_id', $server_id)->first();

        if (! $this->server) {
            return;
        }

        $this->loadChartData();
    }

    public function updatedTimeRange()
    {
        $this->loadChartData();
    }

    private function loadChartData()
    {
        try {
            // Convert time range to hours
            $hours = $this->getHoursFromRange($this->timeRange);

            // Get chart data from monitoring system
            $chartData = $this->monitoringService->getChartData($this->server, $hours);

            // Get latest metrics for total memory
            $latest = $this->monitoringService->getLatestMetrics($this->server);
            $this->total = $latest ? $latest['memory']['total'] / 1024 / 1024 : 0;

            $this->labels = $chartData['labels'] ?? [];
            $this->dataset = [
                [
                    'label' => 'Memory Usage (%)',
                    'backgroundColor' => 'rgba(15,64,97,255)',
                    'borderColor' => 'rgba(15,64,97,255)',
                    'data' => $chartData['memory'] ?? [],
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ];
        } catch (\Throwable $th) {
            // Fallback to empty data
            $this->total = 0;
            $this->labels = [];
            $this->dataset = [];
        }
    }

    private function getHoursFromRange(string $range): int
    {
        return match ($range) {
            '1h' => 1,
            '6h' => 6,
            '12h' => 12,
            '24h' => 24,
            '7d' => 24 * 7,
            '30d' => 24 * 30,
            default => 24,
        };
    }

    public function render()
    {
        return view('livewire.stats.mem');
    }
}

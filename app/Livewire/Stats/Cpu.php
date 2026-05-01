<?php

namespace App\Livewire\Stats;

use App\Models\Server;
use App\Services\MonitoringService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Cpu extends Component
{
    protected $server;

    protected $monitoringService;

    public array $dataset = [];

    public array $labels = [];

    public $cpu;

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
        // server_id parameter is actually the UUID string from the route
        // We need to find the server by this UUID to get its integer ID
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

            // Get chart data from monitoring system using the integer ID
            $chartData = $this->monitoringService->getChartData($this->server, $hours);

            Log::info('CPU Chart Data', [
                'server_id' => $this->server->id,
                'hours' => $hours,
                'labels_count' => count($chartData['labels'] ?? []),
                'cpu_count' => count($chartData['cpu'] ?? []),
                'labels' => $chartData['labels'] ?? [],
                'cpu_data' => $chartData['cpu'] ?? [],
            ]);

            $this->labels = $chartData['labels'] ?? [];
            $this->dataset = [
                [
                    'label' => 'CPU Usage (%)',
                    'backgroundColor' => 'rgba(15,64,97,255)',
                    'borderColor' => 'rgba(15,64,97,255)',
                    'data' => $chartData['cpu'] ?? [],
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ];
        } catch (\Throwable $th) {
            Log::error('Failed to load CPU chart data', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            // Fallback to empty data
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
        return view('livewire.stats.cpu');
    }
}

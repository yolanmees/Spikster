<?php

namespace App\Livewire\Stats;

use App\Models\Server;
use App\Services\MonitoringService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Chart extends Component
{
    protected $server;

    protected $monitoringService;

    public string $type;

    public string $title;

    public string $dataKey;

    public ?int $yMax = null;

    public array $dataset = [];

    public array $labels = [];

    public ?float $total = null;

    public string $timeRange = '24h';

    public array $timeRangeOptions = [
        '1h' => 'Last Hour',
        '6h' => 'Last 6 Hours',
        '12h' => 'Last 12 Hours',
        '24h' => 'Last 24 Hours',
        '7d' => 'Last 7 Days',
        '30d' => 'Last 30 Days',
    ];

    private const TYPE_CONFIG = [
        'cpu' => ['label' => 'CPU Usage (%)', 'dataKey' => 'cpu', 'yMax' => 100],
        'mem' => ['label' => 'Memory Usage (%)', 'dataKey' => 'memory', 'yMax' => 100, 'needsTotal' => true],
        'load' => ['label' => 'Load Average (1 min)', 'dataKey' => 'load', 'yMax' => null],
        'disk' => ['label' => 'Disk Usage (%)', 'dataKey' => 'disk', 'yMax' => 100, 'needsTotal' => true],
    ];

    public function boot(MonitoringService $monitoringService): void
    {
        $this->monitoringService = $monitoringService;
    }

    public function mount($server_id, $type): void
    {
        if (! isset(self::TYPE_CONFIG[$type])) {
            throw new \InvalidArgumentException("Invalid chart type: {$type}");
        }

        $config = self::TYPE_CONFIG[$type];
        $this->type = $type;
        $this->title = $config['label'];
        $this->dataKey = $config['dataKey'];
        $this->yMax = $config['yMax'] ?? null;

        $this->server = Server::where('server_id', $server_id)->first();

        if (! $this->server) {
            return;
        }

        $this->loadChartData($config);
    }

    public function updatedTimeRange(): void
    {
        $this->loadChartData(self::TYPE_CONFIG[$this->type]);
    }

    private function loadChartData(array $config): void
    {
        try {
            $hours = $this->getHoursFromRange($this->timeRange);
            $chartData = $this->monitoringService->getChartData($this->server, $hours);

            if (! empty($config['needsTotal'])) {
                $latest = $this->monitoringService->getLatestMetrics($this->server);
                $totalKey = $this->type === 'mem' ? 'memory' : 'disk';
                $this->total = $latest ? $latest[$totalKey]['total'] / 1024 / 1024 : 0;
            }

            $this->labels = $chartData['labels'] ?? [];
            $this->dataset = [
                [
                    'label' => $config['label'],
                    'backgroundColor' => 'rgba(15,64,97,255)',
                    'borderColor' => 'rgba(15,64,97,255)',
                    'data' => $chartData[$config['dataKey']] ?? [],
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ];
        } catch (\Throwable $th) {
            Log::error("Failed to load {$this->type} chart data", [
                'error' => $th->getMessage(),
            ]);

            $this->total = null;
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
        return view('livewire.stats.chart');
    }
}

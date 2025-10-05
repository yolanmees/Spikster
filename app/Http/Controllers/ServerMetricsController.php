<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\ServerMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ServerMetricsController extends Controller
{
    /**
     * Get chart data for a server
     */
    public function getChartData(Request $request, $serverId)
    {
        $server = Server::where('server_id', $serverId)->firstOrFail();
        $hours = (int) $request->get('hours', 1);

        Log::info('ServerMetricsController', [
            'server_uuid' => $serverId,
            'server_id' => $server->id,
            'hours' => $hours,
        ]);

        // Get metrics data with dynamic grouping
        $data = $this->getTimeSeriesData($server->id, $hours);

        Log::info('Metrics data result', [
            'labels_count' => count($data['labels']),
            'cpu_count' => count($data['cpu']),
            'first_label' => $data['labels'][0] ?? null,
            'last_label' => $data['labels'][count($data['labels']) - 1] ?? null,
        ]);

        return response()->json($data);
    }

    /**
     * Get time-series data with dynamic grouping
     */
    private function getTimeSeriesData(int $serverId, int $hours): array
    {
        Log::info('getTimeSeriesData called', [
            'server_id' => $serverId,
            'hours' => $hours,
            'time_range' => $hours <= 1 ? 'minute' : ($hours <= 6 ? '5min' : 'hourly'),
        ]);

        if ($hours <= 1) {
            // Last hour: show every data point
            $metrics = ServerMetric::where('server_id', $serverId)
                ->where('measured_at', '>=', now()->subHours($hours))
                ->orderBy('measured_at')
                ->get();

            Log::info('Query result for hourly', [
                'count' => $metrics->count(),
                'first' => $metrics->first() ? $metrics->first()->measured_at : null,
                'last' => $metrics->last() ? $metrics->last()->measured_at : null,
            ]);

            return [
                'labels' => $metrics->pluck('measured_at')->map(fn($t) => Carbon::parse($t)->format('H:i'))->values()->toArray(),
                'cpu' => $metrics->pluck('cpu_percent')->map(fn($v) => round($v, 2))->values()->toArray(),
                'memory' => $metrics->pluck('memory_percent')->map(fn($v) => round($v, 2))->values()->toArray(),
                'disk' => $metrics->pluck('disk_percent')->map(fn($v) => round($v, 2))->values()->toArray(),
                'load' => $metrics->pluck('load_1')->map(fn($v) => round($v, 2))->values()->toArray(),
            ];

        } elseif ($hours <= 6) {
            // 6 hours: group by 5 minutes
            $metrics = ServerMetric::where('server_id', $serverId)
                ->where('measured_at', '>=', now()->subHours($hours))
                ->select([
                    DB::raw('FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(measured_at)/300)*300) as time_bucket'),
                    DB::raw('AVG(cpu_percent) as avg_cpu'),
                    DB::raw('AVG(memory_percent) as avg_memory'),
                    DB::raw('AVG(disk_percent) as avg_disk'),
                    DB::raw('AVG(load_1) as avg_load'),
                ])
                ->groupBy('time_bucket')
                ->orderBy('time_bucket')
                ->get();

            return [
                'labels' => $metrics->pluck('time_bucket')->map(fn($t) => Carbon::parse($t)->format('H:i'))->values()->toArray(),
                'cpu' => $metrics->pluck('avg_cpu')->map(fn($v) => round($v, 2))->values()->toArray(),
                'memory' => $metrics->pluck('avg_memory')->map(fn($v) => round($v, 2))->values()->toArray(),
                'disk' => $metrics->pluck('avg_disk')->map(fn($v) => round($v, 2))->values()->toArray(),
                'load' => $metrics->pluck('avg_load')->map(fn($v) => round($v, 2))->values()->toArray(),
            ];

        } else {
            // 12+ hours: group by hour
            $metrics = ServerMetric::where('server_id', $serverId)
                ->where('measured_at', '>=', now()->subHours($hours))
                ->select([
                    DB::raw('DATE_FORMAT(measured_at, "%Y-%m-%d %H:00:00") as hour'),
                    DB::raw('AVG(cpu_percent) as avg_cpu'),
                    DB::raw('AVG(memory_percent) as avg_memory'),
                    DB::raw('AVG(disk_percent) as avg_disk'),
                    DB::raw('AVG(load_1) as avg_load'),
                ])
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $labelFormat = $hours > 48 ? 'M j H:i' : 'H:i';

            return [
                'labels' => $metrics->pluck('hour')->map(fn($t) => Carbon::parse($t)->format($labelFormat))->values()->toArray(),
                'cpu' => $metrics->pluck('avg_cpu')->map(fn($v) => round($v, 2))->values()->toArray(),
                'memory' => $metrics->pluck('avg_memory')->map(fn($v) => round($v, 2))->values()->toArray(),
                'disk' => $metrics->pluck('avg_disk')->map(fn($v) => round($v, 2))->values()->toArray(),
                'load' => $metrics->pluck('avg_load')->map(fn($v) => round($v, 2))->values()->toArray(),
            ];
        }
    }
}

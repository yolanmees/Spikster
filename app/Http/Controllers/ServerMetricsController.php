<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\ServerMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServerMetricsController extends Controller
{
    public function getChartData(Request $request, $serverId)
    {
        $server = Server::where('server_id', $serverId)->firstOrFail();
        $hours = (int) $request->get('hours', 1);

        $data = ServerMetric::getTimeSeriesData($server->id, $hours);

        return response()->json($data);
    }

    public function getBatchMetrics(Request $request)
    {
        $idsParam = $request->get('ids', '');
        $uuids = array_filter(explode(',', $idsParam));

        if (empty($uuids)) {
            return response()->json(['servers' => (object) [], 'averages' => ['cpu' => 0, 'memory' => 0, 'disk' => 0]]);
        }

        $serverIds = Server::whereIn('server_id', $uuids)->pluck('id', 'server_id');

        $metrics = ServerMetric::whereIn('server_id', $serverIds->values())
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('server_metrics')->groupBy('server_id');
            })
            ->get(['server_id', 'cpu_percent', 'memory_percent', 'disk_percent']);

        $result = [];
        $cpuSum = 0; $memSum = 0; $diskSum = 0; $count = $metrics->count();

        foreach ($serverIds as $uuid => $intId) {
            $m = $metrics->firstWhere('server_id', $intId);
            if ($m) {
                $result[$uuid] = [
                    'cpu' => (int) $m->cpu_percent,
                    'memory' => (int) $m->memory_percent,
                    'disk' => (int) $m->disk_percent,
                ];
                $cpuSum += $m->cpu_percent;
                $memSum += $m->memory_percent;
                $diskSum += $m->disk_percent;
            } else {
                $result[$uuid] = ['cpu' => 0, 'memory' => 0, 'disk' => 0];
            }
        }

        return response()->json([
            'servers' => $result,
            'averages' => [
                'cpu' => $count > 0 ? (int) round($cpuSum / $count) : 0,
                'memory' => $count > 0 ? (int) round($memSum / $count) : 0,
                'disk' => $count > 0 ? (int) round($diskSum / $count) : 0,
            ],
        ]);
    }
}

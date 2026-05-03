<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\ServerMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServerMetricsController extends Controller
{
    public function getChartData(Request $request, $serverId)
    {
        $server = Server::where('server_id', $serverId)->firstOrFail();
        $hours = (int) $request->get('hours', 1);

        Log::info('ServerMetricsController', [
            'server_uuid' => $serverId,
            'server_id' => $server->id,
            'hours' => $hours,
        ]);

        $data = ServerMetric::getTimeSeriesData($server->id, $hours);

        Log::info('Metrics data result', [
            'labels_count' => count($data['labels']),
            'cpu_count' => count($data['cpu']),
            'first_label' => $data['labels'][0] ?? null,
            'last_label' => $data['labels'][count($data['labels']) - 1] ?? null,
        ]);

        return response()->json($data);
    }
}

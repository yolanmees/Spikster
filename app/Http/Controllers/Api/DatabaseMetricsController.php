<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Database;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DatabaseMetricsController extends Controller
{
    /**
     * Return size and active connection count for all databases belonging to a site.
     */
    public function metrics(string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();
        $this->authorize('view', $site);

        $databases = Database::where('site_id', $site_id)->pluck('database_name');

        if ($databases->isEmpty()) {
            return response()->json([]);
        }

        // Size per database from information_schema
        $placeholders = implode(',', array_fill(0, $databases->count(), '?'));

        $sizes = DB::select(
            "SELECT table_schema AS db_name,
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb,
                    COUNT(table_name) AS table_count
             FROM information_schema.tables
             WHERE table_schema IN ({$placeholders})
             GROUP BY table_schema",
            $databases->values()->toArray()
        );

        $sizeMap = collect($sizes)->keyBy('db_name');

        // Active connections per database from SHOW PROCESSLIST
        $processList = DB::select('SHOW PROCESSLIST');
        $connMap = collect($processList)
            ->groupBy('db')
            ->map(fn ($rows) => $rows->count());

        $result = $databases->map(function (string $name) use ($sizeMap, $connMap) {
            $info = $sizeMap->get($name);

            return [
                'database' => $name,
                'size_mb' => $info ? (float) $info->size_mb : 0.0,
                'table_count' => $info ? (int) $info->table_count : 0,
                'connections' => (int) ($connMap->get($name) ?? 0),
            ];
        });

        return response()->json($result->values());
    }
}

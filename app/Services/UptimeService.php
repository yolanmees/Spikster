<?php

namespace App\Services;

use App\Models\Server;
use App\Models\ServerMetric;

class UptimeService
{
    public function getTimeline(Server $server, int $days = 7): array
    {
        $metrics = ServerMetric::forServer($server->id)
            ->lastDays($days)
            ->orderBy('measured_at')
            ->get(['measured_at', 'uptime_seconds']);

        if ($metrics->isEmpty()) {
            return ['points' => [], 'outages' => []];
        }

        $points = $metrics->map(fn ($m) => [
            'time' => $m->measured_at->toIso8601String(),
            'uptime_seconds' => $m->uptime_seconds,
            'uptime_formatted' => $m->formatted_uptime,
        ]);

        $outages = [];
        $previousUptime = null;
        foreach ($metrics as $metric) {
            if ($previousUptime !== null && $metric->uptime_seconds < $previousUptime - 10) {
                $outages[] = [
                    'detected_at' => $metric->measured_at->toIso8601String(),
                    'uptime_before' => $previousUptime,
                    'uptime_after' => $metric->uptime_seconds,
                ];
            }
            $previousUptime = $metric->uptime_seconds;
        }

        $uptimePercent = 100;
        if ($metrics->isNotEmpty()) {
            $first = $metrics->first()->uptime_seconds;
            $last = $metrics->last()->uptime_seconds;
            $expectedIncrease = $metrics->count() * 60;
            $actualIncrease = $last - $first;
            $uptimePercent = $expectedIncrease > 0
                ? round(($actualIncrease / $expectedIncrease) * 100, 2)
                : 100;
        }

        return [
            'points' => $points,
            'outages' => $outages,
            'uptime_percent' => min(100, max(0, $uptimePercent)),
            'total_outages' => count($outages),
            'period_days' => $days,
        ];
    }
}

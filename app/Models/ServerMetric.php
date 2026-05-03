<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ServerMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'cpu_percent',
        'cpu_cores',
        'memory_total',
        'memory_used',
        'memory_free',
        'memory_available',
        'memory_percent',
        'memory_cached',
        'memory_buffers',
        'disk_total',
        'disk_used',
        'disk_free',
        'disk_percent',
        'load_1',
        'load_5',
        'load_15',
        'network_bytes_sent',
        'network_bytes_recv',
        'network_packets_sent',
        'network_packets_recv',
        'uptime_seconds',
        'measured_at',
    ];

    protected $casts = [
        'cpu_percent' => 'decimal:2',
        'cpu_cores' => 'integer',
        'memory_total' => 'integer',
        'memory_used' => 'integer',
        'memory_free' => 'integer',
        'memory_available' => 'integer',
        'memory_percent' => 'decimal:2',
        'memory_cached' => 'integer',
        'memory_buffers' => 'integer',
        'disk_total' => 'integer',
        'disk_used' => 'integer',
        'disk_free' => 'integer',
        'disk_percent' => 'decimal:2',
        'load_1' => 'decimal:2',
        'load_5' => 'decimal:2',
        'load_15' => 'decimal:2',
        'network_bytes_sent' => 'integer',
        'network_bytes_recv' => 'integer',
        'network_packets_sent' => 'integer',
        'network_packets_recv' => 'integer',
        'uptime_seconds' => 'integer',
        'measured_at' => 'datetime',
    ];

    /**
     * Get the server that owns this metric.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id', 'server_id');
    }

    /**
     * Scope to get metrics for a specific server.
     */
    public function scopeForServer($query, string $serverId)
    {
        return $query->where('server_id', $serverId);
    }

    /**
     * Scope to get the latest metric for a server.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('measured_at', 'desc');
    }

    /**
     * Scope to get metrics within a time range.
     */
    public function scopeInTimeRange($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('measured_at', [$start, $end]);
    }

    /**
     * Scope to get metrics from the last N hours.
     */
    public function scopeLastHours($query, int $hours = 24)
    {
        return $query->where('measured_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope to get metrics from the last N days.
     */
    public function scopeLastDays($query, int $days = 7)
    {
        return $query->where('measured_at', '>=', now()->subDays($days));
    }

    /**
     * Get the latest metric for a specific server.
     */
    public static function getLatestForServer(string $serverId): ?self
    {
        return static::forServer($serverId)
            ->latest()
            ->first();
    }

    /**
     * Get aggregated metrics for a server over a time period.
     */
    public static function getAggregatedMetrics(string $serverId, int $hours = 24): array
    {
        $metrics = static::forServer($serverId)
            ->lastHours($hours)
            ->get();

        if ($metrics->isEmpty()) {
            return [];
        }

        return [
            'cpu' => [
                'avg' => round($metrics->avg('cpu_percent'), 2),
                'max' => round($metrics->max('cpu_percent'), 2),
                'min' => round($metrics->min('cpu_percent'), 2),
            ],
            'memory' => [
                'avg' => round($metrics->avg('memory_percent'), 2),
                'max' => round($metrics->max('memory_percent'), 2),
                'min' => round($metrics->min('memory_percent'), 2),
            ],
            'disk' => [
                'avg' => round($metrics->avg('disk_percent'), 2),
                'max' => round($metrics->max('disk_percent'), 2),
                'min' => round($metrics->min('disk_percent'), 2),
            ],
            'load' => [
                'avg_1' => round($metrics->avg('load_1'), 2),
                'avg_5' => round($metrics->avg('load_5'), 2),
                'avg_15' => round($metrics->avg('load_15'), 2),
                'max_1' => round($metrics->max('load_1'), 2),
                'max_5' => round($metrics->max('load_5'), 2),
                'max_15' => round($metrics->max('load_15'), 2),
            ],
        ];
    }

    /**
     * Delete old metrics beyond retention period.
     */
    public static function cleanupOldMetrics(int $daysToKeep = 30): int
    {
        return static::where('created_at', '<', now()->subDays($daysToKeep))->delete();
    }

    /**
     * Delete metrics older than N days for a specific server.
     */
    public static function cleanupForServer(string $serverId, int $daysToKeep = 30): int
    {
        return static::forServer($serverId)
            ->where('created_at', '<', now()->subDays($daysToKeep))
            ->delete();
    }

    /**
     * Get time-series data for charting with dynamic grouping based on time range.
     *
     * Grouping strategy:
     * - 1 hour: Show every data point (per minute) - ~60 points
     * - 6 hours: Group by 5 minutes - ~72 points
     * - 12+ hours: Group by hour - varies by range
     */
    public static function getTimeSeriesData(string $serverId, int $hours = 24): array
    {
        // Determine grouping strategy based on time range
        if ($hours <= 1) {
            // Last hour: show all data points (no grouping)
            $metrics = static::forServer($serverId)
                ->lastHours($hours)
                ->select([
                    'measured_at',
                    'cpu_percent',
                    'memory_percent',
                    'disk_percent',
                    'load_1',
                ])
                ->orderBy('measured_at')
                ->get();

            return [
                'labels' => $metrics->pluck('measured_at')->map(fn ($t) => Carbon::parse($t)->format('H:i'))->toArray(),
                'cpu' => $metrics->pluck('cpu_percent')->map(fn ($v) => round($v, 2))->toArray(),
                'memory' => $metrics->pluck('memory_percent')->map(fn ($v) => round($v, 2))->toArray(),
                'disk' => $metrics->pluck('disk_percent')->map(fn ($v) => round($v, 2))->toArray(),
                'load' => $metrics->pluck('load_1')->map(fn ($v) => round($v, 2))->toArray(),
            ];

        } elseif ($hours <= 6) {
            // Last 6 hours: group by 5 minutes
            $metrics = static::forServer($serverId)
                ->lastHours($hours)
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
                'labels' => $metrics->pluck('time_bucket')->map(fn ($h) => Carbon::parse($h)->format('H:i'))->toArray(),
                'cpu' => $metrics->pluck('avg_cpu')->map(fn ($v) => round($v, 2))->toArray(),
                'memory' => $metrics->pluck('avg_memory')->map(fn ($v) => round($v, 2))->toArray(),
                'disk' => $metrics->pluck('avg_disk')->map(fn ($v) => round($v, 2))->toArray(),
                'load' => $metrics->pluck('avg_load')->map(fn ($v) => round($v, 2))->toArray(),
            ];

        } else {
            // 12+ hours: group by hour
            $metrics = static::forServer($serverId)
                ->lastHours($hours)
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

            // For longer periods, show date + hour
            $labelFormat = $hours > 48 ? 'M j H:i' : 'H:i';

            return [
                'labels' => $metrics->pluck('hour')->map(fn ($h) => Carbon::parse($h)->format($labelFormat))->toArray(),
                'cpu' => $metrics->pluck('avg_cpu')->map(fn ($v) => round($v, 2))->toArray(),
                'memory' => $metrics->pluck('avg_memory')->map(fn ($v) => round($v, 2))->toArray(),
                'disk' => $metrics->pluck('avg_disk')->map(fn ($v) => round($v, 2))->toArray(),
                'load' => $metrics->pluck('avg_load')->map(fn ($v) => round($v, 2))->toArray(),
            ];
        }
    }

    /**
     * Format bytes to human-readable format.
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }

    /**
     * Get formatted memory values.
     */
    public function getFormattedMemoryAttribute(): array
    {
        return [
            'total' => $this->formatBytes($this->memory_total),
            'used' => $this->formatBytes($this->memory_used),
            'free' => $this->formatBytes($this->memory_free),
            'available' => $this->memory_available ? $this->formatBytes($this->memory_available) : null,
            'percent' => $this->memory_percent.'%',
        ];
    }

    /**
     * Get formatted disk values.
     */
    public function getFormattedDiskAttribute(): array
    {
        return [
            'total' => $this->formatBytes($this->disk_total),
            'used' => $this->formatBytes($this->disk_used),
            'free' => $this->formatBytes($this->disk_free),
            'percent' => $this->disk_percent.'%',
        ];
    }

    /**
     * Get formatted uptime.
     */
    public function getFormattedUptimeAttribute(): string
    {
        $seconds = $this->uptime_seconds;
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = "{$days}d";
        }
        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }
        if ($minutes > 0 || empty($parts)) {
            $parts[] = "{$minutes}m";
        }

        return implode(' ', $parts);
    }
}

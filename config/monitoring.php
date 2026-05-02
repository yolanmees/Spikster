<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Monitoring Agent Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the spikster-agent monitoring system.
    |
    */

    /**
     * Agent port number
     *
     * The port where spikster-agent listens on each server.
     */
    'agent_port' => env('MONITORING_AGENT_PORT', 9273),

    /**
     * Metrics retention period
     *
     * How long to keep metrics in the database (in days).
     * Older metrics will be automatically deleted.
     * Default: 30 days
     */
    'metrics_retention_days' => env('MONITORING_RETENTION_DAYS', 30),

    /**
     * Enable fallback to cached metrics
     *
     * If true, show last known metrics when agent is unreachable.
     * If false, show error when agent is down.
     */
    'use_cached_metrics' => env('MONITORING_USE_CACHED', true),

    /**
     * Alert thresholds
     *
     * Thresholds for triggering alerts (percentage).
     */
    'thresholds' => [
        'cpu' => [
            'warning' => env('THRESHOLD_CPU_WARNING', 70),
            'critical' => env('THRESHOLD_CPU_CRITICAL', 90),
        ],
        'memory' => [
            'warning' => env('THRESHOLD_MEMORY_WARNING', 80),
            'critical' => env('THRESHOLD_MEMORY_CRITICAL', 95),
        ],
        'disk' => [
            'warning' => env('THRESHOLD_DISK_WARNING', 80),
            'critical' => env('THRESHOLD_DISK_CRITICAL', 90),
        ],
    ],

];

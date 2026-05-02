@extends('layouts.app')

@section('title', 'Diagnostics')

@section('content')
<div class="space-y-6">
    <x-page-header title="Diagnostics" subtitle="System health and configuration information" />

    {{-- System Overview --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="PHP Version" :value="$info['php_version']" icon="code" color="blue" />
        <x-stat-card label="Laravel" :value="$info['laravel_version']" icon="cog" color="purple" />
        <x-stat-card label="Environment" :value="ucfirst($info['environment'])" icon="globe" color="{{ $info['environment'] === 'production' ? 'green' : 'amber' }}" />
        <x-stat-card label="Database" :value="$info['db_driver'] . ' (' . $info['db_size'] . ')'" icon="database" color="zinc" />
    </div>

    {{-- Services Status --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Services</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div class="flex items-center gap-3 p-3 rounded-lg border {{ $info['daemon_running'] ? 'border-green-200 dark:border-green-700/50 bg-green-50 dark:bg-green-900/20' : 'border-red-200 dark:border-red-700/50 bg-red-50 dark:bg-red-900/20' }}">
                <span class="w-3 h-3 rounded-full {{ $info['daemon_running'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
                <div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">Panel Daemon</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $info['daemon_running'] ? 'Running' : ($info['daemon_error'] ?? 'Not responding') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-lg border {{ $info['failed_jobs'] === 0 ? 'border-green-200 dark:border-green-700/50 bg-green-50 dark:bg-green-900/20' : 'border-amber-200 dark:border-amber-700/50 bg-amber-50 dark:bg-amber-900/20' }}">
                <span class="w-3 h-3 rounded-full {{ $info['failed_jobs'] === 0 ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                <div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">Queue</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $info['pending_jobs'] }} pending, {{ $info['failed_jobs'] }} failed</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-lg border {{ !$info['debug_mode'] ? 'border-green-200 dark:border-green-700/50 bg-green-50 dark:bg-green-900/20' : 'border-red-200 dark:border-red-700/50 bg-red-50 dark:bg-red-900/20' }}">
                <span class="w-3 h-3 rounded-full {{ !$info['debug_mode'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
                <div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">Debug Mode</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $info['debug_mode'] ? 'ON' : 'OFF' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Resource Usage --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Resource Usage</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">Disk</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white">{{ $info['disk_free'] }} free / {{ $info['disk_total'] }} total</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">Memory Limit</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white">{{ $info['memory_limit'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">Max Upload</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white">{{ $info['max_upload'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">Max Execution Time</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white">{{ $info['max_execution_time'] }}s</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">Log Size</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white">{{ $info['log_size'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">Timezone</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white">{{ $info['timezone'] }}</dd>
                </div>
            </dl>
        </div>

        {{-- Application Stats --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Application Stats</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">Servers</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white">{{ $info['servers_active'] }} active / {{ $info['servers_total'] }} total</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">Sites</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white">{{ $info['sites_total'] }} ({{ $info['sites_panel'] }} panel)</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">Users</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white">{{ $info['users_total'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">Deployments</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white">{{ $info['deployments_total'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">App URL</dt>
                    <dd class="text-sm text-zinc-900 dark:text-white truncate max-w-[200px]">{{ $info['app_url'] }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Configuration --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Configuration</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Queue Driver</p>
                <p class="text-sm text-zinc-900 dark:text-white font-mono">{{ $info['queue_driver'] }}</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Cache Driver</p>
                <p class="text-sm text-zinc-900 dark:text-white font-mono">{{ $info['cache_driver'] }}</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Session Driver</p>
                <p class="text-sm text-zinc-900 dark:text-white font-mono">{{ $info['session_driver'] }}</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Log Channel</p>
                <p class="text-sm text-zinc-900 dark:text-white font-mono">{{ $info['log_channel'] }}</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Database Driver</p>
                <p class="text-sm text-zinc-900 dark:text-white font-mono">{{ $info['db_driver'] }}</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">PHP Version</p>
                <p class="text-sm text-zinc-900 dark:text-white font-mono">{{ $info['php_version'] }}</p>
            </div>
        </div>
    </div>

    {{-- PHP Extensions --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">PHP Extensions</h2>
        <div class="flex flex-wrap gap-1.5">
            @foreach (explode(', ', $info['php_extensions']) as $ext)
                <span class="px-2 py-1 text-xs bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded font-mono">{{ $ext }}</span>
            @endforeach
        </div>
    </div>
</div>
@endsection

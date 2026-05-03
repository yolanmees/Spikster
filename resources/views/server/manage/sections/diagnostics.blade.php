{{-- Per-server diagnostics --}}
@php
    $server = \App\Models\Server::where('server_id', $server_id)->first();
@endphp

@if(!$server)
    <div class="text-red-500 text-sm">Server not found.</div>
@else
<div class="space-y-4" x-data="serverDiagnostics('{{ $server_id }}')" x-init="init()">
    {{-- Quick status cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Daemon</p>
            <p class="text-sm font-semibold mt-1" x-text="status.daemon" x-bind:class="status.daemonClass"></p>
        </div>
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
            <p class="text-xs text-zinc-500 dark:text-zinc-400">CPU</p>
            <p class="text-sm font-semibold text-zinc-950 dark:text-white mt-1" x-text="status.cpu"></p>
        </div>
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Memory</p>
            <p class="text-sm font-semibold text-zinc-950 dark:text-white mt-1" x-text="status.memory"></p>
        </div>
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Disk</p>
            <p class="text-sm font-semibold text-zinc-950 dark:text-white mt-1" x-text="status.disk"></p>
        </div>
    </div>

    {{-- Server info table --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-800">
            <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">Server Information</h3>
        </div>
        <div class="divide-y divide-zinc-200 dark:divide-zinc-800 text-sm">
            <div class="px-4 py-2.5 flex justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">Name</span>
                <span class="text-zinc-950 dark:text-white font-medium">{{ $server->name }}</span>
            </div>
            <div class="px-4 py-2.5 flex justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">IP Address</span>
                <span class="text-zinc-950 dark:text-white font-medium">{{ $server->ip }}</span>
            </div>
            <div class="px-4 py-2.5 flex justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">Provider</span>
                <span class="text-zinc-950 dark:text-white font-medium">{{ $server->provider ?? 'Manual' }}</span>
            </div>
            <div class="px-4 py-2.5 flex justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">Location</span>
                <span class="text-zinc-950 dark:text-white font-medium">{{ $server->location ?? '—' }}</span>
            </div>
            <div class="px-4 py-2.5 flex justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">PHP CLI</span>
                <span class="text-zinc-950 dark:text-white font-medium">{{ $server->php ?? '—' }}</span>
            </div>
            <div class="px-4 py-2.5 flex justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">Status</span>
                <span class="font-medium {{ $server->status === 1 ? 'text-green-600' : 'text-amber-600' }}">
                    {{ $server->status === 1 ? 'Active' : 'Installing' }}
                </span>
            </div>
            <div class="px-4 py-2.5 flex justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">Sites</span>
                <span class="text-zinc-950 dark:text-white font-medium">{{ $server->sites()->count() }}</span>
            </div>
            <div class="px-4 py-2.5 flex justify-between">
                <span class="text-zinc-500 dark:text-zinc-400">Created</span>
                <span class="text-zinc-950 dark:text-white font-medium">{{ $server->created_at->format('M d, Y H:i') }}</span>
            </div>
        </div>
    </div>

    {{-- Daemon health check --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-800">
            <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">Daemon Health</h3>
        </div>
        <div class="px-4 py-3">
            <div x-show="loading" class="text-sm text-zinc-500">Checking daemon connection...</div>
            <div x-show="!loading" class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Connection:</span>
                    <span class="text-sm font-medium" x-text="health.connection" x-bind:class="health.connectionClass"></span>
                </div>
                <div x-show="health.message" class="text-xs text-zinc-500 dark:text-zinc-400" x-text="health.message"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('serverDiagnostics', (serverId) => ({
        loading: true,
        status: {
            daemon: 'Checking...',
            daemonClass: 'text-zinc-400',
            cpu: '—',
            memory: '—',
            disk: '—',
        },
        health: {
            connection: 'Checking...',
            connectionClass: 'text-zinc-400',
            message: '',
        },

        async init() {
            const token = localStorage.getItem('sanctum_token') || '';
            const headers = { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' };

            // Check daemon health
            try {
                const r = await fetch(`/api/servers/${serverId}/healthy`, { headers, signal: AbortSignal.timeout(10000) });
                const data = await r.json();
                if (data.status === 'online' || data.success) {
                    this.health.connection = 'Connected';
                    this.health.connectionClass = 'text-green-600';
                    this.status.daemon = 'Online';
                    this.status.daemonClass = 'text-green-600';
                } else {
                    this.health.connection = 'Issue detected';
                    this.health.connectionClass = 'text-amber-600';
                    this.health.message = data.message || 'Daemon reported an issue';
                    this.status.daemon = 'Degraded';
                    this.status.daemonClass = 'text-amber-600';
                }
            } catch {
                this.health.connection = 'Unreachable';
                this.health.connectionClass = 'text-red-600';
                this.health.message = 'Could not reach the server daemon';
                this.status.daemon = 'Offline';
                this.status.daemonClass = 'text-red-600';
            }

            // Get latest metrics
            try {
                const r = await fetch(`/api/servers/${serverId}/metrics?hours=1`, { headers });
                const data = await r.json();
                if (data && data.labels && data.labels.length > 0) {
                    const i = data.labels.length - 1;
                    if (data.cpu && data.cpu[i] !== undefined) this.status.cpu = data.cpu[i].toFixed(1) + '%';
                    if (data.memory && data.memory[i] !== undefined) this.status.memory = data.memory[i].toFixed(1) + '%';
                    if (data.disk && data.disk[i] !== undefined) this.status.disk = data.disk[i].toFixed(1) + '%';
                } else {
                    this.status.cpu = 'No data';
                    this.status.memory = 'No data';
                    this.status.disk = 'No data';
                }
            } catch {
                this.status.cpu = 'Error';
                this.status.memory = 'Error';
                this.status.disk = 'Error';
            }

            this.loading = false;
        }
    }));
});
</script>
@endif

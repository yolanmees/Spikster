@extends('layouts.app')

@section('title', __('spikster.titles.dashboard'))
@section('topbar-title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Dashboard">
            <x-slot name="actions">
                <x-button variant="light" size="sm" onclick="window.location.reload()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </x-button>
            </x-slot>
        </x-page-header>

        {{-- Server health overview --}}
        <div id="server-grid" class="space-y-3">
            <div class="flex items-center justify-center py-12">
                <x-spinner size="lg" color="blue" />
                <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">Loading servers…</span>
            </div>
        </div>

        {{-- Top Sites --}}
        <div>
            <x-page-header title="Top Sites" size="section" />
            @livewire('dashboard.top-sites')
        </div>
    </div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('server-grid');
    const spinner = '<svg class="animate-spin h-4 w-4 inline-block text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

    function serverCard(s) {
        return `<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 px-6 py-4 shadow-sm hover:shadow-md transition-all duration-200" data-server="${s.server_id}">
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 items-center">
                <div class="col-span-2 md:col-span-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 dark:from-blue-500/10 dark:to-purple-500/10 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">${s.name}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">${s.ip ?? ''}</div>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block text-center">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Sites</div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">${s.sites}</div>
                </div>
                <div class="hidden lg:block text-center">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">CPU</div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white" id="cpu-${s.server_id}">${spinner}</div>
                </div>
                <div class="hidden lg:block text-center">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">RAM</div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white" id="ram-${s.server_id}">${spinner}</div>
                </div>
                <div class="hidden lg:block text-center">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Disk</div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white" id="hdd-${s.server_id}">${spinner}</div>
                </div>
                <div class="flex justify-end">
                    <a href="/servers/${s.server_id}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                        Manage
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>`;
    }

    function emptyState() {
        return `<div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No servers yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Add your first server to get started.</p>
            <a href="/servers" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Server
            </a>
        </div>`;
    }

    async function loadMetrics(serverId) {
        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const res = await fetch(`/api/servers/${serverId}/healthy`, {
                headers: { 'Authorization': 'Bearer ' + localStorage.access_token, 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();
            const fmt = v => `<span class="${v > 80 ? 'text-red-500' : v > 60 ? 'text-yellow-500' : 'text-green-500'}">${v}%</span>`;
            document.getElementById('cpu-' + serverId)?.innerHTML !== undefined && (document.getElementById('cpu-' + serverId).innerHTML = fmt(data.cpu ?? 0));
            document.getElementById('ram-' + serverId)?.innerHTML !== undefined && (document.getElementById('ram-' + serverId).innerHTML = fmt(data.ram ?? 0));
            document.getElementById('hdd-' + serverId)?.innerHTML !== undefined && (document.getElementById('hdd-' + serverId).innerHTML = fmt(data.hdd ?? 0));
        } catch(e) {}
    }

    // Load servers
    fetch('/api/servers', {
        headers: { 'Authorization': 'Bearer ' + localStorage.access_token, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(servers => {
        const active = servers.filter(s => s.status > 0);
        if (!active.length) {
            grid.innerHTML = emptyState();
            return;
        }
        grid.innerHTML = active.map(serverCard).join('');
        active.forEach(s => loadMetrics(s.server_id));

        // Refresh metrics every 30s
        setInterval(() => active.forEach(s => loadMetrics(s.server_id)), 30000);
    })
    .catch(() => {
        grid.innerHTML = `<x-alert type="error">Failed to load servers.</x-alert>`;
    });
});
</script>
@endsection

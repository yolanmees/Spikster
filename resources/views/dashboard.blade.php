@extends('layouts.app')

@section('title', __('spikster.titles.dashboard'))
@section('topbar-title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Stat cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Servers</p>
                    <p class="mt-2 text-2xl font-semibold" id="stat-servers">—</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                    <i data-lucide="server" class="h-5 w-5"></i>
                </span>
            </div>
        </article>
        <article class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Sites</p>
                    <p class="mt-2 text-2xl font-semibold" id="stat-sites">—</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-700/15 text-purple-700 dark:text-purple-300">
                    <i data-lucide="globe" class="h-5 w-5"></i>
                </span>
            </div>
        </article>
        <article class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Avg. CPU</p>
                    <p class="mt-2 text-2xl font-semibold" id="stat-cpu">—</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                    <i data-lucide="cpu" class="h-5 w-5"></i>
                </span>
            </div>
        </article>
        <article class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Avg. RAM</p>
                    <p class="mt-2 text-2xl font-semibold" id="stat-ram">—</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                    <i data-lucide="memory-stick" class="h-5 w-5"></i>
                </span>
            </div>
        </article>
    </div>

    {{-- Server health table --}}
    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 border-b border-zinc-200 p-5 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold">Server overview</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Status and resource usage per server.</p>
            </div>
            <button onclick="window.location.reload()"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-medium shadow-sm hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:bg-zinc-800 transition-colors">
                <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                Refresh
            </button>
        </div>
        <div id="server-grid" class="overflow-x-auto">
            <div class="flex items-center justify-center gap-3 py-14 text-sm text-zinc-500 dark:text-zinc-400">
                <i data-lucide="loader-circle" class="h-5 w-5 animate-spin"></i>
                Loading servers…
            </div>
        </div>
    </section>

    {{-- Bottom row: Top Sites + Activity --}}
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">

        {{-- Top Sites --}}
        @livewire('dashboard.top-sites')

        {{-- Quick links --}}
        <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-base font-semibold">Quick links</h2>
            <div class="mt-5 space-y-2">
                <a href="{{ route('server.list') }}"
                    class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 text-sm hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800 transition-colors">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                        <i data-lucide="server-cog" class="h-4 w-4"></i>
                    </span>
                    <span class="font-medium">Add server</span>
                    <i data-lucide="chevron-right" class="ml-auto h-4 w-4 text-zinc-400"></i>
                </a>
                <a href="{{ route('site.list') }}"
                    class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 text-sm hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800 transition-colors">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-700/15 text-purple-700 dark:text-purple-300">
                        <i data-lucide="globe" class="h-4 w-4"></i>
                    </span>
                    <span class="font-medium">Sites</span>
                    <i data-lucide="chevron-right" class="ml-auto h-4 w-4 text-zinc-400"></i>
                </a>
                <a href="{{ route('domain.list') }}"
                    class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 text-sm hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800 transition-colors">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                        <i data-lucide="at-sign" class="h-4 w-4"></i>
                    </span>
                    <span class="font-medium">Domains</span>
                    <i data-lucide="chevron-right" class="ml-auto h-4 w-4 text-zinc-400"></i>
                </a>
                <a href="{{ route('settings.general') }}"
                    class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 text-sm hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800 transition-colors">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                        <i data-lucide="settings" class="h-4 w-4"></i>
                    </span>
                    <span class="font-medium">Settings</span>
                    <i data-lucide="chevron-right" class="ml-auto h-4 w-4 text-zinc-400"></i>
                </a>
            </div>
        </section>
    </div>

</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('server-grid');

    const spinner = `<svg class="animate-spin h-4 w-4 inline-block text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>`;

    function metricColor(v) {
        if (v > 80) return 'text-red-500 dark:text-red-400';
        if (v > 60) return 'text-yellow-500 dark:text-yellow-400';
        return 'text-green-600 dark:text-green-400';
    }

    function serverRow(s) {
        const initials = s.name.slice(0, 2).toUpperCase();
        return `<tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors" data-server="${s.server_id}">
            <td class="whitespace-nowrap px-5 py-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 text-xs font-bold shrink-0">${initials}</span>
                    <div>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">${s.name}</p>
                        <p class="font-mono text-xs text-zinc-500 dark:text-zinc-400">${s.ip ?? ''}</p>
                    </div>
                </div>
            </td>
            <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">${s.sites ?? 0}</td>
            <td class="whitespace-nowrap px-5 py-4 font-medium" id="cpu-${s.server_id}">${spinner}</td>
            <td class="whitespace-nowrap px-5 py-4 font-medium" id="ram-${s.server_id}">${spinner}</td>
            <td class="whitespace-nowrap px-5 py-4 font-medium" id="hdd-${s.server_id}">${spinner}</td>
            <td class="whitespace-nowrap px-5 py-4 text-right">
                <a href="/servers/${s.server_id}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-xs font-medium hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:bg-zinc-800 transition-colors">
                    Manage
                </a>
            </td>
        </tr>`;
    }

    function serverTable(servers) {
        return `<table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
            <thead class="bg-zinc-50 text-left text-xs font-medium uppercase text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400">
                <tr>
                    <th class="px-5 py-3">Server</th>
                    <th class="px-5 py-3">Sites</th>
                    <th class="px-5 py-3">CPU</th>
                    <th class="px-5 py-3">RAM</th>
                    <th class="px-5 py-3">Disk</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                ${servers.map(serverRow).join('')}
            </tbody>
        </table>`;
    }

    function emptyState() {
        return `<div class="flex flex-col items-center justify-center gap-4 py-16 text-center px-6">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                </svg>
            </span>
            <div>
                <p class="text-base font-semibold text-zinc-900 dark:text-zinc-100">No servers</p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Add your first server to get started.</p>
            </div>
            <a href="/servers" class="inline-flex items-center gap-2 rounded-lg bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Server
            </a>
        </div>`;
    }

    async function refreshMetrics(activeServers) {
        const ids = activeServers.map(s => s.server_id).join(',');
        try {
            const res = await fetch('/api/servers/metrics/batch?ids=' + encodeURIComponent(ids), {
                headers: { 'Authorization': 'Bearer ' + localStorage.sanctum_token, 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();
            const fmt = v => `<span class="${metricColor(v)}">${v}%</span>`;

            for (const s of activeServers) {
                const m = data.servers[s.server_id];
                const cpu = m ? m.cpu : 0;
                const mem = m ? m.memory : 0;
                const dsk = m ? m.disk : 0;
                const cpuEl = document.getElementById('cpu-' + s.server_id);
                const ramEl = document.getElementById('ram-' + s.server_id);
                const hddEl = document.getElementById('hdd-' + s.server_id);
                if (cpuEl) cpuEl.innerHTML = fmt(cpu);
                if (ramEl) ramEl.innerHTML = fmt(mem);
                if (hddEl) hddEl.innerHTML = fmt(dsk);
            }

            const statCpu = document.getElementById('stat-cpu');
            const statRam = document.getElementById('stat-ram');
            if (statCpu && data.averages) statCpu.textContent = data.averages.cpu + '%';
            if (statRam && data.averages) statRam.textContent = data.averages.memory + '%';
        } catch(e) {}
    }

    fetch('/api/servers', {
        headers: { 'Authorization': 'Bearer ' + localStorage.sanctum_token, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(servers => {
        const active = servers.filter(s => s.status > 0);

        const statServers = document.getElementById('stat-servers');
        const statSites = document.getElementById('stat-sites');
        if (statServers) statServers.textContent = active.length;
        if (statSites) statSites.textContent = active.reduce((t, s) => t + (s.sites ?? 0), 0);

        if (!active.length) { grid.innerHTML = emptyState(); return; }

        grid.innerHTML = serverTable(active);
        refreshMetrics(active);
        setInterval(() => refreshMetrics(active), 30000);
    })
    .catch(() => { grid.innerHTML = emptyState(); });
});
</script>
@endsection

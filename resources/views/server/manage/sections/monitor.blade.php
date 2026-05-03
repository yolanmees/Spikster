{{-- Real-time monitoring charts --}}
<div class="space-y-4">
    {{-- Stat summary row (design inspired by new_design/index.html metric cards) --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        @foreach ([
            ['label' => 'CPU Usage',    'id' => 'stat-cpu',  'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'bg' => 'bg-purple-50 dark:bg-purple-900/20', 'color' => 'text-purple-700 dark:text-purple-300'],
            ['label' => 'Memory',       'id' => 'stat-mem',  'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',           'bg' => 'bg-zinc-100 dark:bg-zinc-800/50',   'color' => 'text-zinc-700 dark:text-zinc-300'],
            ['label' => 'Load Avg',     'id' => 'stat-load', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',                                                                                                   'bg' => 'bg-amber-50 dark:bg-amber-900/20', 'color' => 'text-amber-700 dark:text-amber-300'],
            ['label' => 'Disk',         'id' => 'stat-disk', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',                                                            'bg' => 'bg-green-50 dark:bg-green-900/20', 'color' => 'text-green-700 dark:text-green-300'],
        ] as $stat)
        <div class="flex items-center gap-3 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-3">
            <div class="w-9 h-9 rounded-lg {{ $stat['bg'] }} flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 {{ $stat['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</p>
                <p class="text-sm font-semibold text-zinc-950 dark:text-white" id="{{ $stat['id'] }}">—</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Livewire chart components --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
            <p class="text-sm font-semibold text-zinc-950 dark:text-white mb-3">CPU</p>
            @livewire('stats.cpu', ['server_id' => $server_id])
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
            <p class="text-sm font-semibold text-zinc-950 dark:text-white mb-3">Memory</p>
            @livewire('stats.mem', ['server_id' => $server_id])
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
            <p class="text-sm font-semibold text-zinc-950 dark:text-white mb-3">Load Average</p>
            @livewire('stats.load', ['server_id' => $server_id])
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
            <p class="text-sm font-semibold text-zinc-950 dark:text-white mb-3">Disk Usage</p>
            @livewire('stats.disk', ['server_id' => $server_id])
        </div>
    </div>
</div>

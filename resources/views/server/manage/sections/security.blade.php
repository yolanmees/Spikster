<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    {{-- Malware Scanner --}}
    <x-card size="md" dark="false" class="xl:col-span-2">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016zM12 9v2m0 4h.01" />
                </svg>
                Malware Scanner
            </div>
        </x-slot>
        <div class="space-y-4" x-data="malwareScan('{{ $server_id }}')">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Scan server for crypto miners, suspicious binaries, infected cron jobs, and startup file tampering.</p>
            <div class="flex items-center gap-3">
                <button type="button" @click="scan()" :disabled="loading"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!loading">Run Malware Scan</span>
                    <span x-show="loading">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Scanning...
                    </span>
                </button>
            </div>
            <div x-show="result" class="p-4 rounded-xl border" :class="resultClass">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-sm font-semibold" x-text="resultLabel"></span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="badgeClass" x-text="result.status"></span>
                </div>
                <div class="grid grid-cols-4 gap-3 text-xs text-zinc-600 dark:text-zinc-400">
                    <div>Processes: <span class="font-semibold text-zinc-950 dark:text-white" x-text="result.processes"></span></div>
                    <div>Binaries: <span class="font-semibold text-zinc-950 dark:text-white" x-text="result.binaries"></span></div>
                    <div>Cron Jobs: <span class="font-semibold text-zinc-950 dark:text-white" x-text="result.cron_jobs"></span></div>
                    <div>Startup: <span class="font-semibold text-zinc-950 dark:text-white" x-text="result.startup"></span></div>
                </div>
                <template x-if="result.error">
                    <p class="text-xs text-red-600 mt-2" x-text="result.error"></p>
                </template>
            </div>
            <div x-show="lastScan" class="text-xs text-zinc-500 dark:text-zinc-400" x-text="'Last scan: ' + lastScan"></div>
        </div>
    </x-card>

    {{-- Fail2ban --}}
    <x-card size="md" dark="false">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Fail2ban
            </div>
        </x-slot>
        <div class="space-y-4">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Monitor and manage blocked IP addresses to protect your server from brute-force attacks.</p>
            <a href="{{ route('server.fail2ban', $server_id) }}"
                class="inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                Open Fail2ban
            </a>
        </div>
    </x-card>

    {{-- Server health --}}
    <x-card size="md" dark="false">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Server Health
            </div>
        </x-slot>
        <div class="space-y-3">
            <div class="flex items-center gap-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3">
                <div class="w-9 h-9 rounded-lg bg-green-500 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white">Active Protection</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Firewall & monitoring enabled</p>
                </div>
            </div>

            @foreach ([
                ['SSH Protection',  'text-zinc-500',   'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                ['Firewall Rules',  'text-purple-500', 'M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['Auto Updates',    'text-orange-500', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            ] as [$label, $iconColor, $path])
            <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-zinc-800/50 px-3 py-2.5">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}" />
                    </svg>
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</span>
                </div>
                <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/30 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">Active</span>
            </div>
            @endforeach
        </div>
    </x-card>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('malwareScan', (serverId) => ({
        loading: false,
        result: null,
        lastScan: null,
        resultClass: '',
        resultLabel: '',
        badgeClass: '',

        async scan() {
            this.loading = true;
            this.result = null;
            const token = localStorage.getItem('sanctum_token') || '';

            try {
                const r = await fetch(`/api/servers/${serverId}/malware-scan`, {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                    signal: AbortSignal.timeout(60000),
                });
                const data = await r.json();

                if (data.success) {
                    const s = JSON.parse(data.output || '{}');
                    this.result = {
                        status: s.summary?.status || 'unknown',
                        processes: s.summary?.processes_found || 0,
                        binaries: s.summary?.binaries_found || 0,
                        cron_jobs: s.summary?.cron_jobs_found || 0,
                        startup: s.summary?.startup_files_found || 0,
                        error: null,
                    };
                    if (s.summary?.status === 'clean') {
                        this.resultClass = 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800';
                        this.resultLabel = 'Scan complete';
                        this.badgeClass = 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
                    } else if (s.summary?.status === 'warning') {
                        this.resultClass = 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800';
                        this.resultLabel = 'Suspicious items found';
                        this.badgeClass = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
                    } else {
                        this.resultClass = 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800';
                        this.resultLabel = 'Infection detected';
                        this.badgeClass = 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
                    }
                    this.lastScan = new Date().toLocaleString();
                } else {
                    this.result = { status: 'failed', processes: 0, binaries: 0, cron_jobs: 0, startup: 0, error: data.error || 'Scan failed' };
                    this.resultClass = 'bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700';
                    this.resultLabel = 'Scan failed';
                    this.badgeClass = 'bg-zinc-100 text-zinc-700';
                }
            } catch (e) {
                this.result = { status: 'error', processes: 0, binaries: 0, cron_jobs: 0, startup: 0, error: e.message };
                this.resultClass = 'bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700';
                this.resultLabel = 'Error';
                this.badgeClass = 'bg-zinc-100 text-zinc-700';
            }
            this.loading = false;
        }
    }));
});
</script>

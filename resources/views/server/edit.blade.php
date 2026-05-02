@extends('layouts.app')

@section('title')
    {{ __('spikster.titles.server') }}
@endsection

@section('content')
    @php
        $section = $section ?? 'overview';
        $sections = [
            'overview'    => 'Overview',
            'monitor'     => 'Monitor',
            'information' => 'Information',
            'security'    => 'Security',
            'tools'       => 'Tools',
        ];
    @endphp

    <div class="space-y-6">
        <x-page-header title="Server Management" subtitle="Manage, monitor and configure your server.">
            <x-slot name="actions">
                <x-back-button :href="route('server.list')" />
            </x-slot>
        </x-page-header>

        {{-- Info bar --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="flex items-center gap-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
                <div class="w-9 h-9 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">IP</p>
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white truncate" id="serveriptop">-</p>
                </div>
            </div>
            <div class="flex items-center gap-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
                <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Sites</p>
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white truncate" id="serversites">-</p>
                </div>
            </div>
            <div class="flex items-center gap-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
                <div class="w-9 h-9 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">PHP CLI</p>
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white truncate" id="serverbuild">-</p>
                </div>
            </div>
            <div class="flex items-center gap-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
                <div class="w-9 h-9 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Ping</p>
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white" id="serverping">
                        <svg class="animate-spin h-4 w-4 inline-block text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </p>
                </div>
            </div>
        </div>

        @php
            $navItems = collect($sections)->map(fn($label, $key) => [
                'href'   => route('server.edit.section', ['server_id' => $server_id, 'section' => $key]),
                'label'  => $label,
                'active' => $section === $key,
            ])->values()->all();
        @endphp

        <x-section-nav :items="$navItems" title="Manage Sections">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-3">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ $sections[$section] ?? 'Overview' }}</h2>
            </div>

            @includeIf('server.manage.sections.' . $section, ['server_id' => $server_id])
        </x-section-nav>
    </div>
@endsection

@section('extra')
    {{-- Root Reset Modal --}}
    <x-modal id="root-reset-modal" title="{{ __('spikster.require_password_reset_modal_title') }}" max-width="lg">
        <p class="text-sm text-zinc-700 dark:text-zinc-300">
            {{ __('spikster.require_password_reset_modal_text') }}
        </p>
        <x-slot name="footer">
            <x-secondary-button @click="open = false">Cancel</x-secondary-button>
            <x-danger-button id="rootresetsubmit">{{ __('spikster.confirm') }}</x-danger-button>
        </x-slot>
    </x-modal>
@endsection

@section('js')
    <script>
        const $id = id => document.getElementById(id);
        const $html = (id, v) => { const e = $id(id); if (e) e.innerHTML = v; };

        function serverInit() {
            api('/api/servers/{{ $server_id }}').then(data => {
                $html('serveriptop', data.ip);
                $html('serversites', data.sites);
                $html('maintitle', '- ' + data.name);
                $html('serverbuild', data.build || '{{ __('spikster.unknown') }}');
                ['8.3','8.2','8.1','8.0','7.4'].forEach(v => {
                    const el = $id('php' + v.replace('.', ''));
                    if (el && data.php === v) el.setAttribute('selected', 'selected');
                });
                if (data.php === '7.3') {
                    const phpver = $id('phpver');
                    if (phpver) phpver.insertAdjacentHTML('beforeend', '<option value="7.3" selected>7.3</option>');
                }
            });
            api('/api/servers').then(data => {
                try { localStorage.otherdata = JSON.stringify(data); } catch(e) {}
            });
        }
        serverInit();

        function getPing() {
            const spinner = '<svg class="animate-spin h-4 w-4 inline-block text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            const okIcon = '<svg class="w-4 h-4 inline-block text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
            const errIcon = '<svg class="w-4 h-4 inline-block text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';

            const pingEl = $id('serverping');
            if (pingEl) pingEl.innerHTML = spinner;

            const controller = new AbortController();
            const timer = setTimeout(() => controller.abort(), 10000);

            fetch('/api/servers/{{ $server_id }}/ping', {
                headers: { 'Authorization': 'Bearer ' + (localStorage.getItem('sanctum_token') || ''), 'Accept': 'application/json' },
                signal: controller.signal,
            }).then(r => r.json()).then(data => {
                if (pingEl) pingEl.innerHTML = data.status === 'online' ? okIcon : errIcon;
            }).catch(() => {
                if (pingEl) pingEl.innerHTML = errIcon;
            }).finally(() => clearTimeout(timer));
        }
        setInterval(getPing, 10000);
        getPing();

        // Change PHP CLI
        const changePhpBtn = document.getElementById('changephp');
        if (changePhpBtn) {
            changePhpBtn.addEventListener('click', () => {
                const phpver = document.getElementById('phpver');
                api('/api/servers/{{ $server_id }}', 'PATCH', { php: phpver ? phpver.value : '' })
                    .then(() => serverInit());
            });
        }

        // Root Reset
        const rootResetBtn = document.getElementById('rootreset');
        if (rootResetBtn) {
            rootResetBtn.addEventListener('click', () => {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'root-reset-modal' }));
            });
        }
        const rootResetSubmit = document.getElementById('rootresetsubmit');
        if (rootResetSubmit) {
            rootResetSubmit.addEventListener('click', () => {
                api('/api/servers/{{ $server_id }}/rootreset', 'POST').then(data => {
                    window.scrollTo(0, 0);
                    window.dispatchEvent(new CustomEvent('close-modal'));
                });
            });
        }
    </script>
@endsection

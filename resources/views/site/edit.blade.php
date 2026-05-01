@extends('layouts.app')


@section('title')
    {{ __('spikster.titles.site') }}
@endsection



@section('content')
    @php
        $section = $section ?? 'overview';
        $sections = [
            'overview' => 'Overview',
            'configuration' => 'Configuration',
            'security' => 'Security',
            'integrations' => 'Integrations',
            'deployments' => 'Deployments',
            'services' => 'Services',
        ];
    @endphp

    <div class="space-y-6">
        <x-page-header title="Site Management" subtitle="Organized by sections for faster navigation.">
            <x-slot name="actions">
                <x-back-button :href="route('site.list')" />
            </x-slot>
        </x-page-header>

        {{-- Dynamic info bar --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="flex items-center gap-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
                <div class="w-9 h-9 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">IP</p>
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white truncate" id="siteip">-</p>
                </div>
            </div>
            <div class="flex items-center gap-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
                <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Aliases</p>
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white truncate" id="sitealiases">-</p>
                </div>
            </div>
            <div class="flex items-center gap-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
                <div class="w-9 h-9 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">PHP</p>
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white truncate" id="sitephp">-</p>
                </div>
            </div>
            <div class="flex items-center gap-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
                <div class="w-9 h-9 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Base Path</p>
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white truncate">/home/<span id="siteuserinfo"></span>/web/<span id="sitebasepathinfo"></span></p>
                </div>
            </div>
        </div>

        @php
            $navItems = collect($sections)->map(fn($label, $key) => [
                'href'   => route('site.edit.section', ['site_id' => $site_id, 'section' => $key]),
                'label'  => $label,
                'active' => $section === $key,
            ])->values()->all();
        @endphp

        <x-section-nav :items="$navItems" title="Manage Sections">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-3">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ $sections[$section] ?? 'Overview' }}</h2>
            </div>

            @includeIf('site.manage.sections.' . $section, ['site_id' => $site_id])
        </x-section-nav>
    </div>
@endsection



@section('extra')
    <input type="hidden" id="currentdomain">
    <input type="hidden" id="server_id">

        {{-- Repository Modal --}}
    <x-modal id="repository-modal" title="{{ __('spikster.github_repository') }}" max-width="lg">
        <div class="space-y-4">
            <div>
                <label for="repositoryproject" class="form-label">{{ __('spikster.repository_project') }}</label>
                <input class="form-input" type="text" id="repositoryproject" placeholder="e.g. johndoe/helloworld" autocomplete="off" />
            </div>
            <div>
                <label for="repositorybranch" class="form-label">{{ __('spikster.repository_branch') }}</label>
                <input class="form-input" type="text" id="repositorybranch" placeholder="e.g. develop" autocomplete="off" />
            </div>
            <div>
                <label for="deploykey" class="form-label">
                    {{ __('spikster.repository_deploy_key') }} {!! __('spikster.repository_deploy_key_info') !!}
                </label>
                <textarea id="deploykey" readonly
                    class="w-full h-36 px-4 py-2.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 text-zinc-950 dark:text-white font-mono"></textarea>
            </div>
        </div>
        <x-slot name="footer">
            <x-secondary-button @click="open = false">Cancel</x-secondary-button>
            <x-primary-button id="repositorysubmit">{{ __('spikster.confirm') }}</x-primary-button>
        </x-slot>
    </x-modal>

    {{-- Deploy Scripts Modal --}}
    <x-modal id="deploy-modal" title="{{ __('spikster.deploy_scripts') }}" max-width="lg">
        <p class="mb-4 text-sm text-zinc-600 dark:text-zinc-400">{{ __('spikster.github_repository_scripts') }}:</p>
        <div id="deploy" style="height:250px;width:100%;border-radius:0.5rem;overflow:hidden;"></div>
        <x-slot name="footer">
            <x-secondary-button @click="open = false">Cancel</x-secondary-button>
            <x-primary-button id="deploysubmit">{{ __('spikster.save') }}</x-primary-button>
        </x-slot>
    </x-modal>

    {{-- SSH Reset Modal --}}
    <x-modal id="ssh-reset-modal" title="{{ __('spikster.require_password_reset_modal_title') }}" max-width="lg">
        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('spikster.require_ssh_password_reset_modal_text') }}</p>
        <x-slot name="footer">
            <x-secondary-button @click="open = false">Cancel</x-secondary-button>
            <x-danger-button id="sshresetsubmit">{{ __('spikster.confirm') }}</x-danger-button>
        </x-slot>
    </x-modal>

    {{-- MySQL Reset Modal --}}
    <x-modal id="mysql-reset-modal" title="{{ __('spikster.require_password_reset_modal_title') }}" max-width="lg">
        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('spikster.require_mysql_password_reset_modal_text') }}</p>
        <x-slot name="footer">
            <x-secondary-button @click="open = false">Cancel</x-secondary-button>
            <x-danger-button id="mysqlresetsubmit">{{ __('spikster.confirm') }}</x-danger-button>
        </x-slot>
    </x-modal>
@endsection



@section('css')
@endsection



@section('js')
    <script>
        const SITE_ID = '{{ $site_id }}';
        const TOKEN = localStorage.getItem('access_token') || '';

        function api(url, method = 'GET', body = null) {
            const opts = {
                method,
                headers: {
                    'Authorization': 'Bearer ' + TOKEN,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            };
            if (body) opts.body = JSON.stringify(body);
            return fetch(url, opts).then(r => r.json());
        }

        function el(id) { return document.getElementById(id); }
        function hide(id) { const e = el(id); if (e) e.classList.add('hidden'); }
        function show(id) { const e = el(id); if (e) e.classList.remove('hidden'); }
        function html(id, val) { const e = el(id); if (e) e.innerHTML = val; }
        function val(id, v) { const e = el(id); if (e && v !== undefined) e.value = v; }
        function on(id, ev, fn) { const e = el(id); if (e) e.addEventListener(ev, fn); }
        function setSpinner(id) { const e = el(id); if (e) e.innerHTML = '<svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'; }

        // Site Init
        function siteInit() {
            api('/api/sites/' + SITE_ID).then(data => {
                hide('mainloading');
                html('siteip', data.server_ip);
                html('sitealiases', data.aliases);
                html('sitephp', data.php);
                html('sitebasepathinfo', data.basepath);
                html('siteuserinfo', data.username);
                html('maintitle', '- ' + data.domain);
                val('sitedomain', data.domain);
                val('sitebasepath', data.basepath);
                val('siteuuid', data.rootpath);
                val('currentdomain', data.domain);
                val('server_id', data.server_id);
                val('sitesupervisor', data.supervisor);
                html('deploykey', data.deploy_key);
                html('repodeployinfouser1', data.username);
                html('repodeployinfouser2', data.username);
                html('repodeployinfoip', data.server_ip);
                val('repositoryproject', data.repository);
                val('repositorybranch', data.branch);
                if (typeof deploy !== 'undefined') deploy.session.setValue(data.deploy || '');
                if (data.server_id) api('/api/servers/' + data.server_id + '/domains');
                const phpSel = el('sitephpver');
                if (phpSel) {
                    Array.from(phpSel.options).forEach(o => o.selected = (o.value === data.php));
                    if (data.php === '7.3' && !Array.from(phpSel.options).find(o => o.value === '7.3')) {
                        phpSel.insertAdjacentHTML('beforeend', '<option value="7.3" selected>7.3</option>');
                    }
                }
            });

            api('/api/sites/' + SITE_ID + '/aliases').then(data => {
                const list = el('sitealiaseslist');
                if (!list || !Array.isArray(data)) return;
                list.innerHTML = data.map(item =>
                    '<span class="inline-flex items-center gap-2 px-3 py-1.5 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700/50 text-purple-700 dark:text-purple-300 text-sm rounded-lg">' +
                    item.domain + '<button data-id="' + item.alias_id +
                    '" class="transition-colors sitealiasdel hover:text-purple-900 dark:hover:text-purple-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></span>'
                ).join('');
                aliasesDelete();
            });
        }

        // SSH Password reset
        on('sitesshreset', 'click', () => window.dispatchEvent(new CustomEvent('open-modal', { detail: 'ssh-reset-modal' })));
        on('sshresetsubmit', 'click', () => {
            show('sshresetloading');
            api('/api/sites/' + SITE_ID + '/reset/ssh', 'POST').then(data => {
                hide('sshresetloading');
                window.dispatchEvent(new CustomEvent('close-modal'));
                window.scrollTo(0, 0);
            });
        });

        // DB Password reset
        on('sitemysqlreset', 'click', () => window.dispatchEvent(new CustomEvent('open-modal', { detail: 'mysql-reset-modal' })));
        on('mysqlresetsubmit', 'click', () => {
            show('mysqlresetloading');
            api('/api/sites/' + SITE_ID + '/reset/db', 'POST').then(data => {
                hide('mysqlresetloading');
                window.dispatchEvent(new CustomEvent('close-modal'));
                window.scrollTo(0, 0);
            });
        });

        // SSL
        on('sitessl', 'click', () => {
            show('sitesslloading');
            api('/api/sites/' + SITE_ID + '/ssl', 'POST').then(() => hide('sitesslloading'));
        });

        // Repository
        on('sitesetrepo', 'click', () => window.dispatchEvent(new CustomEvent('open-modal', { detail: 'repository-modal' })));
        on('repositorysubmit', 'click', () => {
            show('repositoryloading');
            api('/api/sites/' + SITE_ID, 'PATCH', {
                repository: el('repositoryproject')?.value,
                branch: el('repositorybranch')?.value,
            }).then(() => {
                hide('repositoryloading');
                window.dispatchEvent(new CustomEvent('close-modal'));
                siteInit();
            });
        });

        // Copy deploy key
        on('copykey', 'click', () => {
            const key = el('deploykey');
            if (key) navigator.clipboard?.writeText(key.innerText) || (key.select?.(), document.execCommand('copy'));
        });

        // Deploy editor (ace)
        let deploy;
        if (typeof ace !== 'undefined') {
            deploy = ace.edit('deploy');
            deploy.setTheme('ace/theme/monokai');
            deploy.session.setMode('ace/mode/sh');
        }

        on('editdeploy', 'click', () => window.dispatchEvent(new CustomEvent('open-modal', { detail: 'deploy-modal' })));
        on('deploysubmit', 'click', () => {
            show('deployloading');
            api('/api/sites/' + SITE_ID, 'PATCH', {
                deploy: typeof deploy !== 'undefined' ? deploy.getSession().getValue() : '',
            }).then(() => {
                hide('deployloading');
                window.dispatchEvent(new CustomEvent('close-modal'));
                siteInit();
            });
        });

        // Domain conflict check
        function domainConflict(domain) {
            try {
                const data = JSON.parse(localStorage.getItem('otherdata') || '[]');
                return data.filter(item => item === domain).length;
            } catch { return 0; }
        }

        // Aliases
        on('siteaddalias', 'keyup', () => {
            el('siteaddalias')?.classList.remove('border-red-500');
            el('siteaddalias')?.classList.add('border-zinc-200', 'dark:border-zinc-700');
        });
        on('siteaddaliassubmit', 'click', () => {
            const input = el('siteaddalias');
            const domain = input?.value || '';
            if (!domain || domainConflict(domain) > 0) {
                input?.classList.remove('border-zinc-200');
                input?.classList.add('border-red-500');
                return;
            }
            setSpinner('siteaddaliassubmit');
            api('/api/sites/' + SITE_ID + '/aliases', 'POST', { domain }).then(() => {
                if (input) input.value = '';
                const btn = el('siteaddaliassubmit');
                if (btn) btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>';
                siteInit();
            });
        });

        function aliasesDelete() {
            document.querySelectorAll('.sitealiasdel').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    api('/api/sites/' + SITE_ID + '/aliases/' + id, 'DELETE').then(() => {
                        show('mainloading');
                        setTimeout(() => siteInit(), 5000);
                    });
                });
            });
        }

        // Change PHP
        on('sitephpversubmit', 'click', () => {
            setSpinner('sitephpversubmit');
            api('/api/sites/' + SITE_ID, 'PATCH', { php: el('sitephpver')?.value }).then(() => {
                const btn = el('sitephpversubmit');
                if (btn) btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>';
                siteInit();
            });
        });

        // Supervisor
        on('sitesupervisorupdate', 'click', () => {
            show('sitesupervisorupdateloading');
            api('/api/sites/' + SITE_ID, 'PATCH', { supervisor: el('sitesupervisor')?.value }).then(() => {
                hide('sitesupervisorupdateloading');
                siteInit();
            });
        });

        // Basic info
        on('updateSite', 'click', () => {
            show('updateSiteloadingloading');
            api('/api/sites/' + SITE_ID, 'PATCH', {
                domain: el('sitedomain')?.value,
                basepath: el('sitebasepath')?.value,
            }).then(() => {
                hide('updateSiteloadingloading');
                siteInit();
            });
        });

        // Init
        show('mainloading');
        siteInit();
    </script>
@endsection

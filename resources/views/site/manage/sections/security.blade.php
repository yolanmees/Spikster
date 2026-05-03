<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    {{-- File Malware Scan --}}
    <x-card size="md" dark="false" class="xl:col-span-2">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016zM12 9v2m0 4h.01" />
                </svg>
                File Malware Scanner
            </div>
        </x-slot>
        <div class="space-y-4" x-data="siteFileScan('{{ $site_id }}')">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Scan site PHP files for obfuscated code, backdoors, and suspicious patterns (eval, base64_decode, shell_exec, etc.).</p>
            <div class="flex items-center gap-3">
                <button type="button" @click="scan()" :disabled="loading"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!loading">Run File Scan</span>
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
                <p class="text-xs text-zinc-600 dark:text-zinc-400">
                    Files scanned: <span class="font-semibold text-zinc-950 dark:text-white" x-text="result.files"></span>
                    &middot; Findings: <span class="font-semibold" :class="result.findings > 0 ? 'text-red-600' : 'text-green-600'" x-text="result.findings"></span>
                </p>
                <template x-if="result.error">
                    <p class="text-xs text-red-600 mt-2" x-text="result.error"></p>
                </template>
            </div>
        </div>
    </x-card>

    {{-- SSL and Credential Resets --}}
    <x-card size="md" dark="false">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <x-icon icon="check" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                SSL and Credential Resets
            </div>
        </x-slot>
        <div class="space-y-4">
            <div>
                <p class="mb-3 text-sm text-zinc-600 dark:text-zinc-400">Generate or renew SSL certificates and reset service passwords.</p>
                <div class="flex items-center gap-2 mb-3">
                    <button type="button" id="sitessl"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                        Generate SSL
                    </button>
                    <button type="button" id="sitecertstatus"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 font-semibold rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                        Check Status
                    </button>
                </div>
                <div id="sslstatus" class="hidden p-3 rounded-lg text-sm border"></div>
            </div>
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <p class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Reset credentials:</p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" id="sitesshreset"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 font-semibold rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                        Reset SSH
                    </button>
                    <button type="button" id="sitemysqlreset"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 font-semibold rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                        Reset MySQL
                    </button>
                </div>
            </div>
        </div>
    </x-card>

    <x-site-management-card
        title="File Permissions"
        subtitle="Maintenance"
        description="Fix file ownership and permission issues with one secure action."
        status="Manual"
        statusClass="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
        buttonVariant="secondary"
    >
        <x-slot name="icon">
            <x-icon icon="folder" class="h-4 w-4 text-zinc-700 dark:text-zinc-300" />
        </x-slot>

        <x-slot name="actions">
            <button type="button" id="resetPermissionsBtn"
                class="inline-flex w-full items-center justify-center rounded-lg bg-zinc-100 px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 disabled:cursor-not-allowed disabled:opacity-50">
                <span id="resetPermissionsText">Reset Permissions</span>
            </button>
        </x-slot>
    </x-site-management-card>

    <x-card size="md" dark="false" class="xl:col-span-2">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <x-icon icon="check" class="h-5 w-5 text-green-600 dark:text-green-400" />
                Runtime Health Check
            </div>
        </x-slot>
        <div class="space-y-4">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Check if the site's services are running and responding correctly.</p>
            <button type="button" id="sitehealthcheck"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                Run Health Check
            </button>
            <div id="healthresults" class="hidden space-y-2"></div>
        </div>
    </x-card>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('siteFileScan', (siteId) => ({
        loading: false,
        result: null,
        resultClass: '',
        resultLabel: '',
        badgeClass: '',

        async scan() {
            this.loading = true;
            this.result = null;
            const token = localStorage.getItem('sanctum_token') || '';

            try {
                const r = await fetch(`/api/sites/${siteId}/file-scan`, {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                    signal: AbortSignal.timeout(120000),
                });
                const data = await r.json();

                if (data.success) {
                    const scan = data.scan || {};
                    this.result = {
                        status: scan.is_clean ? 'clean' : 'warning',
                        files: scan.files_scanned || 0,
                        findings: scan.findings_count || 0,
                        error: null,
                    };
                    if (scan.is_clean) {
                        this.resultClass = 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800';
                        this.resultLabel = 'No threats found';
                        this.badgeClass = 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
                    } else {
                        this.resultClass = 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800';
                        this.resultLabel = 'Suspicious files detected';
                        this.badgeClass = 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
                    }
                } else {
                    this.result = { status: 'failed', files: 0, findings: 0, error: data.error || 'Scan failed' };
                    this.resultClass = 'bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700';
                    this.resultLabel = 'Scan failed';
                    this.badgeClass = 'bg-zinc-100 text-zinc-700';
                }
            } catch (e) {
                this.result = { status: 'error', files: 0, findings: 0, error: e.message };
                this.resultClass = 'bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700';
                this.resultLabel = 'Error';
                this.badgeClass = 'bg-zinc-100 text-zinc-700';
            }
            this.loading = false;
        }
    }));
});
</script>

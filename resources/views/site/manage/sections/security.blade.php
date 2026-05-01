<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
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
                <button type="button" id="sitessl"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                    Generate SSL
                </button>
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
</div>

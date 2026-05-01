<div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="mb-4">
        <label for="panel_domain" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
            Custom Panel Domain/Subdomain
        </label>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-3">
            Configure a custom domain or subdomain for your Spikster control panel
        </p>
        <input type="text" wire:model="panel_domain" id="panel_domain" placeholder="control.spikster.com"
            autocomplete="off"
            class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white" />
    </div>

    <div class="flex gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
        <button type="button" wire:click="updatePanelDomain()"
            class="inline-flex items-center gap-2 rounded-lg bg-zinc-950 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Update Domain
        </button>
        <button type="button" wire:click="sslPanelDomain()" title="{{ __('spikster.panel_url_force_ssl') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm font-medium hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Force SSL
        </button>
    </div>
</div>

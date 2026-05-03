<div class="mt-6 space-y-6">
    <!-- Panel URL -->
    <div>
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
                {{ __('spikster.panel_url') }}
            </h2>
        </div>
        @livewire('settings.panel-domain')
    </div>

    <!-- Panel API -->
    <div>
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ __('spikster.panel_api') }}
            </h2>
        </div>
        @livewire('settings.api-key')
    </div>
</div>

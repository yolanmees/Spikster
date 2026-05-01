<div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="mb-4">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">API Endpoint:</span>
            <code class="px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 font-mono text-xs text-zinc-700 dark:text-zinc-300">
                {{ $api_endpoint }}
            </code>
        </div>
    </div>

    @if ($show_api_key)
        <div class="mb-4">
            <label for="api_key" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                API Key
            </label>
            <div class="relative">
                <input wire:model="api_key" type="text" name="api_key" id="api_key" readonly
                    class="h-10 w-full rounded-lg border border-zinc-200 bg-zinc-50 px-3 pr-10 font-mono text-sm outline-none dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300" />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                Keep this key secure. It provides full access to your Spikster API.
            </p>
        </div>
    @endif

    <div class="flex gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
        <button wire:click="generateApiKey" type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-zinc-950 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            Generate API Key
        </button>
        <a href="{{ $api_endpoint }}/docs" target="_blank"
            class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm font-medium hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            API Docs
        </a>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    {{-- PHP CLI version --}}
    <x-card size="md" dark="false">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                PHP Configuration
            </div>
        </x-slot>
        <div class="space-y-4">
            <div>
                <label class="block mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('spikster.php_cli_version') }}</label>
                <div class="flex gap-2">
                    <select id="phpver"
                        class="flex-1 px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all">
                        <option value="8.3" id="php83">PHP 8.3</option>
                        <option value="8.2" id="php82">PHP 8.2</option>
                        <option value="8.1" id="php81">PHP 8.1</option>
                        <option value="8.0" id="php80">PHP 8.0</option>
                        <option value="7.4" id="php74">PHP 7.4</option>
                    </select>
                    <button type="button" id="changephp"
                        class="px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                        Save
                    </button>
                </div>
            </div>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">This changes the default PHP version for CLI commands.</p>
        </div>
    </x-card>

    {{-- Cron jobs --}}
    <x-card size="md" dark="false">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Scheduled Tasks
            </div>
        </x-slot>
        <div class="space-y-4">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('spikster.manage_cron_jobs') }}</p>
            <a href="{{ route('server.cron', $server_id) }}"
                class="inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                {{ __('spikster.edit_crontab') }}
            </a>
            <p class="text-xs text-amber-600 dark:text-amber-400">Be careful when editing cron jobs. Incorrect syntax may break scheduled tasks.</p>
        </div>
    </x-card>

    {{-- Password reset --}}
    <x-card size="md" dark="false" class="xl:col-span-2">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                System Access
            </div>
        </x-slot>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-zinc-950 dark:text-white">{{ __('spikster.reset_cipi_password') }}</p>
                <p class="text-xs text-red-600 dark:text-red-400 mt-1">This will generate a new password for the server user. Store it safely.</p>
            </div>
            <button type="button" id="rootreset"
                class="shrink-0 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                {{ __('spikster.require_reset_cipi_password') }}
            </button>
        </div>
    </x-card>
</div>

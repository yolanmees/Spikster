<div>
    <form wire:submit.prevent="submit">
        <div class="space-y-6">
            @if (session()->has('error'))
                <div class="flex items-start gap-3 rounded-lg border-l-4 border-red-400 bg-red-50 p-4 dark:border-red-600 dark:bg-red-900/20">
                    <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <div class="p-6 space-y-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Schedule Details</h3>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Schedule Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" wire:model="name"
                                class="mt-1 block w-full rounded-lg border-zinc-300 dark:border-zinc-700 shadow-sm focus:border-purple-700 focus:ring-purple-700 dark:bg-zinc-800 dark:text-white sm:text-sm"
                                placeholder="e.g. Daily Full Backup">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Backup Type <span class="text-red-500">*</span>
                            </label>
                            <select id="type" wire:model="type"
                                class="mt-1 block w-full rounded-lg border-zinc-300 dark:border-zinc-700 shadow-sm focus:border-purple-700 focus:ring-purple-700 dark:bg-zinc-800 dark:text-white sm:text-sm">
                                <option value="full">Full Backup</option>
                                <option value="incremental">Incremental Backup</option>
                                <option value="database">Database Only</option>
                                <option value="files">Files Only</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="frequency" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Frequency <span class="text-red-500">*</span>
                            </label>
                            <select id="frequency" wire:model.live="frequency"
                                class="mt-1 block w-full rounded-lg border-zinc-300 dark:border-zinc-700 shadow-sm focus:border-purple-700 focus:ring-purple-700 dark:bg-zinc-800 dark:text-white sm:text-sm">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="custom">Custom (Cron)</option>
                            </select>
                            @error('frequency')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($frequency !== 'custom')
                            <div>
                                <label for="time" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Time <span class="text-red-500">*</span>
                                </label>
                                <input type="time" id="time" wire:model="time"
                                    class="mt-1 block w-full rounded-lg border-zinc-300 dark:border-zinc-700 shadow-sm focus:border-purple-700 focus:ring-purple-700 dark:bg-zinc-800 dark:text-white sm:text-sm">
                                @error('time')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        @if ($frequency === 'weekly')
                            <div>
                                <label for="day_of_week" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Day of Week
                                </label>
                                <select id="day_of_week" wire:model="day_of_week"
                                    class="mt-1 block w-full rounded-lg border-zinc-300 dark:border-zinc-700 shadow-sm focus:border-purple-700 focus:ring-purple-700 dark:bg-zinc-800 dark:text-white sm:text-sm">
                                    <option value="0">Sunday</option>
                                    <option value="1">Monday</option>
                                    <option value="2">Tuesday</option>
                                    <option value="3">Wednesday</option>
                                    <option value="4">Thursday</option>
                                    <option value="5">Friday</option>
                                    <option value="6">Saturday</option>
                                </select>
                            </div>
                        @endif

                        @if ($frequency === 'monthly')
                            <div>
                                <label for="day_of_month" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Day of Month
                                </label>
                                <input type="number" id="day_of_month" wire:model="day_of_month" min="1" max="31"
                                    class="mt-1 block w-full rounded-lg border-zinc-300 dark:border-zinc-700 shadow-sm focus:border-purple-700 focus:ring-purple-700 dark:bg-zinc-800 dark:text-white sm:text-sm">
                            </div>
                        @endif

                        @if ($frequency === 'custom')
                            <div class="sm:col-span-2">
                                <label for="cron_expression" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Cron Expression <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="cron_expression" wire:model="cron_expression" placeholder="0 2 * * *"
                                    class="mt-1 block w-full rounded-lg border-zinc-300 dark:border-zinc-700 shadow-sm focus:border-purple-700 focus:ring-purple-700 dark:bg-zinc-800 dark:text-white sm:text-sm font-mono">
                                @error('cron_expression')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Standard cron format: minute hour day month weekday</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <div class="p-6 space-y-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Retention & Encryption</h3>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="retention_count" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Keep Last N Backups
                            </label>
                            <input type="number" id="retention_count" wire:model="retention_count" min="1"
                                class="mt-1 block w-full rounded-lg border-zinc-300 dark:border-zinc-700 shadow-sm focus:border-purple-700 focus:ring-purple-700 dark:bg-zinc-800 dark:text-white sm:text-sm">
                            @error('retention_count')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="retention_days" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Keep for N Days
                            </label>
                            <input type="number" id="retention_days" wire:model="retention_days" min="1"
                                class="mt-1 block w-full rounded-lg border-zinc-300 dark:border-zinc-700 shadow-sm focus:border-purple-700 focus:ring-purple-700 dark:bg-zinc-800 dark:text-white sm:text-sm">
                            @error('retention_days')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="is_encrypted"
                                class="h-4 w-4 rounded border-zinc-300 text-purple-700 focus:ring-purple-700 dark:border-zinc-600">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Encrypt backups (GPG AES-256)</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="is_active"
                                class="h-4 w-4 rounded border-zinc-300 text-purple-700 focus:ring-purple-700 dark:border-zinc-600">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Enable schedule immediately</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('backups.index', $site) }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-lg shadow-sm hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:bg-zinc-800 dark:hover:bg-zinc-700">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-700 border border-transparent rounded-lg shadow-sm hover:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50"
                    wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Create Schedule</span>
                    <span wire:loading wire:target="submit" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Creating...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>

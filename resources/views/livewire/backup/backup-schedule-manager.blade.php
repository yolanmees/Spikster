<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Backup Schedules</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Automate your backups with scheduled tasks</p>
            </div>
            <a href="{{ route('backups.schedules.create', $site) }}" wire:navigate
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-700 rounded-lg hover:bg-purple-800 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Schedule
            </a>
        </div>
    </div>

    {{-- Schedules List --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($schedules as $schedule)
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white truncate">{{ $schedule->name }}</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ $schedule->getScheduleDescription() }}</p>
                        </div>
                        <button wire:click="toggleSchedule({{ $schedule->id }})"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-700 focus:ring-offset-2 {{ $schedule->is_active ? 'bg-purple-700' : 'bg-zinc-200 dark:bg-zinc-700' }}">
                            <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $schedule->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full
                                @if($schedule->type === 'full') bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200
                                @elseif($schedule->type === 'incremental') bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300
                                @elseif($schedule->type === 'database') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                @else bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 @endif">
                                {{ ucfirst($schedule->type) }}
                            </span>
                            @if($schedule->is_encrypted)
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                    Encrypted
                                </span>
                            @endif
                            <span class="px-3 py-1 text-xs font-semibold rounded-full
                                {{ $schedule->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-zinc-500 dark:text-zinc-400">Storage</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ implode(', ', array_map('ucfirst', $schedule->storage_locations)) }}</p>
                            </div>
                            <div>
                                <p class="text-zinc-500 dark:text-zinc-400">Retention</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $schedule->getRetentionDescription() }}</p>
                            </div>
                            <div>
                                <p class="text-zinc-500 dark:text-zinc-400">Last Run</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $schedule->last_run_at ? $schedule->last_run_at->diffForHumans() : 'Never' }}</p>
                            </div>
                            <div>
                                <p class="text-zinc-500 dark:text-zinc-400">Next Run</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $schedule->next_run_at ? $schedule->next_run_at->diffForHumans() : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button wire:click="deleteSchedule({{ $schedule->id }})"
                            class="w-full px-3 py-2 text-sm font-medium rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="mt-4 text-zinc-500 dark:text-zinc-400">No backup schedules configured</p>
                <a href="{{ route('backups.schedules.create', $site) }}" wire:navigate
                    class="inline-flex items-center mt-4 px-4 py-2 text-sm font-medium text-white bg-purple-700 rounded-lg hover:bg-purple-800 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create First Schedule
                </a>
            </div>
        @endforelse
    </div>
</div>

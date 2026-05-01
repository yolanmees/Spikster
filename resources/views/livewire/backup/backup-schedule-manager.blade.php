<div class="space-y-6">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Backup Schedules</h2>
                <p class="mt-1 text-sm text-gray-500">Automate your backups with scheduled tasks</p>
            </div>
            <button wire:click="openCreateModal"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Create Schedule
            </button>
        </div>
    </div>

    {{-- Schedules List --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($schedules as $schedule)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
                <div class="p-6">
                    {{-- Header --}}
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $schedule->name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $schedule->getScheduleDescription() }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="toggleSchedule({{ $schedule->id }})"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $schedule->is_active ? 'bg-blue-600' : 'bg-gray-200' }}">
                                <span
                                    class="translate-x-0 inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $schedule->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Details --}}
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <span
                                class="px-3 py-1 text-xs font-semibold rounded-full
                                @if ($schedule->type === 'full') bg-blue-100 text-blue-800
                                @elseif($schedule->type === 'incremental') bg-purple-100 text-purple-800
                                @elseif($schedule->type === 'database') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($schedule->type) }} Backup
                            </span>
                            @if ($schedule->is_encrypted)
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    🔒 Encrypted
                                </span>
                            @endif
                            <span
                                class="px-3 py-1 text-xs font-semibold rounded-full
                                {{ $schedule->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Storage</p>
                                <p class="font-medium text-gray-900">
                                    {{ implode(', ', array_map('ucfirst', $schedule->storage_locations)) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500">Retention</p>
                                <p class="font-medium text-gray-900">{{ $schedule->getRetentionDescription() }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Last Run</p>
                                <p class="font-medium text-gray-900">
                                    {{ $schedule->last_run_at ? $schedule->last_run_at->diffForHumans() : 'Never' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500">Next Run</p>
                                <p class="font-medium text-gray-900">
                                    {{ $schedule->next_run_at ? $schedule->next_run_at->diffForHumans() : 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-200">
                            <p class="text-xs text-gray-500">
                                {{ $schedule->backups_count ?? 0 }} backups created
                            </p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-4 flex gap-2">
                        <button wire:click="openEditModal({{ $schedule->id }})"
                            class="flex-1 px-3 py-2 text-sm bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-md hover:bg-zinc-200 dark:hover:bg-zinc-700">
                            Edit
                        </button>
                        <button wire:click="openDeleteModal({{ $schedule->id }})"
                            class="flex-1 px-3 py-2 text-sm bg-red-100 text-red-700 rounded-md hover:bg-red-200">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 bg-white rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-4 text-gray-500">No backup schedules configured</p>
                <button wire:click="openCreateModal"
                    class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Create First Schedule
                </button>
            </div>
        @endforelse
    </div>

    {{-- Create/Edit Modal --}}
    @if ($showCreateModal || $showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    wire:click="{{ $showCreateModal ? 'closeCreateModal' : 'closeEditModal' }}"></div>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit.prevent="{{ $showCreateModal ? 'createSchedule' : 'updateSchedule' }}">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                {{ $showCreateModal ? 'Create Backup Schedule' : 'Edit Backup Schedule' }}
                            </h3>

                            <div class="space-y-4">
                                {{-- Name --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Schedule Name</label>
                                    <input type="text" wire:model="name" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('name')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Type --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Backup Type</label>
                                    <select wire:model="type" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="full">Full Backup (Files + Database + Email)</option>
                                        <option value="incremental">Incremental Backup (Changed files only)</option>
                                        <option value="database">Database Only</option>
                                        <option value="files">Files Only</option>
                                    </select>
                                    @error('type')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Frequency --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Frequency</label>
                                    <select wire:model.live="frequency" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="custom">Custom (Cron)</option>
                                    </select>
                                    @error('frequency')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Time --}}
                                @if ($frequency !== 'custom')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Time</label>
                                        <input type="time" wire:model="time" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('time')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                {{-- Day of Week --}}
                                @if ($frequency === 'weekly')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Day of Week</label>
                                        <select wire:model="day_of_week"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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

                                {{-- Day of Month --}}
                                @if ($frequency === 'monthly')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Day of Month</label>
                                        <input type="number" wire:model="day_of_month" min="1"
                                            max="31"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                @endif

                                {{-- Cron Expression --}}
                                @if ($frequency === 'custom')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Cron Expression</label>
                                        <input type="text" wire:model="cron_expression" placeholder="0 2 * * *"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Example: 0 2 * * * (every day at 2:00 AM)
                                        </p>
                                        @error('cron_expression')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                {{-- Encryption --}}
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="is_encrypted" id="is_encrypted"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="is_encrypted" class="ml-2 block text-sm text-gray-700">
                                        Encrypt backups (GPG AES-256)
                                    </label>
                                </div>

                                {{-- Retention Policy --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Keep Last N
                                            Backups</label>
                                        <input type="number" wire:model="retention_count" min="1"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Keep for N Days</label>
                                        <input type="number" wire:model="retention_days" min="1"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                {{-- Active Status --}}
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="is_active" id="is_active"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                        Enable schedule immediately
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ $showCreateModal ? 'Create Schedule' : 'Update Schedule' }}
                            </button>
                            <button type="button"
                                wire:click="{{ $showCreateModal ? 'closeCreateModal' : 'closeEditModal' }}"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-700 shadow-sm px-4 py-2 bg-white dark:bg-zinc-900 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-600 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($showDeleteModal && $selectedSchedule)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDeleteModal">
                </div>

                <div
                    class="inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-zinc-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-zinc-100">Delete Schedule</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Are you sure you want to delete "{{ $selectedSchedule->name }}"? This will not
                                        delete existing backups.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-zinc-50 dark:bg-zinc-950 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="deleteSchedule"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button type="button" wire:click="closeDeleteModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-700 shadow-sm px-4 py-2 bg-white dark:bg-zinc-900 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="space-y-6">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <livewire:components.alert type="success" :message="session('message')" :dismissible="true" />
    @endif
    @if (session()->has('error'))
        <livewire:components.alert type="error" :message="session('error')" :dismissible="true" />
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Backups"
            :value="$stats['total_backups']"
            color="blue"
            icon='<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>'
        />
        <x-stat-card
            title="Completed"
            :value="$stats['completed_backups']"
            color="green"
            icon='<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-stat-card
            title="In Progress"
            :value="$stats['in_progress_backups']"
            color="yellow"
            icon='<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-stat-card
            title="Total Size"
            :value="\App\Models\Backup::formatBytes($stats['total_compressed_size'])"
            color="purple"
            icon='<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>'
        />
    </div>

    {{-- Actions and Filters --}}
    <x-card>
        <x-slot name="header">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
                <div class="flex flex-1 flex-wrap gap-2">
                    <x-text-input model="search" debounce="300" placeholder="Search backups..." class="flex-1 min-w-0">
                        <x-slot name="icon"><x-icon icon="search" class="h-5 w-5 text-gray-400" /></x-slot>
                        <x-slot name="suffix"><x-wire-spinner target="search" /></x-slot>
                    </x-text-input>

                    <x-select model="filterType" class="shrink-0">
                        <option value="">All Types</option>
                        <option value="full">Full</option>
                        <option value="incremental">Incremental</option>
                        <option value="database">Database</option>
                        <option value="files">Files</option>
                    </x-select>

                    <x-select model="filterStatus" class="shrink-0">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </x-select>

                    @if ($search || $filterType || $filterStatus)
                        <x-button variant="light" wire:click="resetFilters">Reset</x-button>
                    @endif
                </div>

                <div class="flex gap-2 shrink-0">
                    <x-button variant="info" wire:click="createDatabaseBackup">Database Only</x-button>
                    <x-primary-button wire:click="createFullBackup">
                        <x-icon icon="plus" class="h-4 w-4 -ml-1 mr-1.5" />
                        Create Backup
                    </x-primary-button>
                </div>
            </div>
        </x-slot>

        {{-- Backups Table --}}
        <div class="relative -m-6">
            <div wire:loading.delay class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 z-10 flex items-center justify-center rounded-lg">
                <livewire:components.loading-spinner size="lg" color="blue" message="Loading..." />
            </div>
            <div class="overflow-x-auto">
                <table class="table min-w-full">
                    <thead class="table-header">
                        <tr>
                            <th class="table-header-cell">Backup</th>
                            <th class="table-header-cell">Type</th>
                            <th class="table-header-cell">Size</th>
                            <th class="table-header-cell">Status</th>
                            <th class="table-header-cell">Created</th>
                            <th class="table-header-cell text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        @forelse($backups as $backup)
                            <tr class="table-row">
                                <td class="table-cell">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $backup->filename }}</div>
                                    @if ($backup->backupSchedule)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Schedule: {{ $backup->backupSchedule->name }}</div>
                                    @endif
                                </td>
                                <td class="table-cell">
                                    <x-badge
                                        :color="match($backup->type) { 'full' => 'blue', 'incremental' => 'purple', 'database' => 'green', default => 'gray' }"
                                        :text="ucfirst($backup->type)"
                                    />
                                    @if ($backup->is_encrypted)
                                        <x-badge color="yellow" text="🔒 Encrypted" class="ml-1" />
                                    @endif
                                </td>
                                <td class="table-cell">
                                    <span>{{ $backup->getFormattedSize() }}</span>
                                    @if ($backup->compression_ratio)
                                        <span class="text-xs text-gray-400 dark:text-gray-500">({{ $backup->compression_ratio }}%)</span>
                                    @endif
                                </td>
                                <td class="table-cell">
                                    @php $colors = $backup->getBadgeColors(); @endphp
                                    <span class="badge {{ $colors['bg'] }} {{ $colors['text'] }}">
                                        {{ ucfirst(str_replace('_', ' ', $backup->status)) }}
                                    </span>
                                </td>
                                <td class="table-cell">
                                    <span>{{ $backup->created_at->diffForHumans() }}</span>
                                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $backup->created_at->format('Y-m-d H:i') }}</div>
                                </td>
                                <td class="table-cell text-right space-x-2 whitespace-nowrap">
                                    @if ($backup->isComplete())
                                        <x-action-button wire:click="openRestoreModal({{ $backup->id }})">Restore</x-action-button>
                                        <x-action-button wire:click="downloadBackup({{ $backup->id }})">Download</x-action-button>
                                    @endif
                                    <x-danger-button size="sm" wire:click="openDeleteModal({{ $backup->id }})">Delete</x-danger-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="backup" title="No backups found">
                                        <x-slot name="action">
                                            <x-primary-button wire:click="createFullBackup">Create First Backup</x-primary-button>
                                        </x-slot>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($backups->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $backups->links() }}
                </div>
            @endif
        </div>
    </x-card>

    {{-- Restore Modal --}}
    @if ($showRestoreModal && $selectedBackup)
        <x-modal title="Restore Backup" max-width="lg">
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Restore Backup</span>
                </div>
            </x-slot>

            <div class="space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Are you sure you want to restore this backup? This will overwrite current site data.
                </p>
                <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3 text-sm">
                    <p class="font-medium text-gray-900 dark:text-white">{{ $selectedBackup->filename }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Created: {{ $selectedBackup->created_at->format('Y-m-d H:i:s') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Size: {{ $selectedBackup->getFormattedSize() }}</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @if ($selectedBackup->includes_database) <x-badge color="blue" text="Database" /> @endif
                        @if ($selectedBackup->includes_files) <x-badge color="green" text="Files" /> @endif
                        @if ($selectedBackup->includes_email) <x-badge color="purple" text="Email" /> @endif
                    </div>
                </div>
                <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 px-3 py-2 text-sm text-yellow-800 dark:text-yellow-300">
                    ⚠️ <strong>Warning:</strong> This action cannot be undone. Consider creating a backup before restoring.
                </div>
            </div>

            <x-slot name="footer">
                <x-secondary-button wire:click="closeRestoreModal">Cancel</x-secondary-button>
                <x-button variant="success" wire:click="confirmRestore">Restore Backup</x-button>
            </x-slot>
        </x-modal>
    @endif

    {{-- Delete Modal --}}
    @if ($showDeleteModal && $selectedBackup)
        <x-modal title="Delete Backup" max-width="lg">
            <div class="space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Are you sure you want to delete this backup?</p>
                <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3 text-sm">
                    <p class="font-medium text-gray-900 dark:text-white">{{ $selectedBackup->filename }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Size: {{ $selectedBackup->getFormattedSize() }}</p>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="checkbox" wire:model="deleteFile" class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                    Also delete the physical backup file from storage
                </label>
            </div>

            <x-slot name="footer">
                <x-secondary-button wire:click="closeDeleteModal">Cancel</x-secondary-button>
                <x-danger-button wire:click="confirmDelete">Delete Backup</x-danger-button>
            </x-slot>
        </x-modal>
    @endif
</div>

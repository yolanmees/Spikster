<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Storage Locations</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Configure where backups are stored.</p>
        </div>
        @unless ($showForm)
            <button wire:click="create" class="px-4 py-2 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 text-white font-semibold rounded-lg transition-all text-sm">
                Add Location
            </button>
        @endunless
    </div>

    @if ($showForm)
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 space-y-4">
            <h4 class="font-medium text-zinc-900 dark:text-white">{{ $editingId ? 'Edit' : 'New' }} Storage Location</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Name</label>
                    <input wire:model="name" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Type</label>
                    <select wire:model="type" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                        <option value="local">Local</option>
                        <option value="s3">Amazon S3</option>
                        <option value="ftp">FTP</option>
                        <option value="sftp">SFTP</option>
                    </select>
                </div>
            </div>

            @if ($type === 'local')
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Path</label>
                    <input wire:model="configPath" placeholder="/backups" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                    @error('configPath') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            @elseif ($type === 's3')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Access Key</label>
                        <input wire:model="configAccessKey" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Secret Key</label>
                        <input wire:model="configSecretKey" type="password" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Bucket</label>
                        <input wire:model="configBucket" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                        @error('configBucket') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Region</label>
                        <input wire:model="configRegion" placeholder="us-east-1" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Endpoint (optional, for S3-compatible)</label>
                        <input wire:model="configEndpoint" placeholder="https://s3.example.com" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                    </div>
                </div>
            @elseif ($type === 'ftp' || $type === 'sftp')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Host</label>
                        <input wire:model="configHost" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                        @error('configHost') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Port</label>
                        <input wire:model="configPort" type="number" placeholder="{{ $type === 'sftp' ? '22' : '21' }}" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Username</label>
                        <input wire:model="configUsername" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Password</label>
                        <input wire:model="configPassword" type="password" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Path</label>
                        <input wire:model="configPath" placeholder="/" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                    </div>
                </div>
            @endif

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model="isDefault" class="rounded border-zinc-300 dark:border-zinc-600">
                <span class="text-zinc-700 dark:text-zinc-300">Set as default storage location</span>
            </label>

            <div class="flex items-center gap-3 pt-2">
                <button wire:click="save" class="px-4 py-2 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 text-white font-semibold rounded-lg transition-all text-sm">
                    {{ $editingId ? 'Update' : 'Create' }}
                </button>
                <button wire:click="cancel" class="text-sm text-zinc-600 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors">Cancel</button>
            </div>
        </div>
    @endif

    @if ($locations->isEmpty() && ! $showForm)
        <div class="p-8 text-center text-zinc-500 dark:text-zinc-400">
            <p>No storage locations configured.</p>
            <p class="text-sm mt-1">Add a storage location to store backups off-site.</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($locations as $location)
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $location->is_active ? 'bg-green-500' : 'bg-zinc-400' }}"></span>
                                <h4 class="font-medium text-zinc-900 dark:text-white truncate">{{ $location->name }}</h4>
                                @if ($location->is_default)
                                    <span class="px-2 py-0.5 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded">Default</span>
                                @endif
                            </div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                {{ ucfirst($location->type) }}
                                @if ($location->last_test_at)
                                    · Last test: {{ $location->last_test_status ?? 'unknown' }}
                                    ({{ $location->last_test_at->diffForHumans() }})
                                @endif
                            </p>
                            @if ($location->last_test_error)
                                <p class="text-xs text-red-500 mt-0.5">{{ $location->last_test_error }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 ml-4 shrink-0">
                            <button wire:click="test('{{ $location->id }}')" class="text-xs text-blue-600 hover:underline">Test</button>
                            <button wire:click="edit('{{ $location->id }}')" class="text-xs text-zinc-600 hover:underline">Edit</button>
                            <button wire:click="delete('{{ $location->id }}')" wire:confirm="Delete this storage location?" class="text-xs text-red-600 hover:underline">Delete</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

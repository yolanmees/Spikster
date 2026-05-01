<div class="space-y-6">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded relative" role="alert">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded relative" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Storage Locations</h2>
                <p class="mt-1 text-sm text-gray-500">Configure backup storage backends</p>
            </div>
            <button wire:click="openCreateModal" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Add Storage Location
            </button>
        </div>
    </div>

    {{-- Locations List --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($locations as $location)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $location->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ strtoupper($location->type) }}</p>
                    </div>
                    @if ($location->is_default)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            Default
                        </span>
                    @endif
                </div>

                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Status</span>
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $location->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $location->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    @if ($location->last_test_at)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Last Test</span>
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $location->test_status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($location->test_status) }}
                            </span>
                        </div>
                    @endif

                    @if ($location->type !== 'local')
                        <div class="mt-3 pt-3 border-t border-gray-200 text-xs text-gray-500">
                            @if ($location->type === 's3')
                                <p>Bucket: {{ $location->config['bucket'] ?? 'N/A' }}</p>
                                <p>Region: {{ $location->config['region'] ?? 'N/A' }}</p>
                            @else
                                <p>Host: {{ $location->config['host'] ?? 'N/A' }}</p>
                                <p>Port: {{ $location->config['port'] ?? 'N/A' }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="mt-4 flex gap-2">
                    <button wire:click="testConnection({{ $location->id }})"
                        class="flex-1 px-3 py-2 text-sm bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-md hover:bg-zinc-200 dark:hover:bg-zinc-700">
                        Test
                    </button>
                    @if (!$location->is_default)
                        <button wire:click="openDeleteModal({{ $location->id }})"
                            class="flex-1 px-3 py-2 text-sm bg-red-100 text-red-700 rounded-md hover:bg-red-200">
                            Delete
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white rounded-lg shadow p-12 text-center">
                <p class="text-gray-500">No storage locations configured</p>
                <button wire:click="openCreateModal"
                    class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Add First Location
                </button>
            </div>
        @endforelse
    </div>

    {{-- Create Modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeCreateModal"></div>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit.prevent="createLocation">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Add Storage Location</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" wire:model="name" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('name')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type</label>
                                    <select wire:model.live="type" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="local">Local Disk</option>
                                        <option value="s3">Amazon S3</option>
                                        <option value="ftp">FTP</option>
                                        <option value="sftp">SFTP</option>
                                    </select>
                                </div>

                                @if ($type === 's3')
                                    <div class="space-y-3 p-4 bg-gray-50 rounded-md">
                                        <div><input type="text" wire:model="s3_bucket" placeholder="Bucket Name"
                                                required class="block w-full rounded-md border-gray-300"></div>
                                        <div><input type="text" wire:model="s3_region"
                                                placeholder="Region (us-east-1)" required
                                                class="block w-full rounded-md border-gray-300"></div>
                                        <div><input type="text" wire:model="s3_access_key" placeholder="Access Key"
                                                required class="block w-full rounded-md border-gray-300"></div>
                                        <div><input type="password" wire:model="s3_secret_key" placeholder="Secret Key"
                                                required class="block w-full rounded-md border-gray-300"></div>
                                        <div><input type="text" wire:model="s3_endpoint"
                                                placeholder="Endpoint (optional)"
                                                class="block w-full rounded-md border-gray-300"></div>
                                        <div><input type="text" wire:model="s3_path" placeholder="Path (backups)"
                                                class="block w-full rounded-md border-gray-300"></div>
                                    </div>
                                @elseif($type === 'ftp' || $type === 'sftp')
                                    <div class="space-y-3 p-4 bg-gray-50 rounded-md">
                                        <div><input type="text" wire:model="ftp_host" placeholder="Host" required
                                                class="block w-full rounded-md border-gray-300"></div>
                                        <div><input type="number" wire:model="ftp_port"
                                                placeholder="Port ({{ $type === 'sftp' ? '22' : '21' }})"
                                                class="block w-full rounded-md border-gray-300"></div>
                                        <div><input type="text" wire:model="ftp_username" placeholder="Username"
                                                required class="block w-full rounded-md border-gray-300"></div>
                                        <div><input type="password" wire:model="ftp_password" placeholder="Password"
                                                required class="block w-full rounded-md border-gray-300"></div>
                                        <div><input type="text" wire:model="ftp_path" placeholder="Path (/backups)"
                                                class="block w-full rounded-md border-gray-300"></div>
                                    </div>
                                @endif

                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="is_default" id="is_default"
                                        class="h-4 w-4 text-blue-600 rounded">
                                    <label for="is_default" class="ml-2 text-sm text-gray-700">Set as default
                                        storage</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="is_active" id="is_active"
                                        class="h-4 w-4 text-blue-600 rounded">
                                    <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 sm:ml-3">
                                Create Location
                            </button>
                            <button type="button" wire:click="closeCreateModal"
                                class="mt-3 sm:mt-0 w-full sm:w-auto px-4 py-2 bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-700 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($showDeleteModal && $selectedLocation)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeDeleteModal"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Delete Storage Location</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Are you sure you want to delete "{{ $selectedLocation->name }}"?
                    </p>
                    <div class="flex gap-2 justify-end">
                        <button wire:click="closeDeleteModal"
                            class="px-4 py-2 bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-700 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            Cancel
                        </button>
                        <button wire:click="deleteLocation"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <livewire:components.alert type="success" :message="session('success')" :dismissible="true" />
    @endif
    @if (session()->has('error'))
        <livewire:components.alert type="error" :message="session('error')" :dismissible="true" />
    @endif

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
        <x-text-input model="search" debounce="300" placeholder="Search roles..." class="w-full max-w-md">
            <x-slot name="icon"><x-icon icon="search" class="h-5 w-5 text-gray-400" /></x-slot>
            <x-slot name="suffix"><x-wire-spinner target="search" /></x-slot>
        </x-text-input>
        <x-button variant="info" wire:click="openCreateModal">
            <x-icon icon="plus" class="-ml-1 mr-1.5 h-5 w-5" />
            Create Role
        </x-button>
    </div>

    {{-- Roles Table --}}
    <x-card>
        <div class="-m-6 overflow-x-auto">
            <table class="table min-w-full">
                <thead class="table-header">
                    <tr>
                        <th class="table-header-cell">Role</th>
                        <th class="table-header-cell">Users</th>
                        <th class="table-header-cell">Permissions</th>
                        <th class="table-header-cell text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    @forelse($roles as $role)
                        <tr class="table-row">
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    </div>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $role->name }}</span>
                                </div>
                            </td>
                            <td class="table-cell">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}
                                </span>
                            </td>
                            <td class="table-cell">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($role->permissions->take(3) as $permission)
                                        <x-badge color="blue" :text="Str::limit($permission->name, 20)" />
                                    @empty
                                        <span class="text-sm text-gray-400 italic">No permissions</span>
                                    @endforelse
                                    @if ($role->permissions->count() > 3)
                                        <x-badge color="gray" :text="'+' . ($role->permissions->count() - 3) . ' more'" />
                                    @endif
                                </div>
                            </td>
                            <td class="table-cell text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button variant="warning" size="sm" outline wire:click="openEditModal({{ $role->id }})">Edit</x-button>
                                    <x-danger-button size="sm" wire:click="openDeleteModal({{ $role->id }})">Delete</x-danger-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state icon="tag" title="No roles found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    @if ($roles->hasPages())
        <div class="mt-4">{{ $roles->links() }}</div>
    @endif

    {{-- Permissions form partial (shared between create & edit) --}}
    @php
        $permissionsForm = function($submitAction, $cancelAction) use ($permissions) {
            return compact('submitAction', 'cancelAction', 'permissions');
        };
    @endphp

    {{-- Create Role Modal --}}
    @if ($showCreateModal)
        <x-modal title="Create New Role" max-width="2xl">
            <form wire:submit.prevent="createRole" class="space-y-4">
                <div>
                    <x-label for="roleName_create" value="Role Name" />
                    <x-input id="roleName_create" type="text" wire:model="roleName" class="mt-1 w-full" />
                    @error('roleName') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-label value="Permissions" />
                    <div class="mt-1 max-h-80 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-4 space-y-4">
                        @foreach ($permissions as $group => $groupPermissions)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white capitalize mb-2">{{ $group }}</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach ($groupPermissions as $permission)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <x-checkbox wire:model="selectedPermissions" value="{{ $permission->id }}" class="text-purple-600" />
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <x-slot name="footer">
                    <x-secondary-button wire:click="resetForm">Cancel</x-secondary-button>
                    <x-button variant="info" type="submit">Create Role</x-button>
                </x-slot>
            </form>
        </x-modal>
    @endif

    {{-- Edit Role Modal --}}
    @if ($showEditModal)
        <x-modal title="Edit Role" max-width="2xl">
            <form wire:submit.prevent="updateRole" class="space-y-4">
                <div>
                    <x-label for="roleName_edit" value="Role Name" />
                    <x-input id="roleName_edit" type="text" wire:model="roleName" class="mt-1 w-full" />
                    @error('roleName') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-label value="Permissions" />
                    <div class="mt-1 max-h-80 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-4 space-y-4">
                        @foreach ($permissions as $group => $groupPermissions)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white capitalize mb-2">{{ $group }}</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach ($groupPermissions as $permission)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <x-checkbox wire:model="selectedPermissions" value="{{ $permission->id }}" class="text-purple-600" />
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <x-slot name="footer">
                    <x-secondary-button wire:click="resetForm">Cancel</x-secondary-button>
                    <x-button variant="info" type="submit">Update Role</x-button>
                </x-slot>
            </form>
        </x-modal>
    @endif

    {{-- Delete Role Modal --}}
    <x-delete-confirm-modal
        :show="$showDeleteModal"
        title="Delete Role"
        message="Are you sure you want to delete this role? This action cannot be undone."
        confirm-action="deleteRole"
        cancel-action="resetForm"
    />
</div>

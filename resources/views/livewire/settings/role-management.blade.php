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
        <x-primary-button wire:click="openCreateModal">
            Create Role
        </x-primary-button>
    </div>

    {{-- Roles Table --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                <thead class="bg-zinc-50 text-left text-xs font-medium uppercase text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400">
                    <tr>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Users</th>
                        <th class="px-5 py-3">Permissions</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                    @forelse($roles as $role)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="font-semibold text-zinc-900 dark:text-white">{{ $role->name }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                {{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($role->permissions->take(3) as $permission)
                                        <span class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                                            {{ Str::limit($permission->name, 20) }}
                                        </span>
                                    @empty
                                        <span class="text-xs italic text-zinc-400">No permissions</span>
                                    @endforelse
                                    @if ($role->permissions->count() > 3)
                                        <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                            +{{ $role->permissions->count() - 3 }} more
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <button wire:click="openEditModal({{ $role->id }})"
                                        class="inline-flex items-center rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-xs font-medium hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800 transition-colors">
                                        Edit
                                    </button>
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800 transition-colors"
                                            aria-label="More options">
                                            <i data-lucide="more-horizontal" class="h-4 w-4 text-zinc-500"></i>
                                        </button>
                                        <div x-show="open"
                                             x-cloak
                                             @click.outside="open = false"
                                             class="absolute right-0 z-20 mt-1 w-44 rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                            <button wire:click="openEditModal({{ $role->id }})"
                                                @click="open = false"
                                                class="block w-full px-3 py-2 text-left text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                                Edit role
                                            </button>
                                            <div class="my-1 h-px bg-zinc-100 dark:bg-zinc-800"></div>
                                            <button wire:click="openDeleteModal({{ $role->id }})"
                                                @click="open = false"
                                                class="block w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/10 transition-colors">
                                                Delete role
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center">
                                <x-empty-state icon="tag" title="No roles found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($roles->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3 dark:border-zinc-800">
                {{ $roles->links() }}
            </div>
        @endif
    </div>

    {{-- Permissions form partial (shared between create & edit) --}}
    @php
        $permissionsForm = function($submitAction, $cancelAction) use ($permissions) {
            return compact('submitAction', 'cancelAction', 'permissions');
        };
    @endphp

    {{-- Create Role Modal --}}
    @if ($showCreateModal)
        <x-modal title="Create New Role" max-width="2xl" closeAction="resetForm">
            <form wire:submit.prevent="createRole" class="space-y-4">
                <div>
                    <x-label for="roleName_create" value="Role Name" />
                    <x-input id="roleName_create" type="text" wire:model="roleName" class="mt-1 w-full" />
                    @error('roleName') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-label value="Permissions" />
                    <div class="mt-1 max-h-80 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-4">
                        @foreach ($permissions as $group => $groupPermissions)
                            <div>
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white capitalize mb-2">{{ $group }}</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach ($groupPermissions as $permission)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <x-checkbox wire:model="selectedPermissions" value="{{ $permission->id }}" class="text-purple-600" />
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <x-slot name="footer">
                    <x-secondary-button wire:click="resetForm">Cancel</x-secondary-button>
                    <x-primary-button type="submit">Create Role</x-primary-button>
                </x-slot>
            </form>
        </x-modal>
    @endif

    {{-- Edit Role Modal --}}
    @if ($showEditModal)
        <x-modal title="Edit Role" max-width="2xl" closeAction="resetForm">
            <form wire:submit.prevent="updateRole" class="space-y-4">
                <div>
                    <x-label for="roleName_edit" value="Role Name" />
                    <x-input id="roleName_edit" type="text" wire:model="roleName" class="mt-1 w-full" />
                    @error('roleName') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-label value="Permissions" />
                    <div class="mt-1 max-h-80 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-4">
                        @foreach ($permissions as $group => $groupPermissions)
                            <div>
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white capitalize mb-2">{{ $group }}</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach ($groupPermissions as $permission)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <x-checkbox wire:model="selectedPermissions" value="{{ $permission->id }}" class="text-purple-600" />
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <x-slot name="footer">
                    <x-secondary-button wire:click="resetForm">Cancel</x-secondary-button>
                    <x-primary-button type="submit">Update Role</x-primary-button>
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

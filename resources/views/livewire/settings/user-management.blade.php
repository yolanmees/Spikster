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
        <x-text-input model="search" debounce="300" placeholder="Search users..." class="w-full max-w-md">
            <x-slot name="icon"><x-icon icon="search" class="h-5 w-5 text-gray-400" /></x-slot>
            <x-slot name="suffix"><x-wire-spinner target="search" /></x-slot>
        </x-text-input>
        <x-primary-button wire:click="openCreateModal">
            <x-icon icon="plus" class="-ml-1 mr-1.5 h-5 w-5" />
            Create User
        </x-primary-button>
    </div>

    {{-- Users Table --}}
    <x-card>
        <div class="-m-6 overflow-x-auto">
            <table class="table min-w-full">
                <thead class="table-header">
                    <tr>
                        <th class="table-header-cell">User</th>
                        <th class="table-header-cell">Email</th>
                        <th class="table-header-cell">Roles</th>
                        <th class="table-header-cell text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    @forelse($users as $user)
                        <tr class="table-row">
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="table-cell">{{ $user->email }}</td>
                            <td class="table-cell">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($user->roles as $role)
                                        <x-badge color="purple" :text="$role->name" />
                                    @empty
                                        <span class="text-sm text-gray-400 italic">No roles</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="table-cell text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button variant="warning" size="sm" outline wire:click="openEditModal({{ $user->id }})">
                                        Edit
                                    </x-button>
                                    @if ($user->id !== auth()->id())
                                        <x-danger-button size="sm" wire:click="openDeleteModal({{ $user->id }})">
                                            Delete
                                        </x-danger-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state icon="user" title="No users found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    </x-card>

    {{-- Create User Modal --}}
    @if ($showCreateModal)
        <x-modal title="Create New User" max-width="lg">
            <form wire:submit.prevent="createUser" class="space-y-4">
                <div>
                    <x-label for="name" value="Name" />
                    <x-input id="name" type="text" wire:model="name" class="mt-1 w-full" />
                    @error('name') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-label for="email" value="Email" />
                    <x-input id="email" type="email" wire:model="email" class="mt-1 w-full" />
                    @error('email') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-label for="password" value="Password" />
                    <x-input id="password" type="password" wire:model="password" class="mt-1 w-full" />
                    @error('password') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-label for="password_confirmation" value="Confirm Password" />
                    <x-input id="password_confirmation" type="password" wire:model="password_confirmation" class="mt-1 w-full" />
                </div>
                <div>
                    <x-label value="Roles" />
                    <div class="mt-1 space-y-2 max-h-40 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                        @foreach ($roles as $role)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <x-checkbox wire:model="selectedRoles" value="{{ $role->id }}" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <x-slot name="footer">
                    <x-secondary-button wire:click="resetForm">Cancel</x-secondary-button>
                    <x-primary-button type="submit">Create User</x-primary-button>
                </x-slot>
            </form>
        </x-modal>
    @endif

    {{-- Edit User Modal --}}
    @if ($showEditModal)
        <x-modal title="Edit User" max-width="lg">
            <form wire:submit.prevent="updateUser" class="space-y-4">
                <div>
                    <x-label for="edit_name" value="Name" />
                    <x-input id="edit_name" type="text" wire:model="name" class="mt-1 w-full" />
                    @error('name') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-label for="edit_email" value="Email" />
                    <x-input id="edit_email" type="email" wire:model="email" class="mt-1 w-full" />
                    @error('email') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-label for="edit_password" value="New Password (leave blank to keep current)" />
                    <x-input id="edit_password" type="password" wire:model="password" class="mt-1 w-full" />
                    @error('password') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
                <div>
                    <x-label for="edit_password_confirmation" value="Confirm Password" />
                    <x-input id="edit_password_confirmation" type="password" wire:model="password_confirmation" class="mt-1 w-full" />
                </div>
                <div>
                    <x-label value="Roles" />
                    <div class="mt-1 space-y-2 max-h-40 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                        @foreach ($roles as $role)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <x-checkbox wire:model="selectedRoles" value="{{ $role->id }}" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <x-slot name="footer">
                    <x-secondary-button wire:click="resetForm">Cancel</x-secondary-button>
                    <x-primary-button type="submit">Update User</x-primary-button>
                </x-slot>
            </form>
        </x-modal>
    @endif

    {{-- Delete User Modal --}}
    <x-delete-confirm-modal
        :show="$showDeleteModal"
        title="Delete User"
        message="Are you sure you want to delete this user? This action cannot be undone."
        confirm-action="deleteUser"
        cancel-action="resetForm"
    />
</div>

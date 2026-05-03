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
    <div class="rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                <thead class="bg-zinc-50 text-left text-xs font-medium uppercase text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400">
                    <tr>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Roles</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($users as $user)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-900 dark:bg-white text-white dark:text-zinc-950 text-xs font-bold">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $user->email }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($user->roles as $role)
                                        <span class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-xs text-zinc-400 italic">No roles</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openEditModal({{ $user->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs font-medium hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                        Edit
                                    </button>
                                    @if ($user->id !== auth()->id())
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
                                                <button wire:click="openEditModal({{ $user->id }})"
                                                    @click="open = false"
                                                    class="block w-full px-3 py-2 text-left text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                                    Edit user
                                                </button>
                                                <div class="my-1 h-px bg-zinc-100 dark:bg-zinc-800"></div>
                                                <button wire:click="openDeleteModal({{ $user->id }})"
                                                    @click="open = false"
                                                    class="block w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/10 transition-colors">
                                                    Delete user
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    </span>
                                    <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">No users found</p>
                                    <p class="text-xs text-zinc-400">Try adjusting your search.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3 dark:border-zinc-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Create User Modal --}}
    @if ($showCreateModal)
        <x-modal title="Create New User" max-width="lg" closeAction="resetForm">
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
                    <div class="mt-1 space-y-2 max-h-40 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg p-3">
                        @foreach ($roles as $role)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <x-checkbox wire:model="selectedRoles" value="{{ $role->id }}" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $role->name }}</span>
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
        <x-modal title="Edit User" max-width="lg" closeAction="resetForm">
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
                    <div class="mt-1 space-y-2 max-h-40 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg p-3">
                        @foreach ($roles as $role)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <x-checkbox wire:model="selectedRoles" value="{{ $role->id }}" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $role->name }}</span>
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

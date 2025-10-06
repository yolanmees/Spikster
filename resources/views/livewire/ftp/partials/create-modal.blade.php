{{-- Create FTP User Modal --}}
<div x-data="{ show: @entangle('showCreateModal') }" x-show="show" x-cloak class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

            <form wire:submit.prevent="createUser">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Create FTP User
                            </h3>
                            <div class="mt-4 space-y-4">
                                {{-- Username --}}
                                <div>
                                    <label for="username" class="block text-sm font-medium text-gray-700">Username
                                        (optional)</label>
                                    <input wire:model="username" type="text" id="username"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="Will auto-generate if empty">
                                    @error('username')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate
                                        username@{{ $site - > domain }}</p>
                                </div>

                                {{-- Password --}}
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">Password
                                        *</label>
                                    <input wire:model="password" type="password" id="password"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        required>
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Password Confirmation --}}
                                <div>
                                    <label for="password_confirmation"
                                        class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                                    <input wire:model="password_confirmation" type="password" id="password_confirmation"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        required>
                                </div>

                                {{-- Home Directory --}}
                                <div>
                                    <label for="home_directory" class="block text-sm font-medium text-gray-700">Home
                                        Directory *</label>
                                    <input wire:model="home_directory" type="text" id="home_directory"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        required>
                                    @error('home_directory')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Quota --}}
                                <div>
                                    <label for="quota_mb" class="block text-sm font-medium text-gray-700">Quota (MB)
                                        *</label>
                                    <input wire:model="quota_mb" type="number" id="quota_mb" min="100"
                                        max="100000"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        required>
                                    @error('quota_mb')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">Min: 100 MB, Max: 100 GB</p>
                                </div>

                                {{-- Max Connections --}}
                                <div>
                                    <label for="max_connections" class="block text-sm font-medium text-gray-700">Max
                                        Connections *</label>
                                    <input wire:model="max_connections" type="number" id="max_connections"
                                        min="1" max="20"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        required>
                                    @error('max_connections')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Bandwidth Limit --}}
                                <div>
                                    <label for="bandwidth_limit_kbps"
                                        class="block text-sm font-medium text-gray-700">Bandwidth Limit (KB/s)</label>
                                    <input wire:model="bandwidth_limit_kbps" type="number" id="bandwidth_limit_kbps"
                                        min="128"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('bandwidth_limit_kbps')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">Leave empty for unlimited</p>
                                </div>

                                {{-- Require SSL --}}
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input wire:model="require_ssl" id="require_ssl" type="checkbox"
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="require_ssl" class="font-medium text-gray-700">Require
                                            SSL/TLS</label>
                                        <p class="text-gray-500">Force encrypted connections (recommended)</p>
                                    </div>
                                </div>

                                {{-- Allowed IP --}}
                                <div>
                                    <label for="allowed_ip" class="block text-sm font-medium text-gray-700">Allowed IP
                                        (optional)</label>
                                    <input wire:model="allowed_ip" type="text" id="allowed_ip"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="e.g., 192.168.1.100">
                                    @error('allowed_ip')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">Restrict access to specific IP address</p>
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <label for="notes"
                                        class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea wire:model="notes" id="notes" rows="3"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                    @error('notes')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Create User
                    </button>
                    <button wire:click="closeModal" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

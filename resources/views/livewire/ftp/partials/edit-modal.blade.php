{{-- Edit FTP User Modal --}}
<div
    x-data="{ open: @entangle('showEditModal') }"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="open = false"
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
>
    <div class="flex min-h-screen items-center justify-center p-4 sm:p-0">
        {{-- Backdrop --}}
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm"
            @click="open = false"
        ></div>

        {{-- Panel --}}
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full sm:max-w-lg transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-black/10 dark:ring-white/10 transition-all sm:my-8"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit FTP User</h3>
                <button type="button" @click="open = false"
                    class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form wire:submit.prevent="updateUser">
                <div class="px-6 py-5 space-y-4 overflow-y-auto max-h-[70vh]">
                    {{-- Home Directory --}}
                    <div>
                        <x-label for="edit_home_directory" value="Home Directory *" />
                        <x-input wire:model="home_directory" type="text" id="edit_home_directory" class="mt-1 w-full" required />
                        @error('home_directory') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Quota --}}
                    <div>
                        <x-label for="edit_quota_mb" value="Quota (MB) *" />
                        <x-input wire:model="quota_mb" type="number" id="edit_quota_mb" min="100" max="100000" class="mt-1 w-full" required />
                        @error('quota_mb') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Max Connections --}}
                    <div>
                        <x-label for="edit_max_connections" value="Max Connections *" />
                        <x-input wire:model="max_connections" type="number" id="edit_max_connections" min="1" max="20" class="mt-1 w-full" required />
                        @error('max_connections') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Bandwidth Limit --}}
                    <div>
                        <x-label for="edit_bandwidth_limit_kbps" value="Bandwidth Limit (KB/s)" />
                        <x-input wire:model="bandwidth_limit_kbps" type="number" id="edit_bandwidth_limit_kbps" min="128" class="mt-1 w-full" />
                        @error('bandwidth_limit_kbps') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Require SSL --}}
                    <div class="flex items-start gap-3">
                        <input wire:model="require_ssl" id="edit_require_ssl" type="checkbox"
                            class="mt-1 h-4 w-4 text-purple-700 border-gray-300 dark:border-gray-600 rounded focus:ring-purple-700">
                        <div>
                            <label for="edit_require_ssl" class="text-sm font-medium text-gray-700 dark:text-gray-300">Require SSL/TLS</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Force encrypted connections</p>
                        </div>
                    </div>

                    {{-- Active Status --}}
                    <div class="flex items-start gap-3">
                        <input wire:model="is_active" id="edit_is_active" type="checkbox"
                            class="mt-1 h-4 w-4 text-purple-700 border-gray-300 dark:border-gray-600 rounded focus:ring-purple-700">
                        <div>
                            <label for="edit_is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Enable or disable this FTP account</p>
                        </div>
                    </div>

                    {{-- Allowed IP --}}
                    <div>
                        <x-label for="edit_allowed_ip" value="Allowed IP" />
                        <x-input wire:model="allowed_ip" type="text" id="edit_allowed_ip" class="mt-1 w-full" />
                        @error('allowed_ip') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Notes --}}
                    <div>
                        <x-label for="edit_notes" value="Notes" />
                        <textarea wire:model="notes" id="edit_notes" rows="3"
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-zinc-500 focus:border-transparent transition-all sm:text-sm"></textarea>
                        @error('notes') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <x-secondary-button wire:click="closeModal" type="button">Cancel</x-secondary-button>
                    <x-primary-button type="submit">Update User</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>

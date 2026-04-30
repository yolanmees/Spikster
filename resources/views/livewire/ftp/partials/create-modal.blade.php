{{-- Create FTP User Modal --}}
<div
    x-data="{ show: @entangle('showCreateModal') }"
    x-show="show"
    x-cloak
    x-on:keydown.escape.window="show = false"
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
>
    <div class="flex min-h-screen items-center justify-center p-4 sm:p-0">
        {{-- Backdrop --}}
        <div
            x-show="show"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"
            @click="show = false"
        ></div>

        {{-- Panel --}}
        <div
            x-show="show"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
            class="relative w-full sm:max-w-lg transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-black/10 dark:ring-white/10 transition-all sm:my-8"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Create FTP User</h3>
                <button type="button" @click="show = false"
                    class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form wire:submit.prevent="createUser">
                <div class="px-6 py-5 space-y-4 overflow-y-auto max-h-[70vh]">
                    <div>
                        <x-label for="ftp_username" value="Username (optional)" />
                        <x-input id="ftp_username" type="text" wire:model="username" class="mt-1 w-full" placeholder="Will auto-generate if empty" />
                        @error('username') <x-input-error :messages="$message" class="mt-1" /> @enderror
                        <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate username@{{ $site->domain }}</p>
                    </div>
                    <div>
                        <x-label for="ftp_password" value="Password *" />
                        <x-input id="ftp_password" type="password" wire:model="password" class="mt-1 w-full" required />
                        @error('password') <x-input-error :messages="$message" class="mt-1" /> @enderror
                    </div>
                    <div>
                        <x-label for="ftp_password_confirmation" value="Confirm Password *" />
                        <x-input id="ftp_password_confirmation" type="password" wire:model="password_confirmation" class="mt-1 w-full" required />
                    </div>
                    <div>
                        <x-label for="ftp_home_directory" value="Home Directory *" />
                        <x-input id="ftp_home_directory" type="text" wire:model="home_directory" class="mt-1 w-full" required />
                        @error('home_directory') <x-input-error :messages="$message" class="mt-1" /> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="ftp_quota_mb" value="Quota (MB) *" />
                            <x-input id="ftp_quota_mb" type="number" wire:model="quota_mb" class="mt-1 w-full" min="100" max="100000" required />
                            @error('quota_mb') <x-input-error :messages="$message" class="mt-1" /> @enderror
                            <p class="mt-1 text-xs text-gray-500">Min: 100 MB, Max: 100 GB</p>
                        </div>
                        <div>
                            <x-label for="ftp_max_connections" value="Max Connections *" />
                            <x-input id="ftp_max_connections" type="number" wire:model="max_connections" class="mt-1 w-full" min="1" max="20" required />
                            @error('max_connections') <x-input-error :messages="$message" class="mt-1" /> @enderror
                        </div>
                    </div>
                    <div>
                        <x-label for="ftp_bandwidth" value="Bandwidth Limit (KB/s)" />
                        <x-input id="ftp_bandwidth" type="number" wire:model="bandwidth_limit_kbps" class="mt-1 w-full" min="128" />
                        @error('bandwidth_limit_kbps') <x-input-error :messages="$message" class="mt-1" /> @enderror
                        <p class="mt-1 text-xs text-gray-500">Leave empty for unlimited</p>
                    </div>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <x-checkbox wire:model="require_ssl" id="ftp_require_ssl" class="mt-0.5" />
                        <div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Require SSL/TLS</span>
                            <p class="text-xs text-gray-500">Force encrypted connections (recommended)</p>
                        </div>
                    </label>
                    <div>
                        <x-label for="ftp_allowed_ip" value="Allowed IP (optional)" />
                        <x-input id="ftp_allowed_ip" type="text" wire:model="allowed_ip" class="mt-1 w-full" placeholder="e.g., 192.168.1.100" />
                        @error('allowed_ip') <x-input-error :messages="$message" class="mt-1" /> @enderror
                        <p class="mt-1 text-xs text-gray-500">Restrict access to a specific IP address</p>
                    </div>
                    <div>
                        <x-label for="ftp_notes" value="Notes" />
                        <textarea id="ftp_notes" wire:model="notes" rows="2"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                        @error('notes') <x-input-error :messages="$message" class="mt-1" /> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <x-secondary-button wire:click="closeModal" type="button">Cancel</x-secondary-button>
                    <x-primary-button type="submit">Create User</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>

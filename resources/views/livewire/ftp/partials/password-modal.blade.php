{{-- Reset Password Modal --}}
<div
    x-data="{ open: @entangle('showPasswordModal') }"
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reset FTP Password</h3>
                <button type="button" @click="open = false"
                    class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form wire:submit.prevent="resetPassword">
                <div class="px-6 py-5 space-y-4">
                    {{-- New Password --}}
                    <div>
                        <x-label for="reset_password" value="New Password *" />
                        <x-input wire:model="password" type="password" id="reset_password" class="mt-1 w-full" required />
                        @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Minimum 8 characters</p>
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <x-label for="reset_password_confirmation" value="Confirm Password *" />
                        <x-input wire:model="password_confirmation" type="password" id="reset_password_confirmation" class="mt-1 w-full" required />
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <x-secondary-button wire:click="closeModal" type="button">Cancel</x-secondary-button>
                    <x-primary-button type="submit">Reset Password</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>

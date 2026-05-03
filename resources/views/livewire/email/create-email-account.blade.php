<div>
    <form wire:submit="save" class="space-y-5">
        {{-- Username --}}
        <div>
            <x-label for="username" value="Username" required />
            <div class="mt-1 flex rounded-md shadow-sm">
                <x-input wire:model="username" type="text" id="username" autocomplete="off"
                    placeholder="contact"
                    class="flex-1 min-w-0 rounded-r-none border-r-0" />
                <span class="inline-flex items-center px-3 rounded-r-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 sm:text-sm">
                    @{{ $site->domain }}
                </span>
            </div>
            @error('username') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Only letters, numbers, dots, underscores and hyphens.</p>
        </div>

        {{-- Password --}}
        <div>
            <x-label for="password" value="Password" required />
            <x-input wire:model="password" type="password" id="password" autocomplete="new-password" class="mt-1 block w-full" />
            @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Password Confirmation --}}
        <div>
            <x-label for="password_confirmation" value="Confirm Password" required />
            <x-input wire:model="password_confirmation" type="password" id="password_confirmation" autocomplete="new-password" class="mt-1 block w-full" />
            @error('password_confirmation') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Quota --}}
        <div>
            <x-label for="quota_mb" value="Mailbox Quota (MB)" required />
            <x-input wire:model="quota_mb" type="number" id="quota_mb" min="100" max="10240" step="100" class="mt-1 block w-full" />
            @error('quota_mb') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Current: {{ number_format($quota_mb) }} MB ({{ number_format($quota_mb / 1024, 2) }} GB)
            </p>
        </div>

        {{-- Options --}}
        <div class="space-y-3 pt-2">
            <label class="flex items-start gap-3 cursor-pointer">
                <input wire:model="enable_spam_filter" type="checkbox" id="enable_spam_filter"
                    class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-purple-700 focus:ring-purple-700">
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Spam Filter</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Use SpamAssassin to filter spam emails.</p>
                </div>
            </label>

            <label class="flex items-start gap-3 cursor-pointer">
                <input wire:model="enable_virus_scan" type="checkbox" id="enable_virus_scan"
                    class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-purple-700 focus:ring-purple-700">
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Virus Scanning</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Use ClamAV to scan attachments for viruses.</p>
                </div>
            </label>

            <label class="flex items-start gap-3 cursor-pointer">
                <input wire:model="is_active" type="checkbox" id="is_active"
                    class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-purple-700 focus:ring-purple-700">
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Email account can send and receive emails.</p>
                </div>
            </label>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
            <x-secondary-button type="button" wire:click="$parent.closeModals">Cancel</x-secondary-button>
            <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Create Account</span>
                <span wire:loading wire:target="save" class="inline-flex items-center gap-1.5">
                    <x-wire-spinner size="sm" /> Creating...
                </span>
            </x-primary-button>
        </div>
    </form>
</div>

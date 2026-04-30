<div>
    <form wire:submit="save" class="space-y-5">
        {{-- Password (Optional) --}}
        <div>
            <x-label for="password" value="New Password (leave blank to keep current)" />
            <x-input wire:model="password" type="password" id="password" autocomplete="new-password" class="mt-1 block w-full" />
            @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Password Confirmation --}}
        @if ($password)
            <div>
                <x-label for="password_confirmation" value="Confirm New Password" />
                <x-input wire:model="password_confirmation" type="password" id="password_confirmation" autocomplete="new-password" class="mt-1 block w-full" />
                @error('password_confirmation') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
        @endif

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
                    class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Spam Filter</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Use SpamAssassin to filter spam emails.</p>
                </div>
            </label>

            <label class="flex items-start gap-3 cursor-pointer">
                <input wire:model="enable_virus_scan" type="checkbox" id="enable_virus_scan"
                    class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Virus Scanning</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Use ClamAV to scan attachments for viruses.</p>
                </div>
            </label>

            <label class="flex items-start gap-3 cursor-pointer">
                <input wire:model="is_active" type="checkbox" id="is_active"
                    class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
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
                <span wire:loading.remove wire:target="save">Update Account</span>
                <span wire:loading wire:target="save" class="inline-flex items-center gap-1.5">
                    <x-wire-spinner size="sm" /> Updating...
                </span>
            </x-primary-button>
        </div>
    </form>
</div>

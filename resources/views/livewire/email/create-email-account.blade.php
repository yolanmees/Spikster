<div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
    <!-- Header -->
    <div class="mb-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
            Create Email Account
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Create a new email account for {{ $site->domain }}
        </p>
    </div>

    <!-- Form -->
    <form wire:submit="save" class="space-y-6">
        <!-- Username -->
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Username <span class="text-red-500">*</span>
            </label>
            <div class="mt-1 flex rounded-md shadow-sm">
                <input wire:model="username" type="text" id="username" name="username" autocomplete="off"
                    class="flex-1 min-w-0 block w-full px-3 py-2 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="contact">
                <span
                    class="inline-flex items-center px-3 rounded-r-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 sm:text-sm">
                    @{{ $site - > domain }}
                </span>
            </div>
            @error('username')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Only letters, numbers, dots, underscores and hyphens are allowed.
            </p>
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Password <span class="text-red-500">*</span>
            </label>
            <input wire:model="password" type="password" id="password" name="password" autocomplete="new-password"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            @error('password')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Confirmation -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Confirm Password <span class="text-red-500">*</span>
            </label>
            <input wire:model="password_confirmation" type="password" id="password_confirmation"
                name="password_confirmation" autocomplete="new-password"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            @error('password_confirmation')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Quota -->
        <div>
            <label for="quota_mb" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Mailbox Quota (MB) <span class="text-red-500">*</span>
            </label>
            <input wire:model="quota_mb" type="number" id="quota_mb" name="quota_mb" min="100" max="10240"
                step="100"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            @error('quota_mb')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Current: {{ number_format($quota_mb) }} MB ({{ number_format($quota_mb / 1024, 2) }} GB)
            </p>
        </div>

        <!-- Options -->
        <div class="space-y-4">
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input wire:model="enable_spam_filter" type="checkbox" id="enable_spam_filter"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="enable_spam_filter" class="font-medium text-gray-700 dark:text-gray-300">Enable Spam
                        Filter</label>
                    <p class="text-gray-500 dark:text-gray-400">Use SpamAssassin to filter spam emails.</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input wire:model="enable_virus_scan" type="checkbox" id="enable_virus_scan"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="enable_virus_scan" class="font-medium text-gray-700 dark:text-gray-300">Enable Virus
                        Scanning</label>
                    <p class="text-gray-500 dark:text-gray-400">Use ClamAV to scan attachments for viruses.</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input wire:model="is_active" type="checkbox" id="is_active"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="is_active" class="font-medium text-gray-700 dark:text-gray-300">Active</label>
                    <p class="text-gray-500 dark:text-gray-400">Email account can send and receive emails.</p>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Footer -->
<div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
    <button wire:click="save" type="button" wire:loading.attr="disabled" wire:target="save"
        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
        <span wire:loading.remove wire:target="save">Create Account</span>
        <span wire:loading wire:target="save" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            Creating...
        </span>
    </button>
    <button wire:click="$parent.closeModals" type="button"
        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
        Cancel
    </button>
</div>

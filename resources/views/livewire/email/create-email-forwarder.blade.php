<div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Create Email Forwarder</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Forward emails from one address to another
        </p>
    </div>

    <form wire:submit="save" class="space-y-6">
        <!-- Catch-All Toggle -->
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input wire:model.live="is_catch_all" type="checkbox" id="is_catch_all"
                    class="focus:ring-purple-700 h-4 w-4 text-purple-700 border-gray-300 dark:border-gray-600 rounded">
            </div>
            <div class="ml-3 text-sm">
                <label for="is_catch_all" class="font-medium text-gray-700 dark:text-gray-300">Catch-All
                    Forwarder</label>
                <p class="text-gray-500 dark:text-gray-400">Forward all emails to non-existent addresses</p>
            </div>
        </div>

        <!-- Source -->
        @if (!$is_catch_all)
            <div>
                <label for="source" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Source <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input wire:model="source" type="text" id="source"
                        class="flex-1 min-w-0 block w-full px-3 py-2 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-purple-700 focus:border-purple-700 sm:text-sm"
                        placeholder="info">
                    <span
                        class="inline-flex items-center px-3 rounded-r-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 sm:text-sm">
                        @{{ $site - > domain }}
                    </span>
                </div>
                @error('source')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        @else
            <div class="bg-zinc-100 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-zinc-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-zinc-700 dark:text-zinc-300">
                            Catch-all forwarder will forward all emails sent to non-existent addresses on this domain.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Destination -->
        <div>
            <label for="destination" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Destination <span class="text-red-500">*</span>
            </label>
            <textarea wire:model="destination" id="destination" rows="3"
                class="mt-1 w-full"
                placeholder="john@example.com, jane@example.com"></textarea>
            @error('destination')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Separate multiple addresses with commas
            </p>
        </div>

        <!-- Keep Copy -->
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input wire:model="keep_copy" type="checkbox" id="keep_copy"
                    class="focus:ring-purple-700 h-4 w-4 text-purple-700 border-gray-300 dark:border-gray-600 rounded">
            </div>
            <div class="ml-3 text-sm">
                <label for="keep_copy" class="font-medium text-gray-700 dark:text-gray-300">Keep a Copy</label>
                <p class="text-gray-500 dark:text-gray-400">Keep a copy in the original mailbox (if exists)</p>
            </div>
        </div>
    </form>
</div>

<div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
    <button wire:click="save" type="button" wire:loading.attr="disabled" wire:target="save"
        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-700 text-base font-medium text-white hover:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-700 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
        <span wire:loading.remove wire:target="save">Create Forwarder</span>
        <span wire:loading wire:target="save">Creating...</span>
    </button>
    <button wire:click="$parent.closeModals" type="button"
        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
        Cancel
    </button>
</div>

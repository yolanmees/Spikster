<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <!-- Header -->
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Auto-Responder (Vacation Message)</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Automatically reply to incoming emails for {{ $account->email }}
        </p>
    </div>

    @if ($autoresponder && $autoresponder->is_active)
        <!-- Active Autoresponder -->
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Autoresponder Active</h3>
                    <div class="mt-2 text-sm text-green-700 dark:text-green-400">
                        <p><strong>Subject:</strong> {{ $autoresponder->subject }}</p>
                        @if ($autoresponder->start_date)
                            <p class="mt-1">
                                <strong>Period:</strong>
                                {{ $autoresponder->start_date->format('M d, Y') }}
                                @if ($autoresponder->end_date)
                                    - {{ $autoresponder->end_date->format('M d, Y') }}
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button wire:click="showModal" type="button"
                            class="text-sm text-green-700 dark:text-green-300 hover:text-green-900 dark:hover:text-green-100 font-medium">
                            Edit
                        </button>
                        <button wire:click="disable" wire:confirm="Are you sure you want to disable the autoresponder?"
                            type="button"
                            class="text-sm text-red-700 dark:text-red-300 hover:text-red-900 dark:hover:text-red-100 font-medium">
                            Disable
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- No Active Autoresponder -->
        <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-6 mb-6">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No autoresponder active</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Set up an automatic reply for when you're away
                </p>
                <div class="mt-6">
                    <button wire:click="showModal" type="button"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Create Autoresponder
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div wire:click="closeModals" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:w-full sm:max-w-2xl">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            {{ $autoresponder ? 'Edit' : 'Create' }} Autoresponder
                        </h3>

                        <form wire:submit="save" class="space-y-4">
                            <!-- Subject -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Subject <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="subject" type="text"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('subject')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Message -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Message <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="message" rows="6"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                @error('message')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ strlen($message) }}/5000 characters
                                </p>
                            </div>

                            <!-- Date Range -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Start Date <span class="text-red-500">*</span>
                                    </label>
                                    <input wire:model="start_date" type="date"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('start_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        End Date (Optional)
                                    </label>
                                    <input wire:model="end_date" type="date"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('end_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Active -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input wire:model="is_active" type="checkbox" id="is_active"
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_active" class="font-medium text-gray-700 dark:text-gray-300">
                                        Active
                                    </label>
                                    <p class="text-gray-500 dark:text-gray-400">Enable this autoresponder</p>
                                </div>
                            </div>

                            <div class="flex gap-2 justify-end pt-4">
                                <button wire:click="closeModals" type="button"
                                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    Cancel
                                </button>
                                <button type="submit" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                                    <span wire:loading.remove wire:target="save">Save</span>
                                    <span wire:loading wire:target="save">Saving...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

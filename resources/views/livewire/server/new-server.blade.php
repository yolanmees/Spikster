<div>
    @script
    <script>
        $wire.on('close-modal', () => {
            $('#newServerModal').modal('hide');
        });
    </script>
    @endscript

    <form wire:submit="submit">
        <div class="space-y-6">
            <!-- Flash Messages -->
            @if (session()->has('success'))
                <livewire:components.alert type="success" :message="session('success')" :dismissible="true" />
            @endif

            @if (session()->has('error'))
                <livewire:components.alert type="error" :message="session('error')" :dismissible="true" />
            @endif

            <!-- Server Details -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Server Name -->
                <div>
                    <label for="serverName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Server Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="serverName" wire:model="serverName" placeholder="e.g. Production Server"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm @error('serverName') border-red-300 @enderror"
                        autocomplete="off">
                    @error('serverName')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Server IP -->
                <div>
                    <label for="serverIp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Server IP <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="serverIp" wire:model="serverIp" placeholder="e.g. 123.45.67.89"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm @error('serverIp') border-red-300 @enderror"
                        autocomplete="off">
                    @error('serverIp')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Provider -->
                <div>
                    <label for="serverProvider" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Server Provider <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="serverProvider" wire:model="serverProvider"
                        placeholder="e.g. DigitalOcean, AWS, Hetzner"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm @error('serverProvider') border-red-300 @enderror"
                        autocomplete="off">
                    @error('serverProvider')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location -->
                <div>
                    <label for="serverLocation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Server Location
                    </label>
                    <input type="text" id="serverLocation" wire:model="serverLocation"
                        placeholder="e.g. Amsterdam, Frankfurt"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm @error('serverLocation') border-red-300 @enderror"
                        autocomplete="off">
                    @error('serverLocation')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- SSH Configuration -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">SSH Configuration</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- SSH Port -->
                    <div>
                        <label for="serverSshPort" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            SSH Port <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="serverSshPort" wire:model="serverSshPort" min="1"
                            max="65535"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm @error('serverSshPort') border-red-300 @enderror"
                            autocomplete="off">
                        @error('serverSshPort')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default: 22</p>
                    </div>

                    <!-- SSH Password -->
                    <div>
                        <label for="serverSshPassword"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            SSH Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="serverSshPassword" wire:model="serverSshPassword"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm @error('serverSshPassword') border-red-300 @enderror"
                            autocomplete="new-password">
                        @error('serverSshPassword')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Minimum 8 characters</p>
                    </div>
                </div>
            </div>

            <!-- API Configuration (Optional) -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">API Configuration (optional)</h3>
                <div>
                    <label for="serverApiKey" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        API Key
                    </label>
                    <input type="text" id="serverApiKey" wire:model="serverApiKey"
                        placeholder="For automatic server management"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm @error('serverApiKey') border-red-300 @enderror"
                        autocomplete="off">
                    @error('serverApiKey')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        For DigitalOcean, AWS, etc. automation
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 border-t border-gray-200 dark:border-gray-700 pt-6">
                <button type="button" wire:click="resetForm"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    :disabled="isSubmitting">
                    Reset
                </button>

                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">
                        Confirm
                    </span>
                    <span wire:loading wire:target="submit" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Creating server...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>

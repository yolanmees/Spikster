<div>
    <form wire:submit="submit">
        <div class="space-y-6">
            @if (session()->has('error'))
                <livewire:components.alert type="error" :message="session('error')" :dismissible="true" />
            @endif

            {{-- Server Details --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <x-label for="serverName" value="Server Name" required />
                    <x-input type="text" id="serverName" wire:model="serverName" placeholder="e.g. Production Server"
                        class="mt-1 block w-full @error('serverName') border-red-300 @enderror" autocomplete="off" />
                    @error('serverName') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-label for="serverIp" value="Server IP" required />
                    <x-input type="text" id="serverIp" wire:model="serverIp" placeholder="e.g. 123.45.67.89"
                        class="mt-1 block w-full font-mono @error('serverIp') border-red-300 @enderror" autocomplete="off" />
                    @error('serverIp') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-label for="serverProvider" value="Server Provider" required />
                    <x-input type="text" id="serverProvider" wire:model="serverProvider" placeholder="e.g. DigitalOcean, AWS, Hetzner"
                        class="mt-1 block w-full @error('serverProvider') border-red-300 @enderror" autocomplete="off" />
                    @error('serverProvider') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-label for="serverLocation" value="Server Location" />
                    <x-input type="text" id="serverLocation" wire:model="serverLocation" placeholder="e.g. Amsterdam, Frankfurt"
                        class="mt-1 block w-full @error('serverLocation') border-red-300 @enderror" autocomplete="off" />
                    @error('serverLocation') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- SSH Configuration --}}
            <div class="border-t border-gray-200 dark:border-gray-700 pt-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">SSH Configuration</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <x-label for="serverSshPort" value="SSH Port" required />
                        <x-input type="number" id="serverSshPort" wire:model="serverSshPort" min="1" max="65535"
                            class="mt-1 block w-full @error('serverSshPort') border-red-300 @enderror" autocomplete="off" />
                        @error('serverSshPort') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default: 22</p>
                    </div>

                    <div>
                        <x-label for="serverSshPassword" value="SSH Password" required />
                        <x-input type="password" id="serverSshPassword" wire:model="serverSshPassword"
                            class="mt-1 block w-full @error('serverSshPassword') border-red-300 @enderror" autocomplete="new-password" />
                        @error('serverSshPassword') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Minimum 8 characters</p>
                    </div>
                </div>
            </div>

            {{-- API Configuration --}}
            <div class="border-t border-gray-200 dark:border-gray-700 pt-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">API Configuration <span class="font-normal text-gray-500 dark:text-gray-400">(optional)</span></h3>
                <div>
                    <x-label for="serverApiKey" value="API Key" />
                    <x-input type="text" id="serverApiKey" wire:model="serverApiKey" placeholder="For automatic server management"
                        class="mt-1 block w-full @error('serverApiKey') border-red-300 @enderror" autocomplete="off" />
                    @error('serverApiKey') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">For DigitalOcean, AWS, etc. automation</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-5">
                <x-secondary-button type="button" wire:click="resetForm">Reset</x-secondary-button>
                <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Confirm</span>
                    <span wire:loading wire:target="submit" class="inline-flex items-center gap-1.5">
                        <x-wire-spinner size="sm" /> Creating server...
                    </span>
                </x-primary-button>
            </div>
        </div>
    </form>
</div>

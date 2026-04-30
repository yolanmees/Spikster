<div>
    <form wire:submit="submit">
        <div class="space-y-5">
            {{-- Flash Messages --}}
            @if (session()->has('success'))
                <livewire:components.alert type="success" :message="session('success')" :dismissible="true" />
            @endif
            @if (session()->has('error'))
                <livewire:components.alert type="error" :message="session('error')" :dismissible="true" />
            @endif

            {{-- Server Name --}}
            <div>
                <x-label for="serverName" :value="__('spikster.server_name')" />
                <x-input type="text" id="serverName" wire:model.defer="serverName" placeholder="e.g. Production"
                    class="mt-1 block w-full @error('serverName') border-red-400 @enderror" autocomplete="off" />
                @error('serverName')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Server IP --}}
            <div>
                <x-label for="serverIp" :value="__('spikster.server_ip')" />
                <x-input type="text" id="serverIp" wire:model.defer="serverIp" placeholder="e.g. 123.123.123.123"
                    class="mt-1 block w-full font-mono @error('serverIp') border-red-400 @enderror" autocomplete="off" />
                @error('serverIp')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Server Provider --}}
            <div>
                <x-label for="serverProvider" :value="__('spikster.server_provider')" />
                <x-input type="text" id="serverProvider" wire:model.defer="serverProvider" placeholder="e.g. Digital Ocean"
                    class="mt-1 block w-full @error('serverProvider') border-red-400 @enderror" autocomplete="off" />
                @error('serverProvider')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Server Location --}}
            <div>
                <x-label for="serverLocation" :value="__('spikster.server_location')" />
                <x-input type="text" id="serverLocation" wire:model.defer="serverLocation" placeholder="e.g. Amsterdam"
                    class="mt-1 block w-full @error('serverLocation') border-red-400 @enderror" autocomplete="off" />
                @error('serverLocation')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="pt-2">
                <x-primary-button type="submit" class="w-full justify-center" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit" class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('spikster.update') }}
                    </span>
                    <span wire:loading wire:target="submit" class="inline-flex items-center gap-2">
                        <x-wire-spinner size="sm" /> Updating...
                    </span>
                </x-primary-button>
            </div>
        </div>
    </form>
</div>

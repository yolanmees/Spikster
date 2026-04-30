<div>
    <x-flash-messages />

    <x-card>
        <x-slot name="header">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">Ban or Whitelist IP Address</span>
        </x-slot>

        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
            Manually ban or whitelist an IP address. Banned IPs will be blocked from accessing the server.
        </p>

        <form wire:submit="submit" class="space-y-5">
            {{-- Action Type --}}
            <div>
                <x-label value="Action" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Choose whether to ban or whitelist the IP address</p>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model.live="action" type="radio" value="ban"
                            class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ban IP</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model.live="action" type="radio" value="whitelist"
                            class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Whitelist IP</span>
                    </label>
                </div>
            </div>

            {{-- IP Address --}}
            <div>
                <x-label for="ip" value="IP Address" />
                <div class="mt-1 flex gap-2">
                    <x-input wire:model.live="ip" type="text" id="ip" placeholder="192.168.1.100" class="flex-1" />
                    <x-secondary-button type="button" wire:click="checkIpStatus" wire:loading.attr="disabled" wire:target="checkIpStatus">
                        <span wire:loading.remove wire:target="checkIpStatus">Check Status</span>
                        <span wire:loading wire:target="checkIpStatus"><x-wire-spinner size="sm" /></span>
                    </x-secondary-button>
                </div>
                @error('ip') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Jail Selection --}}
            @if ($action === 'ban')
                <div>
                    <x-label for="jail" value="Jail" />
                    <x-select model="jail" id="jail" class="mt-1 block w-full">
                        @if (!empty($jails))
                            @foreach ($jails as $jailOption)
                                <option value="{{ $jailOption['name'] }}">{{ $jailOption['name'] }}</option>
                            @endforeach
                        @else
                            <option value="sshd">sshd</option>
                            <option value="nginx-http-auth">nginx-http-auth</option>
                            <option value="nginx-noscript">nginx-noscript</option>
                        @endif
                    </x-select>
                    @error('jail') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Select the Fail2ban jail to ban the IP in.</p>
                </div>
            @endif

            {{-- Submit --}}
            <div class="pt-2">
                @if ($action === 'ban')
                    <x-danger-button type="submit" wire:loading.attr="disabled" wire:target="submit">
                        <span wire:loading.remove wire:target="submit" class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            Ban IP Address
                        </span>
                        <span wire:loading wire:target="submit" class="inline-flex items-center gap-1.5"><x-wire-spinner size="sm" /> Banning...</span>
                    </x-danger-button>
                @else
                    <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="submit" class="bg-green-600 hover:bg-green-700 focus:ring-green-500">
                        <span wire:loading.remove wire:target="submit" class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Whitelist IP Address
                        </span>
                        <span wire:loading wire:target="submit" class="inline-flex items-center gap-1.5"><x-wire-spinner size="sm" /> Whitelisting...</span>
                    </x-primary-button>
                @endif
            </div>
        </form>
    </x-card>
</div>

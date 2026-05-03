<x-action-section>
    <x-slot name="title">
        {{ __('Browser Sessions') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Manage and log out your active sessions on other browsers and devices.') }}
    </x-slot>

    <x-slot name="content">
        <div class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.') }}
        </div>

        @if (count($this->sessions) > 0)
            <div class="space-y-3">
                @foreach ($this->sessions as $session)
                    <div
                        class="flex items-center gap-4 p-4 rounded-lg border {{ $session->is_current_device ? 'bg-zinc-100 dark:bg-zinc-800/50 border-zinc-200 dark:border-zinc-600' : 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600' }}">
                        <!-- Device Icon -->
                        <div class="flex-shrink-0">
                            @if ($session->agent->isDesktop())
                                <div
                                    class="w-12 h-12 rounded-lg {{ $session->is_current_device ? 'bg-zinc-100 dark:bg-zinc-800/50' : 'bg-gray-200 dark:bg-gray-600' }} flex items-center justify-center">
                                    <svg class="w-6 h-6 {{ $session->is_current_device ? 'text-zinc-600 dark:text-zinc-400' : 'text-gray-600 dark:text-gray-400' }}"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @else
                                <div
                                    class="w-12 h-12 rounded-lg {{ $session->is_current_device ? 'bg-zinc-100 dark:bg-zinc-800/50' : 'bg-gray-200 dark:bg-gray-600' }} flex items-center justify-center">
                                    <svg class="w-6 h-6 {{ $session->is_current_device ? 'text-zinc-600 dark:text-zinc-400' : 'text-gray-600 dark:text-gray-400' }}"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Session Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $session->agent->platform() ? $session->agent->platform() : 'Unknown' }} -
                                    {{ $session->agent->browser() ? $session->agent->browser() : 'Unknown' }}
                                </p>
                                @if ($session->is_current_device)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 dark:bg-zinc-800/50 text-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-600">
                                        {{ __('This device') }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                    {{ $session->ip_address }}
                                </p>
                                @if (!$session->is_current_device)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">•</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ __('Last active') }} {{ $session->last_active }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <x-button variant="danger" wire:click="confirmLogout" wire:loading.attr="disabled">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                {{ __('Log Out Other Browser Sessions') }}
            </x-button>

            <x-action-message class="ml-3" on="loggedOut">
                {{ __('Done.') }}
            </x-action-message>
        </div>

        <!-- Log Out Other Devices Confirmation Modal -->
        <x-dialog-modal wire:model="confirmingLogout">
            <x-slot name="title">
                {{ __('Log Out Other Browser Sessions') }}
            </x-slot>

            <x-slot name="content">
                {{ __('Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.') }}

                <div class="mt-4" x-data="{}"
                    x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-input type="password" class="mt-1 block w-full" placeholder="{{ __('Password') }}"
                        x-ref="password" wire:model.defer="password" wire:keydown.enter="logoutOtherBrowserSessions" />

                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingLogout')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-button variant="danger" wire:click="logoutOtherBrowserSessions" wire:loading.attr="disabled">
                    {{ __('Log Out Other Browser Sessions') }}
                </x-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>

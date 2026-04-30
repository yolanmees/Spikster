{{-- Connection Info Modal --}}
@php
    $ftpUser = $selectedUserId ? \App\Models\FtpUser::find($selectedUserId) : null;
    $connectionInfo = $ftpUser ? app(\App\Services\FtpService::class)->getConnectionInfo($ftpUser) : null;
@endphp

<div
    x-data="{ open: @entangle('showConnectionInfoModal') }"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="open = false"
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
>
    <div class="flex min-h-screen items-center justify-center p-4 sm:p-0">
        {{-- Backdrop --}}
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm"
            @click="open = false"
        ></div>

        {{-- Panel --}}
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full sm:max-w-3xl transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-black/10 dark:ring-white/10 transition-all sm:my-8"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">FTP Connection Information</h3>
                <button type="button" @click="open = false"
                    class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @if ($ftpUser && $connectionInfo)
                {{-- Body --}}
                <div class="px-6 py-5 space-y-4 overflow-y-auto max-h-[70vh]">
                    {{-- Connection Details --}}
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Server</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $connectionInfo['host'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Port</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $connectionInfo['port'] }}
                                    @if ($ftpUser->require_ssl)
                                        <x-badge color="green" text="FTPS" class="ml-1" />
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Username</p>
                                <p class="mt-1 text-sm font-mono text-gray-900 dark:text-white">{{ $connectionInfo['username'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Protocol</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $connectionInfo['protocol'] }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- FileZilla Configuration --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">FileZilla Configuration</h4>
                        <div class="bg-gray-900 rounded-lg p-4 font-mono text-xs text-green-400 overflow-x-auto">
                            <pre>{{ $connectionInfo['filezilla_config'] }}</pre>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Copy this configuration to FileZilla (File → Import)</p>
                    </div>

                    {{-- WinSCP Configuration --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">WinSCP Configuration</h4>
                        <div class="bg-gray-900 rounded-lg p-4 font-mono text-xs text-green-400 overflow-x-auto">
                            <pre>{{ $connectionInfo['winscp_config'] }}</pre>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Copy this configuration to WinSCP</p>
                    </div>

                    {{-- Terminal Commands --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Terminal Commands</h4>
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">lftp:</p>
                                <div class="bg-gray-900 rounded-lg p-3 font-mono text-xs text-green-400 overflow-x-auto">
                                    <code>{{ $connectionInfo['terminal_commands']['lftp'] }}</code>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">curl:</p>
                                <div class="bg-gray-900 rounded-lg p-3 font-mono text-xs text-green-400 overflow-x-auto">
                                    <code>{{ $connectionInfo['terminal_commands']['curl'] }}</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Tips --}}
                    <x-alert type="info">
                        <ul class="space-y-1 list-disc list-inside text-xs">
                            @if ($ftpUser->require_ssl)
                                <li>This account requires SSL/TLS encryption</li>
                            @else
                                <li>This account does not require SSL/TLS (not recommended)</li>
                            @endif
                            <li>Use passive mode for NAT/firewall compatibility</li>
                            <li>Your quota is {{ $ftpUser->getFormattedQuota() }} ({{ $ftpUser->getFormattedUsage() }} used)</li>
                            @if ($ftpUser->allowed_ip)
                                <li>IP restriction: Only {{ $ftpUser->allowed_ip }} can connect</li>
                            @endif
                        </ul>
                    </x-alert>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <x-secondary-button wire:click="closeModal" type="button">Close</x-secondary-button>
                </div>
            @endif
        </div>
    </div>
</div>

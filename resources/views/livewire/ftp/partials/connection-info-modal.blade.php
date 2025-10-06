{{-- Connection Info Modal --}}
@php
    $ftpUser = $selectedUserId ? \App\Models\FtpUser::find($selectedUserId) : null;
    $connectionInfo = $ftpUser ? app(\App\Services\FtpService::class)->getConnectionInfo($ftpUser) : null;
@endphp

<div x-data="{ show: @entangle('showConnectionInfoModal') }" x-show="show" x-cloak class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">

            @if ($ftpUser && $connectionInfo)
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                FTP Connection Information
                            </h3>

                            {{-- Connection Details --}}
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Server</p>
                                        <p class="mt-1 text-sm text-gray-900">{{ $connectionInfo['host'] }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Port</p>
                                        <p class="mt-1 text-sm text-gray-900">{{ $connectionInfo['port'] }} @if ($ftpUser->require_ssl)
                                                <span class="text-green-600">(FTPS)</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Username</p>
                                        <p class="mt-1 text-sm text-gray-900 font-mono">
                                            {{ $connectionInfo['username'] }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Protocol</p>
                                        <p class="mt-1 text-sm text-gray-900">{{ $connectionInfo['protocol'] }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- FileZilla Configuration --}}
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">FileZilla Configuration</h4>
                                <div class="bg-gray-900 rounded-lg p-4 text-white font-mono text-xs overflow-x-auto">
                                    <pre>{{ $connectionInfo['filezilla_config'] }}</pre>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Copy this configuration to FileZilla (File →
                                    Import)</p>
                            </div>

                            {{-- WinSCP Configuration --}}
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">WinSCP Configuration</h4>
                                <div class="bg-gray-900 rounded-lg p-4 text-white font-mono text-xs overflow-x-auto">
                                    <pre>{{ $connectionInfo['winscp_config'] }}</pre>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Copy this configuration to WinSCP</p>
                            </div>

                            {{-- Terminal Commands --}}
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Terminal Commands</h4>
                                <div class="space-y-2">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">lftp:</p>
                                        <div
                                            class="bg-gray-900 rounded-lg p-3 text-white font-mono text-xs overflow-x-auto">
                                            <code>{{ $connectionInfo['terminal_commands']['lftp'] }}</code>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">curl:</p>
                                        <div
                                            class="bg-gray-900 rounded-lg p-3 text-white font-mono text-xs overflow-x-auto">
                                            <code>{{ $connectionInfo['terminal_commands']['curl'] }}</code>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Quick Tips --}}
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-3">
                                <h4 class="text-sm font-medium text-blue-900 mb-2">Quick Tips</h4>
                                <ul class="text-xs text-blue-700 space-y-1 list-disc list-inside">
                                    @if ($ftpUser->require_ssl)
                                        <li>This account requires SSL/TLS encryption</li>
                                    @else
                                        <li>This account does not require SSL/TLS (not recommended)</li>
                                    @endif
                                    <li>Use passive mode for NAT/firewall compatibility</li>
                                    <li>Your quota is {{ $ftpUser->getFormattedQuota() }}
                                        ({{ $ftpUser->getFormattedUsage() }} used)</li>
                                    @if ($ftpUser->allowed_ip)
                                        <li>IP restriction: Only {{ $ftpUser->allowed_ip }} can connect</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="closeModal" type="button"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

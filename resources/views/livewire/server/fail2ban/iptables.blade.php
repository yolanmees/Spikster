<div class="p-4">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-semibold text-gray-900">Banned IPs</h2>
        <button wire:click="refresh"
            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <svg class="-ml-0.5 mr-1.5 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Refresh
        </button>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-md bg-red-50 p-4">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label for="searchIp" class="block text-sm font-medium text-gray-700">Search IP</label>
            <input type="text" wire:model.live="searchIp" id="searchIp"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="192.168.1.100">
        </div>
        <div>
            <label for="selectedJail" class="block text-sm font-medium text-gray-700">Filter by Jail</label>
            <select wire:model.live="selectedJail" id="selectedJail"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="all">All Jails</option>
                @if (isset($iptables[0]) && is_array($iptables[0]))
                    @php
                        $jails = array_unique(array_column(array_filter($iptables[0], fn($ip) => isset($ip[1])), 1));
                        sort($jails);
                    @endphp
                    @foreach ($jails as $jail)
                        <option value="{{ $jail }}">{{ $jail }}</option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>

    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">IP
                                    address</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    Jail</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    Banned At</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @php
                                $filteredIps = $this->getFilteredIptables();
                            @endphp

                            @if (count($filteredIps) > 0)
                                @foreach ($filteredIps as $ip)
                                    @if (is_array($ip) && count($ip) >= 2)
                                        <tr>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                <span class="font-mono">{{ $ip[0] ?? 'N/A' }}</span>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <span
                                                    class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                    {{ $ip[1] ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                @if (isset($ip[2]))
                                                    {{ date('Y-m-d H:i:s', $ip[2]) }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td
                                                class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <button
                                                    wire:click="unbanIp('{{ $ip[0] }}', '{{ $ip[1] ?? null }}')"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                    wire:confirm="Are you sure you want to unban {{ $ip[0] }}?">
                                                    Unban
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4"
                                        class="whitespace-nowrap py-8 pl-4 pr-3 text-center text-sm text-gray-500">
                                        @if (!empty($searchIp) || $selectedJail !== 'all')
                                            No banned IPs found matching your filters.
                                        @else
                                            No banned IPs found. Great! Your server is secure.
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

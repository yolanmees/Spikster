@extends('layouts.app')

@section('title')
    {{ $domain->domain }} - DNS Management
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('domain.list') }}"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center mb-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to domains
                </a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $domain->domain }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    DNS Records Management
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('domain.dns.new', $domain->domain_id) }}"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add DNS Record
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="rounded-md bg-red-50 dark:bg-red-900/30 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Domain Info -->
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            @if ($domain->is_primary)
                                <x-badge color="green" text="Primary Domain" />
                            @else
                                <x-badge color="gray" text="Alias" />
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Site</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            @if ($domain->site)
                                <a href="{{ route('site.edit', $domain->site_id) }}"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                    {{ $domain->site->domain }}
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Server</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            @if ($domain->server)
                                {{ $domain->server->name }} ({{ $domain->server->ip }})
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- DNS Records Table -->
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">DNS Records</h2>

                @if ($dnsRecords->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th scope="col"
                                        class="px-5 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Host
                                    </th>
                                    <th scope="col"
                                        class="px-5 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th scope="col"
                                        class="px-5 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Value
                                    </th>
                                    <th scope="col"
                                        class="px-5 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        TTL
                                    </th>
                                    <th scope="col"
                                        class="px-5 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Priority
                                    </th>
                                    <th scope="col" class="relative px-5 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($dnsRecords as $record)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <span class="font-mono">{{ $record->zone }}</span>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm">
                                            <x-badge :color="match ($record->type) {
                                                'A' => 'blue',
                                                'AAAA' => 'indigo',
                                                'CNAME' => 'purple',
                                                'MX' => 'pink',
                                                'TXT' => 'green',
                                                'NS' => 'yellow',
                                                'SRV' => 'orange',
                                                default => 'gray',
                                            }" :text="$record->type" />
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-300">
                                            <span class="font-mono break-all">{{ Str::limit($record->value, 50) }}</span>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $record->ttl }}
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $record->priority ?? '-' }}
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('domain.dns.edit', [$domain->domain_id, $record->id]) }}"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-4">
                                                Edit
                                            </a>
                                            <form method="POST"
                                                action="{{ route('domain.dns.delete', [$domain->domain_id, $record->id]) }}"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400"
                                                    onclick="return confirm('Are you sure you want to delete this DNS record?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No DNS records</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Get started by adding a new DNS record.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('domain.dns.new', $domain->domain_id) }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Add DNS Record
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

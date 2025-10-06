@extends('layouts.app')

@section('title')
    Add DNS Record - {{ $domain->domain }}
@endsection

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Header -->
        <div>
            <a href="{{ route('domain.show', $domain->domain_id) }}"
                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to {{ $domain->domain }}
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Add DNS Record
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Create a new DNS record for {{ $domain->domain }}
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <form method="POST" action="{{ route('domain.dns.create', $domain->domain_id) }}" class="space-y-6 p-6">
                @csrf

                <!-- Errors -->
                @if ($errors->any())
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
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                    There were errors with your submission
                                </h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Record Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Record Type *
                    </label>
                    <select id="type" name="type" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="A" {{ old('type') == 'A' ? 'selected' : '' }}>A - IPv4 Address</option>
                        <option value="AAAA" {{ old('type') == 'AAAA' ? 'selected' : '' }}>AAAA - IPv6 Address</option>
                        <option value="CNAME" {{ old('type') == 'CNAME' ? 'selected' : '' }}>CNAME - Canonical Name
                        </option>
                        <option value="MX" {{ old('type') == 'MX' ? 'selected' : '' }}>MX - Mail Exchange</option>
                        <option value="TXT" {{ old('type') == 'TXT' ? 'selected' : '' }}>TXT - Text Record</option>
                        <option value="NS" {{ old('type') == 'NS' ? 'selected' : '' }}>NS - Name Server</option>
                        <option value="SRV" {{ old('type') == 'SRV' ? 'selected' : '' }}>SRV - Service Record</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Select the type of DNS record to create
                    </p>
                </div>

                <!-- Host/Zone -->
                <div>
                    <label for="zone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Host/Name *
                    </label>
                    <input type="text" name="zone" id="zone" value="{{ old('zone') }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        placeholder="@, www, mail, subdomain">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Use @ for the root domain, or enter a subdomain name
                    </p>
                </div>

                <!-- Value -->
                <div>
                    <label for="value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Value *
                    </label>
                    <input type="text" name="value" id="value" value="{{ old('value') }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        placeholder="e.g., 192.168.1.1">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        The value depends on the record type (IP address, domain name, or text)
                    </p>
                </div>

                <!-- TTL -->
                <div>
                    <label for="ttl" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        TTL (seconds)
                    </label>
                    <input type="number" name="ttl" id="ttl" value="{{ old('ttl', 3600) }}" min="60"
                        max="86400"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Time to live in seconds (default: 3600 = 1 hour)
                    </p>
                </div>

                <!-- Priority (for MX and SRV) -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Priority (MX/SRV only)
                    </label>
                    <input type="number" name="priority" id="priority" value="{{ old('priority') }}" min="0"
                        max="65535"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Required for MX and SRV records. Lower values have higher priority.
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('domain.show', $domain->domain_id) }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create DNS Record
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

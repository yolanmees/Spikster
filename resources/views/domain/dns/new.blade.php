@extends('layouts.app')

@section('title')
    Add DNS Record - {{ $domain->domain }}
@endsection

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <x-page-header title="Add DNS Record" subtitle="Create a new DNS record for {{ $domain->domain }}" size="section">
            <x-slot name="actions">
                <x-back-button :href="route('domain.show', $domain->domain_id)" label="Back to DNS Records" />
            </x-slot>
        </x-page-header>

        <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <form method="POST" action="{{ route('domain.dns.create', $domain->domain_id) }}" class="space-y-6 p-6">
                @csrf

                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-700/30 dark:bg-red-900/20">
                        <div class="flex gap-3">
                            <i data-lucide="alert-circle" class="mt-0.5 h-5 w-5 shrink-0 text-red-500"></i>
                            <div>
                                <h3 class="text-sm font-semibold text-red-800 dark:text-red-200">There were errors with your submission</h3>
                                <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700 dark:text-red-300">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Record Type *</label>
                        <select id="type" name="type" required
                            class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                            <option value="A" {{ old('type') == 'A' ? 'selected' : '' }}>A - IPv4 Address</option>
                            <option value="AAAA" {{ old('type') == 'AAAA' ? 'selected' : '' }}>AAAA - IPv6 Address</option>
                            <option value="CNAME" {{ old('type') == 'CNAME' ? 'selected' : '' }}>CNAME - Canonical Name</option>
                            <option value="MX" {{ old('type') == 'MX' ? 'selected' : '' }}>MX - Mail Exchange</option>
                            <option value="TXT" {{ old('type') == 'TXT' ? 'selected' : '' }}>TXT - Text Record</option>
                            <option value="NS" {{ old('type') == 'NS' ? 'selected' : '' }}>NS - Name Server</option>
                            <option value="SRV" {{ old('type') == 'SRV' ? 'selected' : '' }}>SRV - Service Record</option>
                        </select>
                        <p class="mt-1 text-xs text-zinc-400">Select the type of DNS record to create</p>
                    </div>

                    <div>
                        <label for="zone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Host/Name *</label>
                        <input type="text" name="zone" id="zone" value="{{ old('zone') }}" required
                            class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 font-mono text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                            placeholder="@, www, mail, subdomain">
                        <p class="mt-1 text-xs text-zinc-400">Use @ for the root domain, or enter a subdomain name</p>
                    </div>

                    <div>
                        <label for="ttl" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">TTL (seconds)</label>
                        <input type="number" name="ttl" id="ttl" value="{{ old('ttl', 3600) }}" min="60" max="86400"
                            class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 font-mono text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                        <p class="mt-1 text-xs text-zinc-400">Time to live (default: 3600 = 1 hour)</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="value" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Value *</label>
                        <input type="text" name="value" id="value" value="{{ old('value') }}" required
                            class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 font-mono text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                            placeholder="e.g., 192.168.1.1">
                        <p class="mt-1 text-xs text-zinc-400">The value depends on the record type (IP address, domain name, or text)</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="priority" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Priority (MX/SRV only)</label>
                        <input type="number" name="priority" id="priority" value="{{ old('priority') }}" min="0" max="65535"
                            class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 font-mono text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                        <p class="mt-1 text-xs text-zinc-400">Required for MX and SRV records. Lower values have higher priority.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-800">
                    <a href="{{ route('domain.show', $domain->domain_id) }}"
                       class="inline-flex items-center justify-center rounded-lg border border-zinc-200 px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-800 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-purple-800 transition-colors">
                        <i data-lucide="check" class="h-4 w-4"></i>
                        Create DNS Record
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.lucide) lucide.createIcons();
    });
</script>
@endpush

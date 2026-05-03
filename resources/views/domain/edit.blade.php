@extends('layouts.app')

@section('title', $domain->domain . ' — DNS Management')

@section('content')
    <div class="space-y-6">
        <x-page-header :title="$domain->domain" subtitle="DNS Records Management">
            <x-slot name="actions">
                <x-back-button :href="route('domain.list')" label="Back to Domains" />
                <x-primary-button tag="a" href="{{ route('domain.dns.new', $domain->domain_id) }}">
                    <x-icon icon="plus" class="-ml-1 mr-1.5 h-4 w-4" />
                    Add DNS Record
                </x-primary-button>
            </x-slot>
        </x-page-header>

        <x-flash-messages />

        {{-- Stats --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">DNS Records</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $stats['dns_records_count'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                        <i data-lucide="network" class="h-5 w-5"></i>
                    </span>
                </div>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Type</p>
                        <p class="mt-2 text-2xl font-semibold">
                            @if ($domain->is_primary)
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">Primary</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">Alias</span>
                            @endif
                        </p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                        <i data-lucide="tag" class="h-5 w-5"></i>
                    </span>
                </div>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Site</p>
                        <p class="mt-2 text-lg font-semibold truncate">
                            @if ($domain->site)
                                <a href="{{ route('site.edit', $domain->site_id) }}" class="text-purple-700 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300">
                                    {{ $domain->site->domain }}
                                </a>
                            @else
                                <span class="text-zinc-400">&mdash;</span>
                            @endif
                        </p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                        <i data-lucide="app-window" class="h-5 w-5"></i>
                    </span>
                </div>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Server</p>
                        <p class="mt-2 text-lg font-semibold truncate">
                            @if ($domain->server)
                                {{ $domain->server->name }}
                            @else
                                <span class="text-zinc-400">&mdash;</span>
                            @endif
                        </p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                        <i data-lucide="server" class="h-5 w-5"></i>
                    </span>
                </div>
            </div>
        </div>

        {{-- DNS Records Table --}}
        <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-200 p-5 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-white">DNS Records</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Manage DNS records for {{ $domain->domain }}</p>
                </div>
                <a href="{{ route('domain.dns.new', $domain->domain_id) }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-950 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200 transition-colors">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Add DNS Record
                </a>
            </div>

            @if ($dnsRecords->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="bg-zinc-50 dark:bg-zinc-900">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Host</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Type</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Value</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">TTL</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Priority</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach ($dnsRecords as $record)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm text-zinc-900 dark:text-white">{{ $record->zone }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5">
                                        @php
                                            $typeColors = [
                                                'A'     => 'bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300',
                                        'AAAA'  => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                                        'CNAME' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                                                'MX'    => 'bg-amber-100 text-amber-700 dark:bg-amber-700/15 dark:text-amber-300',
                                                'TXT'   => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-700/15 dark:text-emerald-300',
                                                'NS'    => 'bg-rose-100 text-rose-700 dark:bg-rose-700/15 dark:text-rose-300',
                                                'SRV'   => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                                            ];
                                            $class = $typeColors[$record->type] ?? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300';
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $class }}">
                                            {{ $record->type }}
                                        </span>
                                    </td>
                                    <td class="max-w-xs truncate px-5 py-3.5 font-mono text-sm text-zinc-500 dark:text-zinc-300">{{ Str::limit($record->value, 50) }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-sm text-zinc-500 dark:text-zinc-400">{{ $record->ttl }}s</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-sm text-zinc-500 dark:text-zinc-400">{{ $record->priority ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('domain.dns.edit', [$domain->domain_id, $record->id]) }}"
                                               class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800 transition-colors">
                                                <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('domain.dns.delete', [$domain->domain_id, $record->id]) }}"
                                                  onsubmit="return confirm('Delete this DNS record?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-700/10 transition-colors">
                                                    <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-empty-state icon="dns" title="No DNS records" message="Get started by adding a new DNS record.">
                    <x-slot name="action">
                        <x-primary-button tag="a" href="{{ route('domain.dns.new', $domain->domain_id) }}">
                            <x-icon icon="plus" class="-ml-1 mr-1.5 h-4 w-4" />
                            Add DNS Record
                        </x-primary-button>
                    </x-slot>
                </x-empty-state>
            @endif
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

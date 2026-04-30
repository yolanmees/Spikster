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

        {{-- Domain Info --}}
        <x-card>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Type</dt>
                    <dd class="mt-1">
                        <x-badge :color="$domain->is_primary ? 'green' : 'gray'" :text="$domain->is_primary ? 'Primary Domain' : 'Alias'" />
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Site</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        @if ($domain->site)
                            <a href="{{ route('site.edit', $domain->site_id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                {{ $domain->site->domain }}
                            </a>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Server</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        @if ($domain->server)
                            {{ $domain->server->name }} <span class="text-gray-400 font-mono text-xs">({{ $domain->server->ip }})</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </dd>
                </div>
            </div>
        </x-card>

        {{-- DNS Records Table --}}
        <x-card>
            <x-slot name="header">DNS Records</x-slot>

            @if ($dnsRecords->count() > 0)
                <div class="-mx-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                @foreach (['Host', 'Type', 'Value', 'TTL', 'Priority', ''] as $col)
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider {{ $loop->last ? 'text-right' : '' }}">
                                        {{ $col }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($dnsRecords as $record)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="px-4 py-3.5 text-sm font-mono text-gray-900 dark:text-white">{{ $record->zone }}</td>
                                    <td class="px-4 py-3.5 text-sm">
                                        <x-badge :color="match ($record->type) {
                                            'A'     => 'blue',
                                            'AAAA'  => 'indigo',
                                            'CNAME' => 'purple',
                                            'MX'    => 'red',
                                            'TXT'   => 'green',
                                            'NS'    => 'yellow',
                                            'SRV'   => 'gray',
                                            default => 'gray',
                                        }" :text="$record->type" />
                                    </td>
                                    <td class="px-4 py-3.5 text-sm font-mono text-gray-500 dark:text-gray-300 max-w-xs truncate">{{ Str::limit($record->value, 50) }}</td>
                                    <td class="px-4 py-3.5 text-sm text-gray-500 dark:text-gray-400">{{ $record->ttl }}</td>
                                    <td class="px-4 py-3.5 text-sm text-gray-500 dark:text-gray-400">{{ $record->priority ?? '—' }}</td>
                                    <td class="px-4 py-3.5 text-right text-sm">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-action-button :href="route('domain.dns.edit', [$domain->domain_id, $record->id])">
                                                Edit
                                            </x-action-button>
                                            <form method="POST" action="{{ route('domain.dns.delete', [$domain->domain_id, $record->id]) }}"
                                                  onsubmit="return confirm('Delete this DNS record?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <x-danger-button type="submit" size="sm">Delete</x-danger-button>
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
        </x-card>
    </div>
@endsection

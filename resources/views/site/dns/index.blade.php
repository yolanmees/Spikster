@extends('layouts.app')

@section('title', 'DNS Records')

@section('content')
    <div class="space-y-6">
        <x-page-header title="DNS Records">
            <x-slot name="actions">
                <x-back-button :href="route('site.edit', $site_id)" label="Back to Site" />
                <x-primary-button tag="a" href="{{ route('site.dns.new', ['site_id' => $site_id]) }}">
                    <x-icon icon="plus" class="-ml-1 mr-1.5 h-4 w-4" />
                    Add Record
                </x-primary-button>
            </x-slot>
        </x-page-header>

        <x-table-wrapper>
            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        @foreach (['Host', 'TTL', 'Type', 'Value', 'Actions'] as $col)
                            <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white {{ $loop->last ? 'text-right pr-6' : '' }}">
                                {{ $col }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @forelse ($dnsRecords as $record)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-4 py-3.5 text-sm font-mono text-gray-900 dark:text-white">{{ $record->zone }}</td>
                            <td class="px-4 py-3.5 text-sm text-gray-500 dark:text-gray-400">{{ $record->ttl }}</td>
                            <td class="px-4 py-3.5 text-sm">
                                <x-badge color="indigo" :text="$record->type" />
                            </td>
                            <td class="px-4 py-3.5 text-sm font-mono text-gray-700 dark:text-gray-300 max-w-xs truncate">{{ $record->value }}</td>
                            <td class="px-4 py-3.5 text-sm text-right pr-6">
                                <div class="flex items-center justify-end gap-2">
                                    <x-action-button :href="route('site.dns.edit', ['site_id' => $site_id, 'dns_id' => $record->id])">
                                        Edit
                                    </x-action-button>
                                    <form method="POST" action="{{ route('site.dns.delete', ['site_id' => $site_id, 'dns_id' => $record->id]) }}"
                                          onsubmit="return confirm('Delete this DNS record?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-danger-button type="submit" size="sm">Delete</x-danger-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="dns" title="No DNS records" message="Add your first DNS record to get started." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-wrapper>
    </div>
@endsection

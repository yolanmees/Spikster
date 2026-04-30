@extends('layouts.settings')

@section('title')
    {{ __('spikster.titles.settings') }}
@endsection

@section('settings-content')
    <x-page-header title="{{ __('spikster.username') }}s" subtitle="Legacy user list — use User Management for full control." />

    <x-alert type="info">
        Please use the new <a href="{{ route('settings.users') }}" class="underline font-bold">User Management</a> page for full functionality.
    </x-alert>

    <x-card class="mt-4">
        <x-table-wrapper>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('spikster.username') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('spikster.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('settings.users.delete', $user->id) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <x-danger-button type="submit">Delete</x-danger-button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <x-empty-state icon="users" title="No users found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-wrapper>
    </x-card>
@endsection

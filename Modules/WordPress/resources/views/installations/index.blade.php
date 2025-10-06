@extends('layouts.app')

@section('title', 'WordPress Installations')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">WordPress Installations</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage all your WordPress installations</p>
            </div>
            <a href="{{ route('wordpress.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Installation
            </a>
        </div>

        @if (session('success'))
            <div
                class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div
                class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Filters -->
        <div class="mb-6 flex gap-4">
            <select
                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                onchange="window.location.href = this.value">
                <option value="{{ route('wordpress.index') }}">All Statuses</option>
                <option value="{{ route('wordpress.index', ['status' => 'active']) }}"
                    {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="{{ route('wordpress.index', ['status' => 'staging']) }}"
                    {{ request('status') === 'staging' ? 'selected' : '' }}>Staging</option>
                <option value="{{ route('wordpress.index', ['status' => 'inactive']) }}"
                    {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Installations Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($installations as $installation)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $installation->site->domain ?? 'Unknown Site' }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $installation->path }}</p>
                        </div>
                        <span
                            class="px-2 py-1 text-xs font-medium rounded-full {{ $installation->status === 'active'
                                ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                : ($installation->status === 'staging'
                                    ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300') }}">
                            {{ ucfirst($installation->status) }}
                        </span>
                    </div>

                    <!-- Stats -->
                    <div class="mb-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Version:</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium">{{ $installation->version ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Themes:</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium">{{ $installation->themes->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Plugins:</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium">{{ $installation->plugins->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Updates:</span>
                            <span
                                class="font-medium {{ $installation->getUpdatesCount() > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ $installation->getUpdatesCount() }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <a href="{{ route('wordpress.show', $installation->id) }}"
                            class="flex-1 px-3 py-2 text-sm font-medium text-center text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                            Details
                        </a>
                        <a href="{{ $installation->getAdminUrl() }}" target="_blank"
                            class="flex-1 px-3 py-2 text-sm font-medium text-center text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Open Admin
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">🔷</div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No WordPress installations</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Get started by creating your first WordPress
                        installation</p>
                    <a href="{{ route('wordpress.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create First Installation
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($installations->hasPages())
            <div class="mt-6">
                {{ $installations->links() }}
            </div>
        @endif
    </div>
@endsection

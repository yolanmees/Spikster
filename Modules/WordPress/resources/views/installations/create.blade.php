@extends('layouts.app')

@section('title', 'Create WordPress Installation')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Breadcrumb -->
        <nav class="mb-6 text-sm text-gray-600 dark:text-gray-400">
            <a href="{{ route('wordpress.index') }}" class="hover:text-blue-600">WordPress</a>
            <span class="mx-2">/</span>
            <span>Create Installation</span>
        </nav>

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create WordPress Installation</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Install a new WordPress site</p>
        </div>

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

        <!-- Form -->
        <div class="max-w-2xl">
            <form action="{{ route('wordpress.store') }}" method="POST"
                class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-6">
                @csrf

                <!-- Site Selection -->
                <div>
                    <label for="site_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Site <span class="text-red-500">*</span>
                    </label>
                    <select name="site_id" id="site_id" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select a site...</option>
                        @foreach (\App\Models\Site::all() as $site)
                            <option value="{{ $site->site_id }}" {{ old('site_id') == $site->site_id ? 'selected' : '' }}>
                                {{ $site->domain }} ({{ $site->site_id }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select the site where WordPress will be
                        installed</p>
                </div>

                <!-- Installation Path -->
                <div>
                    <label for="path" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Installation Path <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="path" id="path" required value="{{ old('path', '/') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="/blog">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Path relative to site root (e.g., /blog or /
                        for root)</p>
                </div>

                <!-- URL (Optional) -->
                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Site URL (Optional)
                    </label>
                    <input type="url" name="url" id="url" value="{{ old('url') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="https://example.com">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Leave empty to auto-generate from site domain
                    </p>
                </div>

                <!-- Admin Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Admin Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="username" id="username" required value="{{ old('username', 'admin') }}"
                        minlength="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="admin">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">WordPress admin username (minimum 3 characters)
                    </p>
                </div>

                <!-- Admin Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Admin Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" id="password" required minlength="8"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="••••••••">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">WordPress admin password (minimum 8 characters)
                    </p>
                </div>

                <!-- Locale -->
                <div>
                    <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Language
                    </label>
                    <select name="locale" id="locale"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="en_US" {{ old('locale', 'en_US') == 'en_US' ? 'selected' : '' }}>English (US)
                        </option>
                        <option value="en_GB" {{ old('locale') == 'en_GB' ? 'selected' : '' }}>English (UK)</option>
                        <option value="nl_NL" {{ old('locale') == 'nl_NL' ? 'selected' : '' }}>Nederlands</option>
                        <option value="de_DE" {{ old('locale') == 'de_DE' ? 'selected' : '' }}>Deutsch</option>
                        <option value="fr_FR" {{ old('locale') == 'fr_FR' ? 'selected' : '' }}>Français</option>
                        <option value="es_ES" {{ old('locale') == 'es_ES' ? 'selected' : '' }}>Español</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Install WordPress
                    </button>
                    <a href="{{ route('wordpress.index') }}"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Cancel
                    </a>
                </div>

                <!-- Installation Info -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-2">What will happen:</h4>
                    <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1 list-disc list-inside">
                        <li>A new MySQL database and user will be created automatically</li>
                        <li>WordPress will be downloaded from wordpress.org</li>
                        <li>Files will be extracted to the specified path</li>
                        <li>wp-config.php will be configured with database credentials</li>
                        <li>Permissions will be set correctly (www-data:www-data)</li>
                    </ul>
                </div>
            </form>
        </div>
    </div>
@endsection

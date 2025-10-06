<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Themes ({{ count($themes) }})
        </h3>
        <div class="flex gap-2">
            <button wire:click="syncThemes"
                class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Sync Themes
            </button>
            <button wire:click="openBrowseModal"
                class="px-3 py-1.5 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                Browse WordPress.org
            </button>
            <button wire:click="openInstallModal"
                class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                Manual Install
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div
            class="p-3 bg-green-100 border border-green-200 text-green-700 rounded-lg dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div
            class="p-3 bg-red-100 border border-red-200 text-red-700 rounded-lg dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <!-- Themes List -->
    @if (count($themes) > 0)
        <div class="space-y-2">
            @foreach ($themes as $theme)
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h4 class="font-medium text-gray-900 dark:text-white">
                                {{ $theme['name'] }}
                            </h4>
                            @if ($theme['is_active'])
                                <span
                                    class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded dark:bg-green-900/30 dark:text-green-400">
                                    Active
                                </span>
                            @endif
                            @if ($theme['update_available'])
                                <span
                                    class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded dark:bg-yellow-900/30 dark:text-yellow-400">
                                    Update: {{ $theme['update_available'] }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            v{{ $theme['version'] }} by {{ $theme['author'] }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        @if (!$theme['is_active'])
                            <button wire:click="activateTheme('{{ $theme['slug'] }}')"
                                class="px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 rounded dark:text-blue-400 dark:hover:bg-blue-900/20">
                                Activate
                            </button>
                        @endif

                        @if ($theme['update_available'])
                            <button wire:click="updateTheme('{{ $theme['slug'] }}')"
                                class="px-3 py-1 text-sm text-green-600 hover:bg-green-50 rounded dark:text-green-400 dark:hover:bg-green-900/20">
                                Update
                            </button>
                        @endif

                        @if (!$theme['is_active'])
                            <button wire:click="deleteTheme('{{ $theme['slug'] }}')"
                                onclick="return confirm('Are you sure you want to delete this theme?')"
                                class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded dark:text-red-400 dark:hover:bg-red-900/20">
                                Delete
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p>No themes found. Click "Sync Themes" to load themes from WordPress.</p>
        </div>
    @endif

    <!-- Install Theme Modal -->
    @if ($showInstallModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click="closeInstallModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4" wire:click.stop>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Manual Install Theme
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Theme Slug
                        </label>
                        <input type="text" wire:model="newThemeSlug" placeholder="e.g., twentytwentyfour"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('newThemeSlug')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="activateAfterInstall" wire:model="activateAfterInstall"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                        <label for="activateAfterInstall" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Activate after installation
                        </label>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button wire:click="installTheme"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Install
                    </button>
                    <button wire:click="closeInstallModal"
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Browse WordPress.org Modal -->
    @if ($showBrowseModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click="closeBrowseModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop>
                <!-- Modal Header -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Browse WordPress.org Themes
                        </h3>
                        <button wire:click="closeBrowseModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.500ms="searchTerm" placeholder="Search themes..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Themes Grid -->
                <div class="flex-1 overflow-y-auto p-6">
                    @if (count($wpOrgThemes) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach ($wpOrgThemes as $theme)
                                <div
                                    class="bg-gray-50 dark:bg-gray-700/50 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 hover:shadow-lg transition-shadow">
                                    <!-- Screenshot -->
                                    @if (!empty($theme['screenshot_url']))
                                        <img src="{{ $theme['screenshot_url'] }}" alt="{{ $theme['name'] }}"
                                            class="w-full h-48 object-cover">
                                    @else
                                        <div
                                            class="w-full h-48 bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-400" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    @endif

                                    <!-- Theme Info -->
                                    <div class="p-4">
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-1">
                                            {{ $theme['name'] }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">by
                                            {{ $theme['author']['display_name'] ?? ($theme['author'] ?? 'Unknown') }}</p>

                                        <!-- Rating & Downloads -->
                                        <div class="flex items-center gap-3 mb-3 text-sm">
                                            @if (!empty($theme['rating']))
                                                <div class="flex items-center gap-1">
                                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path
                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    <span
                                                        class="text-gray-700 dark:text-gray-300">{{ number_format($theme['rating'] / 20, 1) }}</span>
                                                </div>
                                            @endif
                                            @if (!empty($theme['active_installs']))
                                                <span
                                                    class="text-gray-600 dark:text-gray-400">{{ number_format($theme['active_installs']) }}+
                                                    installs</span>
                                            @endif
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex gap-2">
                                            <button wire:click="showThemeDetailsModal('{{ $theme['slug'] }}')"
                                                class="flex-1 px-3 py-1.5 text-sm bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                                                Details
                                            </button>
                                            <button wire:click="installFromWpOrg('{{ $theme['slug'] }}')"
                                                class="flex-1 px-3 py-1.5 text-sm bg-purple-600 text-white rounded hover:bg-purple-700 transition-colors">
                                                Install
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400">No themes found. Try a different search term.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Theme Details Modal -->
    @if ($showThemeDetails && $selectedWpOrgTheme)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60] p-4"
            wire:click="closeThemeDetails">
            <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop>
                <!-- Header -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $selectedWpOrgTheme['name'] }}
                    </h3>
                    <button wire:click="closeThemeDetails"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Screenshot -->
                    @if (!empty($selectedWpOrgTheme['screenshot_url']))
                        <img src="{{ $selectedWpOrgTheme['screenshot_url'] }}"
                            alt="{{ $selectedWpOrgTheme['name'] }}" class="w-full rounded-lg mb-6">
                    @endif

                    <!-- Meta Info -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Version</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $selectedWpOrgTheme['version'] }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Author</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $selectedWpOrgTheme['author']['display_name'] ?? ($selectedWpOrgTheme['author'] ?? 'Unknown') }}
                            </p>
                        </div>
                        @if (!empty($selectedWpOrgTheme['rating']))
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Rating</p>
                                <p class="font-semibold text-gray-900 dark:text-white flex items-center gap-1">
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    {{ number_format($selectedWpOrgTheme['rating'] / 20, 1) }}
                                </p>
                            </div>
                        @endif
                        @if (!empty($selectedWpOrgTheme['active_installs']))
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Active Installs</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($selectedWpOrgTheme['active_installs']) }}+</p>
                            </div>
                        @endif
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Description</h4>
                        <div class="text-gray-700 dark:text-gray-300 prose dark:prose-invert max-w-none">
                            {!! $selectedWpOrgTheme['sections']['description'] ??
                                ($selectedWpOrgTheme['description'] ?? 'No description available.') !!}
                        </div>
                    </div>

                    <!-- Tags -->
                    @if (!empty($selectedWpOrgTheme['tags']))
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tags</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach ((array) $selectedWpOrgTheme['tags'] as $tag)
                                    <span
                                        class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm">{{ is_array($tag) ? $tag['name'] : $tag }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer Actions -->
                <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                    <button wire:click="installFromWpOrg('{{ $selectedWpOrgTheme['slug'] }}', false)"
                        class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold">
                        Install Theme
                    </button>
                    <button wire:click="installFromWpOrg('{{ $selectedWpOrgTheme['slug'] }}', true)"
                        class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold">
                        Install & Activate
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Plugins ({{ count($plugins) }})
        </h3>
        <div class="flex gap-2">
            <button wire:click="syncPlugins"
                class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Sync Plugins
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

    <!-- Plugins List -->
    @if (count($plugins) > 0)
        <div class="space-y-2">
            @foreach ($plugins as $plugin)
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h4 class="font-medium text-gray-900 dark:text-white">
                                {{ $plugin['name'] }}
                            </h4>
                            @if ($plugin['is_active'])
                                <span
                                    class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded dark:bg-green-900/30 dark:text-green-400">
                                    Active
                                </span>
                            @else
                                <span
                                    class="px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded dark:bg-gray-700 dark:text-gray-400">
                                    Inactive
                                </span>
                            @endif
                            @if ($plugin['update_available'])
                                <span
                                    class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded dark:bg-yellow-900/30 dark:text-yellow-400">
                                    Update: {{ $plugin['update_available'] }}
                                </span>
                            @endif
                            @if ($plugin['auto_update'])
                                <span
                                    class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded dark:bg-blue-900/30 dark:text-blue-400">
                                    Auto-update
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            v{{ $plugin['version'] }} by {{ $plugin['author'] }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        @if ($plugin['is_active'])
                            <button wire:click="deactivatePlugin('{{ $plugin['slug'] }}')"
                                class="px-3 py-1 text-sm text-orange-600 hover:bg-orange-50 rounded dark:text-orange-400 dark:hover:bg-orange-900/20">
                                Deactivate
                            </button>
                        @else
                            <button wire:click="activatePlugin('{{ $plugin['slug'] }}')"
                                class="px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 rounded dark:text-blue-400 dark:hover:bg-blue-900/20">
                                Activate
                            </button>
                        @endif

                        @if ($plugin['update_available'])
                            <button wire:click="updatePlugin('{{ $plugin['slug'] }}')"
                                class="px-3 py-1 text-sm text-green-600 hover:bg-green-50 rounded dark:text-green-400 dark:hover:bg-green-900/20">
                                Update
                            </button>
                        @endif

                        @if (!$plugin['is_active'])
                            <button wire:click="deletePlugin('{{ $plugin['slug'] }}')"
                                onclick="return confirm('Are you sure you want to delete this plugin?')"
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
            <p>No plugins found. Click "Sync Plugins" to load plugins from WordPress.</p>
        </div>
    @endif

    <!-- Install Plugin Modal -->
    @if ($showInstallModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click="closeInstallModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4" wire:click.stop>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Manual Install Plugin
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Plugin Slug
                        </label>
                        <input type="text" wire:model="newPluginSlug" placeholder="e.g., akismet"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('newPluginSlug')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="activateAfterInstallPlugin" wire:model="activateAfterInstall"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                        <label for="activateAfterInstallPlugin" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Activate after installation
                        </label>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button wire:click="installPlugin"
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
                            Browse WordPress.org Plugins
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
                        <input type="text" wire:model.live.debounce.500ms="searchTerm"
                            placeholder="Search plugins..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Plugins Grid -->
                <div class="flex-1 overflow-y-auto p-6">
                    @if (count($wpOrgPlugins) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($wpOrgPlugins as $plugin)
                                <div
                                    class="bg-gray-50 dark:bg-gray-700/50 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 hover:shadow-lg transition-shadow">
                                    <!-- Plugin Icon/Banner -->
                                    <div
                                        class="w-full h-32 bg-gradient-to-br from-purple-400 to-purple-600 dark:from-purple-600 dark:to-purple-800 flex items-center justify-center p-4">
                                        @if (!empty($plugin['icons']['2x']))
                                            <img src="{{ $plugin['icons']['2x'] }}" alt="{{ $plugin['name'] }}"
                                                class="max-h-20 max-w-20">
                                        @elseif (!empty($plugin['icons']['1x']))
                                            <img src="{{ $plugin['icons']['1x'] }}" alt="{{ $plugin['name'] }}"
                                                class="max-h-20 max-w-20">
                                        @else
                                            <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </div>

                                    <!-- Plugin Info -->
                                    <div class="p-4">
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-1 line-clamp-1">
                                            {{ $plugin['name'] }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">
                                            {{ $plugin['short_description'] ?? '' }}</p>

                                        <!-- Rating & Downloads -->
                                        <div class="flex items-center gap-3 mb-3 text-sm">
                                            @if (!empty($plugin['rating']))
                                                <div class="flex items-center gap-1">
                                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path
                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    <span
                                                        class="text-gray-700 dark:text-gray-300">{{ number_format($plugin['rating'] / 20, 1) }}</span>
                                                </div>
                                            @endif
                                            @if (!empty($plugin['active_installs']))
                                                <span
                                                    class="text-gray-600 dark:text-gray-400">{{ number_format($plugin['active_installs']) }}+
                                                    installs</span>
                                            @endif
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex gap-2">
                                            <button wire:click="showPluginDetailsModal('{{ $plugin['slug'] }}')"
                                                class="flex-1 px-3 py-1.5 text-sm bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                                                Details
                                            </button>
                                            <button wire:click="installFromWpOrg('{{ $plugin['slug'] }}')"
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
                            <p class="text-gray-500 dark:text-gray-400">No plugins found. Try a different search term.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Plugin Details Modal -->
    @if ($showPluginDetails && $selectedWpOrgPlugin)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60] p-4"
            wire:click="closePluginDetails">
            <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop>
                <!-- Header -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4">
                            @if (!empty($selectedWpOrgPlugin['icons']['2x']))
                                <img src="{{ $selectedWpOrgPlugin['icons']['2x'] }}"
                                    alt="{{ $selectedWpOrgPlugin['name'] }}" class="w-16 h-16 rounded">
                            @endif
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ $selectedWpOrgPlugin['name'] }}</h3>
                                <p class="text-gray-600 dark:text-gray-400">by
                                    {{ $selectedWpOrgPlugin['author'] ?? 'Unknown' }}</p>
                            </div>
                        </div>
                        <button wire:click="closePluginDetails"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Banner -->
                    @if (!empty($selectedWpOrgPlugin['banners']['high']) || !empty($selectedWpOrgPlugin['banners']['low']))
                        <img src="{{ $selectedWpOrgPlugin['banners']['high'] ?? $selectedWpOrgPlugin['banners']['low'] }}"
                            alt="{{ $selectedWpOrgPlugin['name'] }}" class="w-full rounded-lg mb-6">
                    @endif

                    <!-- Meta Info -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Version</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $selectedWpOrgPlugin['version'] }}</p>
                        </div>
                        @if (!empty($selectedWpOrgPlugin['rating']))
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Rating</p>
                                <p class="font-semibold text-gray-900 dark:text-white flex items-center gap-1">
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    {{ number_format($selectedWpOrgPlugin['rating'] / 20, 1) }}
                                </p>
                            </div>
                        @endif
                        @if (!empty($selectedWpOrgPlugin['active_installs']))
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Active Installs</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($selectedWpOrgPlugin['active_installs']) }}+</p>
                            </div>
                        @endif
                        @if (!empty($selectedWpOrgPlugin['last_updated']))
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Last Updated</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($selectedWpOrgPlugin['last_updated'])->diffForHumans() }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Description</h4>
                        <div class="text-gray-700 dark:text-gray-300 prose dark:prose-invert max-w-none">
                            {!! $selectedWpOrgPlugin['sections']['description'] ??
                                ($selectedWpOrgPlugin['short_description'] ?? 'No description available.') !!}
                        </div>
                    </div>

                    <!-- Requirements -->
                    <div
                        class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Requirements</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            @if (!empty($selectedWpOrgPlugin['requires']))
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">WordPress:</span>
                                    <span
                                        class="text-gray-900 dark:text-white font-medium">{{ $selectedWpOrgPlugin['requires'] }}+</span>
                                </div>
                            @endif
                            @if (!empty($selectedWpOrgPlugin['requires_php']))
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">PHP:</span>
                                    <span
                                        class="text-gray-900 dark:text-white font-medium">{{ $selectedWpOrgPlugin['requires_php'] }}+</span>
                                </div>
                            @endif
                            @if (!empty($selectedWpOrgPlugin['tested']))
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Tested up to:</span>
                                    <span
                                        class="text-gray-900 dark:text-white font-medium">{{ $selectedWpOrgPlugin['tested'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tags -->
                    @if (!empty($selectedWpOrgPlugin['tags']))
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tags</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach ((array) $selectedWpOrgPlugin['tags'] as $tag)
                                    <span
                                        class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm">{{ is_array($tag) ? $tag['name'] ?? $tag : $tag }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer Actions -->
                <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                    <button wire:click="installFromWpOrg('{{ $selectedWpOrgPlugin['slug'] }}', false)"
                        class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold">
                        Install Plugin
                    </button>
                    <button wire:click="installFromWpOrg('{{ $selectedWpOrgPlugin['slug'] }}', true)"
                        class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold">
                        Install & Activate
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

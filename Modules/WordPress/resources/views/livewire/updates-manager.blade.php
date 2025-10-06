<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Updates
            @if ($this->totalUpdates > 0)
                <span
                    class="ml-2 px-2 py-0.5 text-sm bg-yellow-100 text-yellow-700 rounded dark:bg-yellow-900/30 dark:text-yellow-400">
                    {{ $this->totalUpdates }} available
                </span>
            @endif
        </h3>
        <div class="flex gap-2">
            <button wire:click="checkUpdates" wire:loading.attr="disabled" wire:target="checkUpdates"
                class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                <span wire:loading.remove wire:target="checkUpdates">Check for Updates</span>
                <span wire:loading wire:target="checkUpdates">Checking...</span>
            </button>
            @if ($this->totalUpdates > 0)
                <button wire:click="updateAll" wire:loading.attr="disabled" wire:target="updateAll"
                    class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="updateAll">Update All</span>
                    <span wire:loading wire:target="updateAll">Updating...</span>
                </button>
            @endif
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

    <!-- Core Update -->
    @if ($coreUpdate)
        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white">
                        WordPress Core Update Available
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Current: {{ $installation->version }} → New: {{ $coreUpdate['version'] ?? 'Unknown' }}
                    </p>
                </div>
                <button wire:click="updateCore" wire:loading.attr="disabled" wire:target="updateCore"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="updateCore">Update Core</span>
                    <span wire:loading wire:target="updateCore">Updating...</span>
                </button>
            </div>
        </div>
    @endif

    <!-- Theme Updates -->
    @if (count($themeUpdates) > 0)
        <div class="space-y-2">
            <h4 class="font-medium text-gray-900 dark:text-white">
                Theme Updates ({{ count($themeUpdates) }})
            </h4>
            @foreach ($themeUpdates as $theme)
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg flex items-center justify-between">
                    <div>
                        <h5 class="font-medium text-gray-900 dark:text-white">
                            {{ $theme['name'] }}
                        </h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            v{{ $theme['version'] }} → v{{ $theme['update_available'] }}
                        </p>
                    </div>
                    <button wire:click="updateTheme('{{ $theme['slug'] }}')" wire:loading.attr="disabled"
                        wire:target="updateTheme('{{ $theme['slug'] }}')"
                        class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="updateTheme('{{ $theme['slug'] }}')">Update</span>
                        <span wire:loading wire:target="updateTheme('{{ $theme['slug'] }}')">Updating...</span>
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Plugin Updates -->
    @if (count($pluginUpdates) > 0)
        <div class="space-y-2">
            <h4 class="font-medium text-gray-900 dark:text-white">
                Plugin Updates ({{ count($pluginUpdates) }})
            </h4>
            @foreach ($pluginUpdates as $plugin)
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg flex items-center justify-between">
                    <div>
                        <h5 class="font-medium text-gray-900 dark:text-white">
                            {{ $plugin['name'] }}
                        </h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            v{{ $plugin['version'] }} → v{{ $plugin['update_available'] }}
                        </p>
                    </div>
                    <button wire:click="updatePlugin('{{ $plugin['slug'] }}')" wire:loading.attr="disabled"
                        wire:target="updatePlugin('{{ $plugin['slug'] }}')"
                        class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="updatePlugin('{{ $plugin['slug'] }}')">Update</span>
                        <span wire:loading wire:target="updatePlugin('{{ $plugin['slug'] }}')">Updating...</span>
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    <!-- No Updates -->
    @if ($this->totalUpdates === 0 && !$isChecking)
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <div class="text-4xl mb-2">✅</div>
            <p>Everything is up to date!</p>
            <p class="text-sm mt-1">Click "Check for Updates" to check again.</p>
        </div>
    @endif

    <!-- Loading State -->
    @if ($isChecking)
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <div class="text-4xl mb-2">🔄</div>
            <p>Checking for updates...</p>
        </div>
    @endif
</div>

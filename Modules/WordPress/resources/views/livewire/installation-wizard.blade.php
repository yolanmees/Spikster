<div class="max-w-3xl mx-auto">
    @if (!$installationComplete)
        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                @for ($i = 1; $i <= $totalSteps; $i++)
                    <div class="flex items-center {{ $i < $totalSteps ? 'flex-1' : '' }}">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full 
                            {{ $currentStep >= $i ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                            {{ $i }}
                        </div>
                        @if ($i < $totalSteps)
                            <div
                                class="flex-1 h-1 mx-2 {{ $currentStep > $i ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
            <div class="flex items-center justify-between mt-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Site Selection</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Admin Credentials</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Configuration</span>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session()->has('success'))
            <div
                class="mb-4 p-3 bg-green-100 border border-green-200 text-green-700 rounded-lg dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div
                class="mb-4 p-3 bg-red-100 border border-red-200 text-red-700 rounded-lg dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                {{ session('error') }}
            </div>
        @endif

        <!-- Step Content -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <!-- Step 1: Site Selection -->
            @if ($currentStep === 1)
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Select Site</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Site <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="site_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Select a site...</option>
                            @foreach ($this->sites as $site)
                                <option value="{{ $site->site_id }}">{{ $site->domain }}</option>
                            @endforeach
                        </select>
                        @error('site_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Installation Path <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="path" placeholder="/"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Path relative to site root (e.g., /blog
                            or / for root)</p>
                        @error('path')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Site URL (Optional)
                        </label>
                        <input type="url" wire:model="url" placeholder="https://example.com"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Leave empty to auto-generate from site
                            domain</p>
                        @error('url')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endif

            <!-- Step 2: Admin Credentials -->
            @if ($currentStep === 2)
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Admin Credentials</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Admin Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="username" placeholder="admin"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('username')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Admin Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" wire:model="password" placeholder="••••••••"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Minimum 8 characters</p>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($this->selectedSite)
                        <div
                            class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <p class="text-sm text-blue-700 dark:text-blue-400">
                                Installing to: <strong>{{ $this->selectedSite->domain }}{{ $path }}</strong>
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Step 3: Configuration -->
            @if ($currentStep === 3)
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Configuration</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Language
                        </label>
                        <select wire:model="locale"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="en_US">English (US)</option>
                            <option value="en_GB">English (UK)</option>
                            <option value="nl_NL">Nederlands</option>
                            <option value="de_DE">Deutsch</option>
                            <option value="fr_FR">Français</option>
                            <option value="es_ES">Español</option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="auto_update" wire:model="auto_update"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                        <label for="auto_update" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Enable automatic updates
                        </label>
                    </div>

                    <div
                        class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <h4 class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-2">Installation Summary:</h4>
                        <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1">
                            @if ($this->selectedSite)
                                <li><strong>Site:</strong> {{ $this->selectedSite->domain }}</li>
                            @endif
                            <li><strong>Path:</strong> {{ $path }}</li>
                            <li><strong>Admin:</strong> {{ $username }}</li>
                            <li><strong>Language:</strong> {{ $locale }}</li>
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button wire:click="previousStep" @if ($currentStep === 1) disabled @endif
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors disabled:opacity-50 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                    Previous
                </button>

                @if ($currentStep < $totalSteps)
                    <button wire:click="nextStep"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Next
                    </button>
                @else
                    <button wire:click="install" wire:loading.attr="disabled" wire:target="install"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="install">Install WordPress</span>
                        <span wire:loading wire:target="install">Installing...</span>
                    </button>
                @endif
            </div>
        </div>
    @else
        <!-- Installation Complete -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
            <div class="text-6xl mb-4">🎉</div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                WordPress Installed Successfully!
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Your WordPress installation is ready to use.
            </p>
            <div class="flex gap-4 justify-center">
                <a href="{{ route('wordpress.show', $installation->id) }}"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    View Installation
                </a>
                <a href="{{ $installation->getAdminUrl() }}" target="_blank"
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                    Open Admin Panel
                </a>
            </div>
        </div>
    @endif
</div>

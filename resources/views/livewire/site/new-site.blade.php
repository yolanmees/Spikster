<div>
    <form wire:submit.prevent="submit">
        <div class="space-y-6">
            {{-- Flash Messages --}}
            @if (session()->has('success'))
                <div class="flex items-start gap-3 rounded-lg border-l-4 border-green-400 bg-green-50 p-4 dark:border-green-600 dark:bg-green-900/20">
                    <svg class="h-5 w-5 shrink-0 mt-0.5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <p class="flex-1 text-sm font-medium text-green-800 dark:text-green-300">{{ session('success') }}</p>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="flex items-start gap-3 rounded-lg border-l-4 border-red-400 bg-red-50 p-4 dark:border-red-600 dark:bg-red-900/20">
                    <svg class="h-5 w-5 shrink-0 mt-0.5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error') }}</p>
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                            Check the server daemon log: <code class="rounded bg-red-100 px-1 dark:bg-red-900/40">sudo tail -20 /var/log/spikster-daemon.log</code>
                        </p>
                    </div>
                </div>
            @endif

            <!-- Site Details -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Domain -->
                <div class="sm:col-span-2">
                    <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Domain <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="domain" wire:model="domain" placeholder="e.g. example.com"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                        autocomplete="off">
                    @error('domain')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Only lowercase letters, numbers, dots and dashes allowed
                    </p>
                </div>

                <!-- Server -->
                <div>
                    <label for="serverId" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Server <span class="text-red-500">*</span>
                    </label>
                    <select id="serverId" wire:model="serverId"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                        <option value="">Select a server</option>
                        @foreach ($servers as $server)
                            <option value="{{ $server->id }}">
                                {{ $server->name }} ({{ $server->ip }})
                                @if ($server->isDefault())
                                    - Default
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('serverId')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- PHP Version -->
                <div>
                    <label for="php" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        PHP Version <span class="text-red-500">*</span>
                    </label>
                    <select id="php" wire:model="php"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                        <option value="7.4">PHP 7.4</option>
                        <option value="8.0">PHP 8.0</option>
                        <option value="8.1">PHP 8.1</option>
                        <option value="8.2">PHP 8.2</option>
                        <option value="8.3">PHP 8.3 (Recommended)</option>
                    </select>
                    @error('php')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Basepath -->
                <div>
                    <label for="basepath" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Public Directory
                    </label>
                    <input type="text" id="basepath" wire:model="basepath" placeholder="/public"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                        autocomplete="off">
                    @error('basepath')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        For Laravel: /public, for other frameworks this may differ
                    </p>
                </div>
            </div>

            <!-- Git Repository (Optional) -->
            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">
                    Git Repository (optional)
                </h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Repository URL -->
                    <div class="sm:col-span-2">
                        <label for="repository" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Repository URL
                        </label>
                        <input type="url" id="repository" wire:model="repository"
                            placeholder="https://github.com/username/repository.git"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                            autocomplete="off">
                        @error('repository')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            GitHub, GitLab, Bitbucket, etc.
                        </p>
                    </div>

                    <!-- Branch -->
                    <div>
                        <label for="branch" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Branch
                        </label>
                        <input type="text" id="branch" wire:model="branch" placeholder="main"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                            autocomplete="off">
                        @error('branch')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Default: main
                        </p>
                    </div>
                </div>
            </div>

            <!-- Information Box -->
            <div class="p-4 rounded-md bg-blue-50 dark:bg-blue-900/20">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex-1 ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                            What happens after creation?
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                            <ul class="pl-5 space-y-1 list-disc">
                                <li>A new site will be created on the selected server</li>
                                <li>Nginx configuration will be automatically generated</li>
                                <li>PHP-FPM pool will be configured</li>
                                <li>Database and user will be created</li>
                                @if ($repository)
                                    <li>Git repository will be cloned</li>
                                    <li>Composer dependencies will be installed (if present)</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end pt-6 space-x-3 border-t border-gray-200 dark:border-gray-700">
                <button type="button" wire:click="resetForm"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:text-gray-300 dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    wire:loading.attr="disabled" wire:target="submit">
                    Reset
                </button>

                <button type="submit"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">
                        Create Site
                    </span>
                    <span wire:loading wire:target="submit" class="flex items-center">
                        <svg class="w-4 h-4 mr-2 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Creating site...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>

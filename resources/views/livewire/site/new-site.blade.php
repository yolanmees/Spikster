<div>
    <form wire:submit="submit">
        <div class="space-y-6">
            <!-- Flash Messages -->
            @if (session()->has('success'))
                <livewire:components.alert type="success" :message="session('success')" :dismissible="true" />
            @endif

            @if (session()->has('error'))
                <livewire:components.alert type="error" :message="session('error')" :dismissible="true" />
            @endif

            <!-- Site Details -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Domain -->
                <div class="sm:col-span-2">
                    <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Domein <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="domain" wire:model="domain" placeholder="bijv. example.com"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                        autocomplete="off">
                    @error('domain')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Alleen lowercase letters, cijfers, punten en streepjes toegestaan
                    </p>
                </div>

                <!-- Server -->
                <div>
                    <label for="serverId" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Server <span class="text-red-500">*</span>
                    </label>
                    <select id="serverId" wire:model="serverId"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                        <option value="">Selecteer een server</option>
                        @foreach ($servers as $server)
                            <option value="{{ $server->id }}">
                                {{ $server->name }} ({{ $server->ip }})
                                @if ($server->isDefault())
                                    - Standaard
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
                        PHP Versie <span class="text-red-500">*</span>
                    </label>
                    <select id="php" wire:model="php"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                        <option value="7.4">PHP 7.4</option>
                        <option value="8.0">PHP 8.0</option>
                        <option value="8.1">PHP 8.1</option>
                        <option value="8.2">PHP 8.2</option>
                        <option value="8.3">PHP 8.3 (Aanbevolen)</option>
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
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                        autocomplete="off">
                    @error('basepath')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Voor Laravel: /public, voor andere frameworks kan dit anders zijn
                    </p>
                </div>
            </div>

            <!-- Git Repository (Optional) -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Git Repository (optioneel)
                </h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Repository URL -->
                    <div class="sm:col-span-2">
                        <label for="repository" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Repository URL
                        </label>
                        <input type="url" id="repository" wire:model="repository"
                            placeholder="https://github.com/username/repository.git"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
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
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                            autocomplete="off">
                        @error('branch')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Standaard: main
                        </p>
                    </div>
                </div>
            </div>

            <!-- Information Box -->
            <div class="rounded-md bg-blue-50 dark:bg-blue-900/20 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                            Wat gebeurt er na aanmaken?
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Een nieuwe site wordt aangemaakt op de geselecteerde server</li>
                                <li>Nginx configuratie wordt automatisch gegenereerd</li>
                                <li>PHP-FPM pool wordt geconfigureerd</li>
                                <li>Database en gebruiker worden aangemaakt</li>
                                @if ($repository)
                                    <li>Git repository wordt gecloned</li>
                                    <li>Composer dependencies worden geïnstalleerd (indien aanwezig)</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 border-t border-gray-200 dark:border-gray-700 pt-6">
                <button type="button" wire:click="resetForm"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    :disabled="isSubmitting">
                    Reset
                </button>

                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">
                        Site Aanmaken
                    </span>
                    <span wire:loading wire:target="submit" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Bezig met aanmaken...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>

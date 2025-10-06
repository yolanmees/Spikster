<div>
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-4">
        <div class="overflow-hidden bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-blue-100 rounded-lg dark:bg-blue-900">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 w-0 ml-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">Total Modules</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $modules->total() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-green-100 rounded-lg dark:bg-green-900">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 w-0 ml-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">Active</dt>
                            <dd class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ \App\Models\Module::active()->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-gray-100 rounded-lg dark:bg-gray-700">
                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 w-0 ml-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">Inactive</dt>
                            <dd class="text-2xl font-bold text-gray-600 dark:text-gray-400">
                                {{ \App\Models\Module::where('is_active', false)->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-purple-100 rounded-lg dark:bg-purple-900">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 w-0 ml-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">Categories</dt>
                            <dd class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {{ $categories->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="mb-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                {{-- Search --}}
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        class="block w-full py-2 pl-10 pr-3 leading-5 text-gray-900 placeholder-gray-500 bg-white border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Search modules...">
                </div>

                {{-- Category Filter --}}
                <div>
                    <select wire:model.live="category"
                        class="block w-full py-2 pl-3 pr-10 leading-5 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <select wire:model.live="statusFilter"
                        class="block w-full py-2 pl-3 pr-10 leading-5 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="all">All Status</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Modules Grid --}}
    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($modules as $module)
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden transition-all duration-200 hover:shadow-xl hover:scale-[1.02]">
                {{-- Module Header --}}
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            @if ($module->icon)
                                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 rounded-lg"
                                    style="background-color: {{ $module->color ?? '#3B82F6' }}20">
                                    <i class="{{ $module->icon }} text-2xl"
                                        style="color: {{ $module->color ?? '#3B82F6' }}"></i>
                                </div>
                            @else
                                <div
                                    class="flex items-center justify-center flex-shrink-0 w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600">
                                    <span class="text-xl font-bold text-white">{{ substr($module->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $module->name }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">v{{ $module->version ?? '1.0.0' }}
                                </p>
                            </div>
                        </div>

                        {{-- Status Badge --}}
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $module->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $module->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                {{-- Module Body --}}
                <div class="p-6">
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400 line-clamp-3">
                        {{ $module->description }}
                    </p>

                    @if ($module->category)
                        <div class="mb-4">
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 rounded-md bg-blue-50 dark:bg-blue-900 dark:text-blue-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                {{ ucfirst($module->category) }}
                            </span>
                        </div>
                    @endif

                    {{-- Module Meta --}}
                    <div class="flex items-center justify-between mb-4 text-xs text-gray-500 dark:text-gray-400">
                        <div class="flex items-center space-x-3">
                            @if ($module->menuItems && $module->menuItems->count() > 0)
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16"></path>
                                    </svg>
                                    {{ $module->menuItems->count() }} menu
                                </span>
                            @endif
                            @if ($module->dependencies && $module->dependencies->count() > 0)
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    {{ $module->dependencies->count() }} deps
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Module Actions --}}
                <div
                    class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50 dark:bg-gray-900 dark:border-gray-700">
                    {{-- Toggle Switch --}}
                    <div class="flex items-center">
                        <button wire:click="toggleModule({{ $module->id }})" type="button"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $module->is_active ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700' }}"
                            role="switch" aria-checked="{{ $module->is_active ? 'true' : 'false' }}">
                            <span
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $module->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $module->is_active ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center space-x-2">
                        {{-- Health Check --}}
                        @if ($module->is_active)
                            <button wire:click="checkHealth({{ $module->id }})"
                                class="p-2 text-gray-400 transition-colors hover:text-blue-600 dark:hover:text-blue-400"
                                title="Health Check">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </button>
                        @endif

                        {{-- View Details --}}
                        <button wire:click="showDetails({{ $module->id }})"
                            class="p-2 text-gray-400 transition-colors hover:text-blue-600 dark:hover:text-blue-400"
                            title="View Details">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="col-span-full">
                <div class="py-12 text-center bg-white rounded-lg shadow dark:bg-gray-800">
                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                        </path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No modules found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $search || $category || $statusFilter !== 'all' ? 'Try adjusting your filters' : 'No modules are installed yet' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $modules->links() }}
    </div>

    {{-- Module Details Modal --}}
    @if ($showingModule && $moduleDetails)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div wire:click="closeDetails" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                    aria-hidden="true"></div>

                {{-- Modal panel --}}
                <div
                    class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-800 sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    {{-- Header --}}
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                @if ($moduleDetails->icon)
                                    <div
                                        class="flex items-center justify-center w-12 h-12 bg-white rounded-lg bg-opacity-20">
                                        <i class="{{ $moduleDetails->icon }} text-2xl text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="text-xl font-bold text-white">{{ $moduleDetails->name }}</h3>
                                    <p class="text-sm text-blue-100">{{ $moduleDetails->description }}</p>
                                </div>
                            </div>
                            <button wire:click="closeDetails"
                                class="text-white transition-colors hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-6 overflow-y-auto max-h-96">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            {{-- Version --}}
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Version</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $moduleDetails->version ?? '1.0.0' }}</dd>
                            </div>

                            {{-- Status --}}
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                <dd class="mt-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $moduleDetails->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ $moduleDetails->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>

                            {{-- Category --}}
                            @if ($moduleDetails->category)
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Category</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ ucfirst($moduleDetails->category) }}</dd>
                                </div>
                            @endif

                            {{-- Author --}}
                            @if ($moduleDetails->author)
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Author</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $moduleDetails->author }}</dd>
                                </div>
                            @endif

                            {{-- Dependencies --}}
                            @if ($moduleDetails->dependencies && $moduleDetails->dependencies->count() > 0)
                                <div class="sm:col-span-2">
                                    <dt class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">Dependencies
                                    </dt>
                                    <dd class="flex flex-wrap gap-2">
                                        @foreach ($moduleDetails->dependencies as $dep)
                                            <span
                                                class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                                {{ $dep->requiredModule->name ?? $dep->required_module_id }}
                                            </span>
                                        @endforeach
                                    </dd>
                                </div>
                            @endif

                            {{-- Menu Items --}}
                            @if ($moduleDetails->menuItems && $moduleDetails->menuItems->count() > 0)
                                <div class="sm:col-span-2">
                                    <dt class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">Menu Items
                                    </dt>
                                    <dd>
                                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ($moduleDetails->menuItems as $menuItem)
                                                <li class="flex items-center py-2">
                                                    @if ($menuItem->icon)
                                                        <i
                                                            class="{{ $menuItem->icon }} w-5 h-5 mr-2 text-gray-400"></i>
                                                    @endif
                                                    <span
                                                        class="text-sm text-gray-900 dark:text-white">{{ $menuItem->name }}</span>
                                                    <span
                                                        class="ml-auto text-xs text-gray-500 dark:text-gray-400">{{ $menuItem->route }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </dd>
                                </div>
                            @endif

                            {{-- Permissions --}}
                            @if ($moduleDetails->permissions && $moduleDetails->permissions->count() > 0)
                                <div class="sm:col-span-2">
                                    <dt class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">Permissions
                                    </dt>
                                    <dd class="flex flex-wrap gap-2">
                                        @foreach ($moduleDetails->permissions as $perm)
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-purple-800 bg-purple-100 rounded dark:bg-purple-900 dark:text-purple-200">
                                                {{ $perm->permission->name ?? 'N/A' }}
                                            </span>
                                        @endforeach
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end px-6 py-4 space-x-3 bg-gray-50 dark:bg-gray-900">
                        <button wire:click="closeDetails" type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg dark:border-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Close
                        </button>
                        <button wire:click="toggleModule({{ $moduleDetails->id }})" type="button"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-white {{ $moduleDetails->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ $moduleDetails->is_active ? 'Disable Module' : 'Enable Module' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

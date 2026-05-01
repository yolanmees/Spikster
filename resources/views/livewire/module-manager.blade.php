<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-semibold">Module Manager</h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Manage and configure your Spikster modules. Enable or disable features to customize your control panel.</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Modules</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $modules->total() }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                    <i data-lucide="layers-3" class="h-5 w-5"></i>
                </span>
            </div>
        </article>

        <article class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Active</p>
                    <p class="mt-2 text-2xl font-semibold">{{ \App\Models\Module::active()->count() }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                    <i data-lucide="check-circle" class="h-5 w-5"></i>
                </span>
            </div>
        </article>

        <article class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Inactive</p>
                    <p class="mt-2 text-2xl font-semibold">{{ \App\Models\Module::where('is_active', false)->count() }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                    <i data-lucide="circle-off" class="h-5 w-5"></i>
                </span>
            </div>
        </article>

        <article class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Categories</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $categories->count() }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                    <i data-lucide="folder-tree" class="h-5 w-5"></i>
                </span>
            </div>
        </article>
    </div>

    {{-- Filters --}}
    <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-2 sm:flex-row">
            <div class="relative">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"></i>
                <input wire:model.live.debounce.300ms="search"
                       class="h-9 w-full rounded-lg border border-zinc-200 bg-white pl-9 pr-3 text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 sm:w-64"
                       placeholder="Search modules...">
            </div>
            <select wire:model.live="category"
                    class="h-9 rounded-lg border border-zinc-200 bg-white px-3 text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter"
                    class="h-9 rounded-lg border border-zinc-200 bg-white px-3 text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950">
                <option value="all">All Status</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>
    </div>

    {{-- Modules Grid --}}
    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($modules as $module)
            <article class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-soft dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        @if ($module->icon)
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg text-white"
                                  style="background-color: {{ $module->color ?? '#7c3aed' }}">
                                <i class="{{ $module->icon }} h-4 w-4"></i>
                            </span>
                        @else
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-700 text-sm font-bold text-white">
                                {{ substr($module->name, 0, 1) }}
                            </span>
                        @endif
                        <div class="min-w-0">
                            <h3 class="truncate text-sm font-semibold">{{ $module->name }}</h3>
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">v{{ $module->version ?? '1.0.0' }}</p>
                        </div>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $module->is_active ? 'bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                        {{ $module->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <p class="mt-5 text-sm text-zinc-500 dark:text-zinc-400 line-clamp-3">{{ $module->description }}</p>

                @if ($module->category)
                    <div class="mt-4">
                        <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ ucfirst($module->category) }}
                        </span>
                    </div>
                @endif

                <div class="mt-5 grid grid-cols-2 gap-3 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-950">
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Menu items</p>
                        <p class="mt-1 text-sm font-semibold">{{ $module->menuItems->count() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Dependencies</p>
                        <p class="mt-1 text-sm font-semibold">{{ $module->dependencies->count() }}</p>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between">
                    <button wire:click="toggleModule({{ $module->id }})"
                            type="button"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-700 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 {{ $module->is_active ? 'bg-purple-700' : 'bg-zinc-200 dark:bg-zinc-700' }}"
                            role="switch"
                            aria-checked="{{ $module->is_active ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $module->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                    <div class="flex gap-2">
                        @if ($module->is_active)
                            <button wire:click="checkHealth({{ $module->id }})"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800"
                                    title="Health Check">
                                <i data-lucide="activity" class="h-4 w-4"></i>
                            </button>
                        @endif
                        <button wire:click="showDetails({{ $module->id }})"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800"
                                title="View details">
                            <i data-lucide="more-horizontal" class="h-4 w-4"></i>
                        </button>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full">
                <div class="flex min-h-56 flex-col items-center justify-center rounded-lg border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                    <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                        <i data-lucide="folder-open" class="h-5 w-5"></i>
                    </span>
                    <h3 class="mt-4 text-sm font-semibold">No modules found</h3>
                    <p class="mt-2 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $search || $category || $statusFilter !== 'all' ? 'Try adjusting your filters.' : 'No modules are installed yet.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($modules->hasPages())
        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Showing <span class="font-medium">{{ $modules->firstItem() }}</span> to <span class="font-medium">{{ $modules->lastItem() }}</span> of <span class="font-medium">{{ $modules->total() }}</span> modules
            </p>
            <div>
                {{ $modules->links() }}
            </div>
        </div>
    @endif

    {{-- Module Details Modal --}}
    @if ($showingModule && $moduleDetails)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/45 px-4 backdrop-blur-sm"
             aria-labelledby="modal-title"
             role="dialog"
             aria-modal="true">
            <section class="w-full max-w-lg rounded-lg border border-zinc-200 bg-white shadow-soft dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-200 p-5 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        @if ($moduleDetails->icon)
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg text-white"
                                  style="background-color: {{ $moduleDetails->color ?? '#7c3aed' }}">
                                <i class="{{ $moduleDetails->icon }} h-4 w-4"></i>
                            </span>
                        @else
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-700 text-sm font-bold text-white">
                                {{ substr($moduleDetails->name, 0, 1) }}
                            </span>
                        @endif
                        <div>
                            <h2 class="text-base font-semibold">{{ $moduleDetails->name }}</h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $moduleDetails->description }}</p>
                        </div>
                    </div>
                    <button wire:click="closeDetails"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <div class="space-y-4 p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Version</p>
                            <p class="mt-1 text-sm font-semibold">{{ $moduleDetails->version ?? '1.0.0' }}</p>
                        </div>
                        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Status</p>
                            <p class="mt-1">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $moduleDetails->is_active ? 'bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                    {{ $moduleDetails->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    @if ($moduleDetails->category || $moduleDetails->author)
                        <div class="grid gap-4 sm:grid-cols-2">
                            @if ($moduleDetails->category)
                                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Category</p>
                                    <p class="mt-1 text-sm font-semibold">{{ ucfirst($moduleDetails->category) }}</p>
                                </div>
                            @endif
                            @if ($moduleDetails->author)
                                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Author</p>
                                    <p class="mt-1 text-sm font-semibold">{{ $moduleDetails->author }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($moduleDetails->dependencies && $moduleDetails->dependencies->count() > 0)
                        <div>
                            <p class="text-sm font-medium">Dependencies</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($moduleDetails->dependencies as $dep)
                                    <span class="rounded-full bg-purple-100 px-2.5 py-1 text-xs font-medium text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                                        {{ $dep->requiredModule->name ?? $dep->required_module_id }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($moduleDetails->menuItems && $moduleDetails->menuItems->count() > 0)
                        <div>
                            <p class="text-sm font-medium">Menu Items</p>
                            <div class="mt-2 space-y-2">
                                @foreach ($moduleDetails->menuItems as $menuItem)
                                    <div class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                                        @if ($menuItem->icon)
                                            <span class="text-zinc-400">{!! $menuItem->icon !!}</span>
                                        @endif
                                        <span class="flex-1 text-sm font-medium">{{ $menuItem->name }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $menuItem->route }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($moduleDetails->permissions && $moduleDetails->permissions->count() > 0)
                        <div>
                            <p class="text-sm font-medium">Permissions</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($moduleDetails->permissions as $perm)
                                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $perm->permission->name ?? 'N/A' }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 p-5 dark:border-zinc-800 sm:flex-row sm:justify-end">
                    <button wire:click="closeDetails"
                            type="button"
                            class="inline-flex items-center justify-center rounded-lg border border-zinc-200 px-3 py-2 text-sm font-medium hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        Close
                    </button>
                    <button wire:click="toggleModule({{ $moduleDetails->id }})"
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-purple-700 px-3 py-2 text-sm font-medium text-white hover:bg-purple-800">
                        <i data-lucide="{{ $moduleDetails->is_active ? 'circle-off' : 'check-circle' }}" class="h-4 w-4"></i>
                        {{ $moduleDetails->is_active ? 'Disable Module' : 'Enable Module' }}
                    </button>
                </div>
            </section>
        </div>
    @endif
</div>

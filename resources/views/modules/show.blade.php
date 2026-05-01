@extends('layouts.app')

@section('title', $module->name . ' — Module Details')

@section('content')
    <div class="space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                    <a href="{{ route('modules.index') }}" class="hover:text-zinc-900 dark:hover:text-white">Modules</a>
                    <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
                    <span>{{ $module->name }}</span>
                </div>
                <h1 class="mt-1 text-lg font-semibold sm:text-xl">{{ $module->name }}</h1>
            </div>
            <form method="POST" action="{{ route('modules.toggle', $module->id) }}" class="shrink-0">
                @csrf
                @if ($module->is_active)
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-medium shadow-sm hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:bg-zinc-800">
                        <i data-lucide="circle-off" class="h-4 w-4"></i>
                        Disable Module
                    </button>
                @else
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-purple-700 px-3 py-2 text-sm font-medium text-white hover:bg-purple-800">
                        <i data-lucide="check-circle" class="h-4 w-4"></i>
                        Enable Module
                    </button>
                @endif
            </form>
        </div>

        {{-- Module Header Card --}}
        <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-lg text-white"
                      style="background-color: {{ $module->color ?? '#7c3aed' }}">
                    @if ($module->icon)
                        <i class="{{ $module->icon }} h-5 w-5"></i>
                    @else
                        <span class="text-lg font-bold">{{ substr($module->name, 0, 1) }}</span>
                    @endif
                </span>
                <div class="min-w-0">
                    <h3 class="text-base font-semibold">{{ $module->name }}</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $module->description }}</p>
                    <div class="mt-3 flex items-center gap-3">
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $module->is_active ? 'bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            {{ $module->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @if ($module->version)
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">v{{ $module->version }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            {{-- Menu Items --}}
            <section class="rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 p-5 dark:border-zinc-800">
                    <h2 class="text-base font-semibold">Menu Items</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Navigation entries registered by this module.</p>
                </div>
                @if ($module->menuItems->isEmpty())
                    <div class="p-5">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">No menu items registered</p>
                    </div>
                @else
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach ($module->menuItems as $item)
                            <div class="flex items-center justify-between p-5">
                                <div class="flex items-center gap-3">
                                    @if ($item->icon)
                                        <span class="text-zinc-400">{!! $item->icon !!}</span>
                                    @endif
                                    <span class="text-sm font-medium">{{ $item->title }}</span>
                                </div>
                                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $item->menu_location }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Metadata Sidebar --}}
            <aside class="space-y-6">
                {{-- Module Information --}}
                <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-base font-semibold">Module Information</h2>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Slug</p>
                            <p class="mt-1 font-mono text-sm font-semibold">{{ $module->slug }}</p>
                        </div>
                        @if ($module->version)
                            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Version</p>
                                <p class="mt-1 text-sm font-semibold">{{ $module->version }}</p>
                            </div>
                        @endif
                        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Registered At</p>
                            <p class="mt-1 text-sm font-semibold">{{ $module->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Last Updated</p>
                            <p class="mt-1 text-sm font-semibold">{{ $module->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </section>

                {{-- Permissions --}}
                <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-base font-semibold">Permissions</h2>
                    @if ($module->permissions->isEmpty())
                        <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">No permissions registered</p>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($module->permissions as $permission)
                                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                                    <p class="text-sm font-medium">{{ $permission->name }}</p>
                                    @if ($permission->description)
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $permission->description }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </aside>
        </div>

    </div>
@endsection

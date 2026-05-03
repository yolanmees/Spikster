{{-- Desktop sidebar (static) --}}
<div class="hidden xl:fixed xl:inset-y-0 xl:z-50 xl:flex xl:w-72 xl:flex-col">
    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white dark:bg-zinc-950 border-r border-zinc-200 dark:border-zinc-800 px-6 pb-4">

        {{-- Logo --}}
        <div class="flex h-16 shrink-0 items-center border-b border-zinc-200 dark:border-zinc-800 gap-3">
            <div class="w-8 h-8 rounded-lg bg-zinc-950 dark:bg-white flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5 text-white dark:text-zinc-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="font-bold text-zinc-950 dark:text-zinc-100 text-xl tracking-tight">{{ config('app.name') }}</span>
        </div>

        {{-- Nav --}}
        <nav class="flex flex-1 flex-col">
            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                <li>
                    <ul role="list" class="-mx-2 space-y-1">
                        @include('layouts.components.sidebar-nav-items')
                    </ul>
                </li>

                {{-- User footer --}}
                <li class="-mx-6 mt-auto border-t border-zinc-200 dark:border-zinc-800">
                    <a href="{{ route('profile.show') }}" wire:navigate
                        class="flex items-center gap-x-4 px-6 py-4 text-sm font-semibold leading-6 text-zinc-700 dark:text-zinc-300 hover:text-zinc-950 dark:hover:text-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-all duration-200">
                        <div class="h-9 w-9 rounded-full bg-zinc-950 dark:bg-white flex items-center justify-center text-white dark:text-zinc-950 font-bold text-xs shrink-0">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-zinc-950 dark:text-zinc-100 font-semibold truncate">{{ Auth::user()->name ?? '' }}</span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">Profile</span>
                        </div>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

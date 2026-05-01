{{-- Mobile sidebar (off-canvas) --}}
<div x-show="sidebarOpen" x-cloak class="relative z-50 xl:hidden" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>

    {{-- Panel --}}
    <div class="fixed inset-0 flex">
        <div x-show="sidebarOpen"
            x-transition:enter="transition ease-in-out duration-200 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-200 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="relative mr-16 flex w-full max-w-xs flex-1">

            {{-- Close button --}}
            <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                <button type="button" @click="sidebarOpen = false" class="-m-2.5 p-2.5 text-white">
                    <span class="sr-only">Close sidebar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Sidebar content (same as desktop) --}}
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white dark:bg-zinc-950 border-r border-zinc-200 dark:border-zinc-800 px-6 pb-4">
                <div class="flex h-16 shrink-0 items-center border-b border-zinc-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-zinc-950 dark:bg-white flex items-center justify-center">
                            <svg class="w-5 h-5 text-white dark:text-zinc-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span class="font-bold text-zinc-950 dark:text-zinc-100 text-lg">{{ config('app.name') }}</span>
                    </div>
                </div>
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">
                                @include('layouts.components.sidebar-nav-items')
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

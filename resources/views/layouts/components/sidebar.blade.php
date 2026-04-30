{{-- Desktop sidebar (static) --}}
<div class="hidden xl:fixed xl:inset-y-0 xl:z-50 xl:flex xl:w-72 xl:flex-col">
    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gradient-to-b from-gray-900 to-gray-950 px-6 pb-4 shadow-2xl ring-1 ring-white/10">

        {{-- Logo --}}
        <div class="flex h-16 shrink-0 items-center border-b border-white/10 gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="font-bold text-white text-xl tracking-tight">{{ config('app.name') }}</span>
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
                <li class="-mx-6 mt-auto border-t border-white/10">
                    <a href="{{ route('profile.show') }}"
                        class="flex items-center gap-x-4 px-6 py-4 text-sm font-semibold leading-6 text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-200">
                        <div class="h-9 w-9 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs shrink-0">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-white font-semibold truncate">{{ Auth::user()->name ?? '' }}</span>
                            <span class="text-xs text-gray-400">Profile</span>
                        </div>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

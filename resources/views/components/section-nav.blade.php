@props([
    'items'  => [],   // [['href' => '', 'label' => '', 'icon' => null, 'active' => false], ...]
    'title'  => 'Navigation',
])

<div>
    {{-- Mobile: horizontal scroll tabs (visible below lg) --}}
    <div class="lg:hidden -mx-4 px-4 overflow-x-auto scrollbar-none border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 mb-4">
        <nav class="flex gap-1 py-2 min-w-max">
            @foreach ($items as $item)
                <a href="{{ $item['href'] }}"
                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium transition-colors
                        {{ $item['active']
                            ? 'bg-purple-100 text-purple-800 dark:bg-purple-700/20 dark:text-purple-200'
                            : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                    @if ($item['active'])
                        <span class="h-1.5 w-1.5 rounded-full bg-purple-700 dark:bg-purple-300"></span>
                    @endif
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Desktop: sidebar + content grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <aside class="hidden lg:block lg:col-span-3">
            <div class="sticky top-20 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-3">
                <p class="px-2 py-1 text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ $title }}</p>
                <nav class="mt-2 space-y-1">
                    @foreach ($items as $item)
                        <a href="{{ $item['href'] }}"
                            class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                                {{ $item['active']
                                    ? 'bg-purple-100 text-purple-800 dark:bg-purple-700/20 dark:text-purple-200'
                                    : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                            @if (!empty($item['icon']))
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $item['icon'] !!}
                                </svg>
                            @endif
                            <span class="flex-1">{{ $item['label'] }}</span>
                            @if ($item['active'])
                                <span class="h-2 w-2 rounded-full bg-purple-700 dark:bg-purple-300 shrink-0"></span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>
        </aside>

        <div class="lg:col-span-9 space-y-4">
            {{ $slot }}
        </div>
    </div>
</div>

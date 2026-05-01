{{--
    x-info-grid — a horizontal grid of labelled stat/info tiles.

    Usage:
        <x-info-grid :items="[
            ['label' => 'Domain', 'value' => $site->domain, 'icon' => 'globe', 'color' => 'blue'],
            ['label' => 'Server', 'value' => $site->server->name, 'icon' => 'server', 'color' => 'purple'],
        ]" />

    Each item: label, value, icon (optional), color (optional: blue|purple|green|orange|gray)
--}}
@props(['items' => [], 'cols' => null])

@php
    $colCount = $cols ?? count($items);
    $gridCols = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 sm:grid-cols-2',
        3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
    ][$colCount] ?? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4';

    $colorMap = [
        'blue'   => ['bg' => 'bg-purple-50 dark:bg-purple-900/20',  'icon' => 'text-purple-700 dark:text-purple-400'],
        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-900/20', 'icon' => 'text-purple-600 dark:text-purple-400'],
        'green'  => ['bg' => 'bg-green-50 dark:bg-green-900/20',  'icon' => 'text-green-600 dark:text-green-400'],
        'orange' => ['bg' => 'bg-orange-50 dark:bg-orange-900/20', 'icon' => 'text-orange-600 dark:text-orange-400'],
        'gray'   => ['bg' => 'bg-zinc-100 dark:bg-zinc-800',       'icon' => 'text-zinc-500 dark:text-zinc-400'],
    ];
@endphp

<div class="p-4 mb-6 bg-white border border-zinc-200 dark:bg-zinc-900 rounded-xl dark:border-zinc-800">
    <div class="grid {{ $gridCols }} gap-4">
        @foreach ($items as $item)
            @php $c = $colorMap[$item['color'] ?? 'blue'] ?? $colorMap['blue']; @endphp
            <div class="flex items-center gap-3">
                @if (!empty($item['icon']))
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg {{ $c['bg'] }} shrink-0">
                        <x-icon :icon="$item['icon']" class="w-5 h-5 {{ $c['icon'] }}" />
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item['label'] }}</p>
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white truncate">{{ $item['value'] ?? '—' }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>

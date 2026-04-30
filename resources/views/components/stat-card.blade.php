@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'blue',
    'suffix' => null,
])

@php
    $colorMap = [
        'blue'   => ['icon' => 'text-blue-500 dark:text-blue-400',    'bg' => 'bg-blue-50 dark:bg-blue-900/20'],
        'green'  => ['icon' => 'text-green-500 dark:text-green-400',  'bg' => 'bg-green-50 dark:bg-green-900/20'],
        'yellow' => ['icon' => 'text-yellow-500 dark:text-yellow-400','bg' => 'bg-yellow-50 dark:bg-yellow-900/20'],
        'red'    => ['icon' => 'text-red-500 dark:text-red-400',      'bg' => 'bg-red-50 dark:bg-red-900/20'],
        'purple' => ['icon' => 'text-purple-500 dark:text-purple-400','bg' => 'bg-purple-50 dark:bg-purple-900/20'],
        'gray'   => ['icon' => 'text-gray-500 dark:text-gray-400',    'bg' => 'bg-gray-100 dark:bg-gray-700/40'],
    ];
    $colors = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border bg-white dark:bg-gray-800 dark:border-gray-700/50 border-gray-200 shadow-sm p-6 flex items-center gap-4']) }}>
    @if ($icon)
        <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-xl {{ $colors['bg'] }}">
            <div class="{{ $colors['icon'] }}">{!! $icon !!}</div>
        </div>
    @endif
    <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ $title }}</p>
        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
            {{ $value }}{{ $suffix ? ' ' . $suffix : '' }}
        </p>
        @isset($extra)
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $extra }}</div>
        @endisset
    </div>
</div>

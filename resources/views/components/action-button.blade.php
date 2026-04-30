@props(['href' => null, 'variant' => 'default'])

@php
    $tag = $href ? 'a' : 'button';
    $base = 'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1';
    $styles = [
        'default' => 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 ring-1 ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-blue-500',
        'primary' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 focus:ring-blue-500',
        'danger'  => 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 focus:ring-red-500',
    ];
    $classes = $base . ' ' . ($styles[$variant] ?? $styles['default']);
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @else type="button" @endif
    {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</{{ $tag }}>

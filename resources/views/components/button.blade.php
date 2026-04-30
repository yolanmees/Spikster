@props([
    'type'     => 'button',
    'variant'  => 'primary',
    'outline'  => false,
    'size'     => 'md',
    'loading'  => false,
    'disabled' => false,
    'icon'     => null,
])

@php
    $sizes = [
        'xs' => 'px-2.5 py-1 text-xs gap-1',
        'sm' => 'px-3 py-1.5 text-sm gap-1.5',
        'md' => 'px-4 py-2 text-sm gap-2',
        'lg' => 'px-5 py-2.5 text-base gap-2',
    ];

    $filled = [
        'primary'   => 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white shadow-sm shadow-blue-600/20 focus:ring-blue-500',
        'secondary' => 'bg-gray-600 hover:bg-gray-700 active:bg-gray-800 text-white focus:ring-gray-500',
        'danger'    => 'bg-red-600 hover:bg-red-700 active:bg-red-800 text-white shadow-sm shadow-red-600/20 focus:ring-red-500',
        'success'   => 'bg-green-600 hover:bg-green-700 active:bg-green-800 text-white focus:ring-green-500',
        'warning'   => 'bg-yellow-500 hover:bg-yellow-600 active:bg-yellow-700 text-white focus:ring-yellow-500',
        'info'      => 'bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white focus:ring-purple-500',
        'light'     => 'bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:active:bg-gray-500 dark:text-gray-200 focus:ring-gray-400',
        'dark'      => 'bg-gray-800 hover:bg-gray-900 text-white dark:bg-gray-700 dark:hover:bg-gray-600 focus:ring-gray-600',
        'link'      => 'text-blue-600 hover:text-blue-700 underline dark:text-blue-400 dark:hover:text-blue-300 focus:ring-blue-400',
        'ghost'     => 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:ring-gray-400',
    ];

    $outlined = [
        'primary'   => 'border-2 border-blue-600 text-blue-600 hover:bg-blue-50 dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-900/20 focus:ring-blue-500',
        'secondary' => 'border-2 border-gray-400 text-gray-600 hover:bg-gray-50 dark:border-gray-500 dark:text-gray-300 dark:hover:bg-gray-800 focus:ring-gray-400',
        'danger'    => 'border-2 border-red-600 text-red-600 hover:bg-red-50 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-900/20 focus:ring-red-500',
        'success'   => 'border-2 border-green-600 text-green-600 hover:bg-green-50 dark:border-green-500 dark:text-green-400 dark:hover:bg-green-900/20 focus:ring-green-500',
    ];

    $variantClass = $outline
        ? ($outlined[$variant] ?? $outlined['primary'])
        : ($filled[$variant] ?? $filled['primary']);

    $base = 'inline-flex items-center justify-center font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-1 transition-all duration-150';
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $disabledClass = ($disabled || $loading) ? 'opacity-60 cursor-not-allowed pointer-events-none' : '';

    $classes = "$base $sizeClass $variantClass $disabledClass";
@endphp

<button
    type="{{ $type }}"
    @if($disabled || $loading) disabled @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if($loading)
        <svg class="animate-spin {{ $size === 'xs' || $size === 'sm' ? 'h-3.5 w-3.5' : 'h-4 w-4' }} shrink-0"
            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @elseif($icon)
        <span class="{{ $size === 'xs' || $size === 'sm' ? 'h-3.5 w-3.5' : 'h-4 w-4' }} shrink-0">{!! $icon !!}</span>
    @endif

    {{ $slot }}
</button>

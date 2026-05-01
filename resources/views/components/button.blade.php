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
        'primary'   => 'bg-zinc-950 hover:bg-zinc-800 active:bg-zinc-700 text-white shadow-sm focus:ring-zinc-500 dark:bg-zinc-100 dark:text-zinc-950 dark:hover:bg-zinc-200',
        'secondary' => 'bg-white border border-zinc-200 text-zinc-900 hover:bg-zinc-50 active:bg-zinc-100 shadow-sm focus:ring-zinc-400 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800',
        'danger'    => 'bg-red-600 hover:bg-red-700 active:bg-red-800 text-white shadow-sm shadow-red-600/20 focus:ring-red-500',
        'success'   => 'bg-green-600 hover:bg-green-700 active:bg-green-800 text-white focus:ring-green-500',
        'warning'   => 'bg-yellow-500 hover:bg-yellow-600 active:bg-yellow-700 text-white focus:ring-yellow-500',
        'info'      => 'bg-purple-700 hover:bg-purple-800 active:bg-purple-900 text-white shadow-sm shadow-purple-700/20 focus:ring-purple-500',
        'light'     => 'bg-zinc-100 hover:bg-zinc-200 active:bg-zinc-300 text-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:active:bg-zinc-600 dark:text-zinc-200 focus:ring-zinc-400',
        'dark'      => 'bg-zinc-900 hover:bg-zinc-950 text-white dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:ring-zinc-600',
        'link'      => 'text-purple-700 hover:text-purple-800 underline dark:text-purple-400 dark:hover:text-purple-300 focus:ring-purple-400',
        'ghost'     => 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-800 focus:ring-zinc-400',
    ];

    $outlined = [
        'primary'   => 'border-2 border-zinc-950 text-zinc-950 hover:bg-zinc-50 dark:border-zinc-400 dark:text-zinc-300 dark:hover:bg-zinc-800 focus:ring-zinc-500',
        'secondary' => 'border-2 border-zinc-300 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800 focus:ring-zinc-400',
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

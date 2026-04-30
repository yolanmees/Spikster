@props([
    'size'    => 'md',
    'variant' => 'default',  {{-- 'default' | 'glass' | 'flat' --}}
    'noPad'   => false,
])

@php
    $wrapperClass = match($variant) {
        'glass' => 'rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md shadow-xl dark:bg-gray-800/40 dark:border-gray-700/30',
        'flat'  => 'rounded-xl border border-gray-200 dark:border-gray-700/50 bg-transparent',
        default => 'rounded-xl border border-gray-200 dark:border-gray-700/50 bg-white dark:bg-gray-800/50 shadow-sm hover:shadow-md transition-shadow duration-200',
    };

    $headerClass = 'flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700/60 font-semibold text-sm text-gray-700 dark:text-gray-300';
    $bodyClass   = $noPad ? '' : 'p-6';
    $sizeClass   = ['sm' => 'text-sm', 'md' => '', 'lg' => 'text-base'][$size] ?? '';

    $hasHeader = isset($header) && trim((string)$header) !== '';
@endphp

<div {{ $attributes->merge(['class' => "$wrapperClass $sizeClass"]) }}>
    @if($hasHeader)
        <div class="{{ $headerClass }}">{{ $header }}</div>
    @endif

    <div class="{{ $bodyClass }}">{{ $slot }}</div>
</div>

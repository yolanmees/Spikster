@props([
    'size'    => 'md',
    'variant' => 'default',  {{-- 'default' | 'glass' | 'flat' --}}
    'noPad'   => false,
])

@php
    $wrapperClass = match($variant) {
        'glass' => 'rounded-2xl border border-zinc-200/50 bg-white/5 backdrop-blur-md shadow-xl dark:bg-zinc-800/40 dark:border-zinc-700/30',
        'flat'  => 'rounded-xl border border-zinc-200 dark:border-zinc-700/50 bg-transparent',
        default => 'rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm hover:shadow-md transition-shadow duration-200',
    };

    $headerClass = 'flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800 font-semibold text-sm text-zinc-700 dark:text-zinc-300';
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

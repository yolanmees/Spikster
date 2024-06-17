@props([
    'header' => '',
    'size' => 'md',
])

@php
    $cardClasses = [
        'base' => 'rounded-lg border overflow-hidden transition-colors',
        'sizes' => [
            'sm' => 'text-sm',
            'md' => 'text-base',
            'lg' => 'text-lg',
        ],
        'light' => [
            'card' => 'bg-white border-gray-400',
            'header' => 'bg-gray-100 text-gray-700 py-3 px-4 border-b border-gray-400',
            'body' => 'p-4',
        ],
        'dark' => [
            'card' => 'dark:bg-gray-800 dark:border-gray-700 dark:text-white',
            'header' => 'dark:bg-gray-900 dark:text-gray-300 py-3 px-4 dark:border-b dark:border-gray-700',
            'body' => 'p-4',
        ],
    ];

    $cardClassesMerged = "{$cardClasses['base']} {$cardClasses['light']['card']} {$cardClasses['dark']['card']} {$cardClasses['sizes'][$size]}";
    $headerClassesMerged = "{$cardClasses['light']['header']} {$cardClasses['dark']['header']}";
    $bodyClassesMerged = "{$cardClasses['light']['body']} {$cardClasses['dark']['body']}";
@endphp

<div {{ $attributes->merge(['class' => $cardClassesMerged]) }}>
    @if ($header)
        <div class="{{ $headerClassesMerged }}">
            {{ $header }}
        </div>
    @endif

    <div class="{{ $bodyClassesMerged }}">
        {{ $slot }}
    </div>
</div>
@props([
    'header' => '',
    'size' => 'md',
])

@php
    $cardClasses = [
        'base' => 'rounded-xl border shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md',
        'sizes' => [
            'sm' => 'text-sm',
            'md' => 'text-base',
            'lg' => 'text-lg',
        ],
        'light' => [
            'card' => 'bg-white border-gray-200',
            'header' => 'bg-gray-50/50 text-gray-900 font-semibold py-4 px-6 border-b border-gray-100',
            'body' => 'p-6',
        ],
        'dark' => [
            'card' => 'dark:bg-gray-800/50 dark:border-gray-700/50 dark:text-white dark:backdrop-blur-sm',
            'header' => 'dark:bg-gray-800/80 dark:text-white py-4 px-6 dark:border-b dark:border-gray-700/50',
            'body' => 'p-6',
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
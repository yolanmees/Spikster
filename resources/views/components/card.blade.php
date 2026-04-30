@props([
    'size' => 'md',
])

@php
    $sizes = ['sm' => 'text-sm', 'md' => 'text-base', 'lg' => 'text-lg'];

    $cardClass = implode(' ', [
        'rounded-xl border shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md',
        'bg-white border-gray-200',
        'dark:bg-gray-800/50 dark:border-gray-700/50 dark:text-white dark:backdrop-blur-sm',
        $sizes[$size] ?? $sizes['md'],
    ]);

    $headerClass = implode(' ', [
        'flex items-center justify-between',
        'bg-gray-50/50 text-gray-900 font-semibold py-4 px-6 border-b border-gray-100',
        'dark:bg-gray-800/80 dark:text-white dark:border-gray-700/50',
    ]);

    $bodyClass = 'p-6';

    $hasHeader = isset($header) && (is_string($header) ? trim($header) !== '' : (string) $header !== '');
@endphp

<div {{ $attributes->merge(['class' => $cardClass]) }}>
    @if ($hasHeader)
        <div class="{{ $headerClass }}">
            {{ $header }}
        </div>
    @endif

    <div class="{{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>

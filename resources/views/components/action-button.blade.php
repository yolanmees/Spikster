@props(['href' => null])

@php
    $tag = $href ? 'a' : 'button';
    $baseClasses = 'inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded-md shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';
    $defaultClasses = 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-blue-500';
@endphp

<{{ $tag }} 
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => $baseClasses . ' ' . $defaultClasses]) }}
>
    {{ $slot }}
</{{ $tag }}>

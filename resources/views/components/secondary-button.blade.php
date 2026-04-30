@props(['size' => 'md'])
@php
    $sizes = ['sm' => 'px-3 py-1.5 text-xs', 'md' => 'px-4 py-2.5 text-sm', 'lg' => 'px-5 py-3 text-base'];
    $sz = $sizes[$size] ?? $sizes['md'];
@endphp
<button {{ $attributes->merge(['type' => 'button', 'class' => "inline-flex items-center justify-center gap-2 {$sz} bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-lg font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200 transform hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"]) }}>
    {{ $slot }}
</button>

@props(['size' => 'md'])
@php
    $sizes = ['sm' => 'px-3 py-1.5 text-xs', 'md' => 'px-4 py-2.5 text-sm', 'lg' => 'px-5 py-3 text-base'];
    $sz = $sizes[$size] ?? $sizes['md'];
@endphp
<button {{ $attributes->merge(['type' => 'button', 'class' => "inline-flex items-center gap-2 {$sz} bg-red-600 border border-transparent rounded-lg font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 active:bg-red-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105 active:scale-95"]) }}>
    {{ $slot }}
</button>

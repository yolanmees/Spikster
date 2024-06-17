@props([
    'type' => 'button',
    'variant' => 'primary',
    'outline' => false,
    'size' => 'md'
])

@php
    $buttonClasses = [
        'base' => 'font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-opacity-75 transition',
        'sizes' => [
            'sm' => 'text-sm py-1 px-3',
            'md' => 'text-base py-2 px-4',
            'lg' => 'text-lg py-3 px-5',
        ],
        'variants' => [
            'primary' => 'bg-sky-800 hover:bg-sky-700 text-white dark:bg-sky-600 dark:hover:bg-sky-500 dark:focus:ring-sky-600 focus:ring-sky-800',
            'secondary' => 'bg-slate-800 hover:bg-slate-700 text-white dark:bg-slate-600 dark:hover:bg-slate-500 dark:focus:ring-slate-600 focus:ring-gray-400',
            'danger' => 'bg-red-800 hover:bg-red-700 text-white dark:bg-red-600 dark:hover:bg-red-500 dark:focus:ring-red-600 focus:ring-red-400',
            'success' => 'bg-green-800 hover:bg-green-700 text-white dark:bg-green-600 dark:hover:bg-green-500 dark:focus:ring-green-600 focus:ring-green-400',
            'warning' => 'bg-yellow-500 text-white hover:bg-yellow-700 dark:bg-yellow-400 dark:hover:bg-yellow-500 dark:focus:ring-yellow-600 focus:ring-yellow-400',
            'info' => 'bg-teal-500 text-white hover:bg-teal-700 dark:bg-teal-400 dark:hover:bg-teal-500 dark:focus:ring-teal-600 focus:ring-teal-400',
            'light' => 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-300 dark:text-gray-800 dark:hover:bg-gray-400 dark:hover:text-gray-900 dark:focus:ring-gray-500 focus:ring-gray-400',
            'dark' => 'bg-gray-700 text-white hover:bg-gray-800 dark:bg-gray-600 dark:hover:bg-gray-500 dark:focus:ring-gray-600 focus:ring-gray-400',
            'link' => 'text-blue-500 underline hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-500 dark:focus:ring-blue-300 focus:ring-blue-400',
        ],
        'outline_variants' => [
            'primary' => 'border border-sky-800 text-sky-800 hover:text-white hover:bg-sky-800 dark:border-sky-600 dark:text-sky-600 dark:hover:bg-sky-600 dark:focus:ring-sky-600 focus:ring-sky-800',
            'secondary' => 'border border-slate-800 text-slate-800 hover:text-white hover:bg-slate-800 dark:border-slate-600 dark:text-slate-600 dark:hover:bg-slate-600 dark:focus:ring-slate-600 focus:ring-gray-400',
            'danger' => 'border border-red-800 text-red-800 hover:text-white hover:bg-red-800 dark:border-red-600 dark:text-red-600 dark:hover:bg-red-600 dark:focus:ring-red-600 focus:ring-red-400',
            'success' => 'border border-green-800 text-green-800 hover:text-white hover:bg-green-800 dark:border-green-600 dark:text-green-600 dark:hover:bg-green-600 dark:focus:ring-green-600 focus:ring-green-400',
            'warning' => 'border border-yellow-500 text-yellow-500 hover:text-white hover:bg-yellow-500 dark:border-yellow-400 dark:text-yellow-400 dark:hover:bg-yellow-400 dark:focus:ring-yellow-600 focus:ring-yellow-400',
            'info' => 'border border-teal-500 text-teal-500 hover:text-white hover:bg-teal-500 dark:border-teal-400 dark:text-teal-400 dark:hover:bg-teal-400 dark:focus:ring-teal-600 focus:ring-teal-400',
            'light' => 'border border-gray-200 text-gray-700 hover:bg-gray-200 dark:border-gray-200 dark:text-gray-300 dark:hover:bg-gray-300 dark:hover:text-gray-800 dark:focus:ring-gray-400 focus:ring-gray-400',
            'dark' => 'border border-gray-700 text-gray-700 hover:text-white hover:bg-gray-700 dark:border-gray-600 dark:text-gray-600 dark:hover:bg-gray-600 dark:focus:ring-gray-600 focus:ring-gray-400',
            'link' => 'text-blue-500 underline hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-500 dark:focus:ring-blue-300 focus:ring-blue-400', // Link does not have outline variant
        ],
    ];

    $variantClasses = $outline
        ? $buttonClasses['outline_variants'][$variant]
        : $buttonClasses['variants'][$variant];

    $classes = "{$buttonClasses['base']} {$buttonClasses['sizes'][$size]} {$variantClasses}";
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
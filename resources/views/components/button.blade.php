@props([
    'type' => 'button',
    'variant' => 'primary',
    'outline' => false,
    'size' => 'md',
])

@php
    $buttonClasses = [
        'base' =>
            'inline-flex items-center justify-center gap-2 font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-opacity-75 transition-all duration-200 transform hover:scale-105 active:scale-95',
        'sizes' => [
            'sm' => 'text-sm py-1.5 px-3',
            'md' => 'text-sm py-2.5 px-4',
            'lg' => 'text-base py-3 px-5',
        ],
        'variants' => [
            'primary' => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
            'secondary' =>
                'bg-gray-600 hover:bg-gray-700 text-white dark:bg-gray-600 dark:hover:bg-gray-500 focus:ring-gray-500',
            'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
            'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
            'warning' => 'bg-yellow-600 hover:bg-yellow-700 text-white focus:ring-yellow-500',
            'info' => 'bg-purple-600 hover:bg-purple-700 text-white focus:ring-purple-500',
            'light' =>
                'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 focus:ring-gray-400',
            'dark' =>
                'bg-gray-800 text-white hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 focus:ring-gray-600',
            'link' =>
                'text-blue-600 hover:text-blue-700 underline dark:text-blue-400 dark:hover:text-blue-300 focus:ring-blue-400',
        ],
        'outline_variants' => [
            'primary' =>
                'border-2 border-blue-600 text-blue-600 hover:bg-blue-50 dark:border-blue-500 dark:text-blue-500 dark:hover:bg-blue-900/20 focus:ring-blue-500',
            'secondary' =>
                'border-2 border-gray-600 text-gray-600 hover:bg-gray-50 dark:border-gray-500 dark:text-gray-400 dark:hover:bg-gray-800 focus:ring-gray-500',
            'danger' =>
                'border-2 border-red-600 text-red-600 hover:bg-red-50 dark:border-red-500 dark:text-red-500 dark:hover:bg-red-900/20 focus:ring-red-500',
            'success' =>
                'border-2 border-green-600 text-green-600 hover:bg-green-50 dark:border-green-500 dark:text-green-500 dark:hover:bg-green-900/20 focus:ring-green-500',
            'warning' =>
                'border-2 border-yellow-600 text-yellow-600 hover:bg-yellow-50 dark:border-yellow-500 dark:text-yellow-500 dark:hover:bg-yellow-900/20 focus:ring-yellow-500',
            'info' =>
                'border-2 border-purple-600 text-purple-600 hover:bg-purple-50 dark:border-purple-500 dark:text-purple-500 dark:hover:bg-purple-900/20 focus:ring-purple-500',
            'light' =>
                'border-2 border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 focus:ring-gray-400',
            'dark' =>
                'border-2 border-gray-700 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800 focus:ring-gray-600',
            'link' =>
                'text-blue-600 hover:text-blue-700 underline dark:text-blue-400 dark:hover:text-blue-300 focus:ring-blue-400',
        ],
    ];

    $variantClasses = $outline ? $buttonClasses['outline_variants'][$variant] : $buttonClasses['variants'][$variant];

    $classes = "{$buttonClasses['base']} {$buttonClasses['sizes'][$size]} {$variantClasses}";
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>

@props(['color' => 'blue', 'text'])

@php
$colorClasses = [
    'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
    'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . ($colorClasses[$color] ?? $colorClasses['blue'])]) }}>
    {{ $text ?? $slot }}
</span>

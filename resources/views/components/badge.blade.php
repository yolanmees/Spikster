@props(['color' => 'blue', 'text'])

@php
    $colorClasses = [
        'blue'   => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200',
        'green'  => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
        'red'    => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200',
        'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200',
        'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200',
        'gray'   => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
    ];
@endphp

<span
    {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . ($colorClasses[$color] ?? $colorClasses['blue'])]) }}>
    {{ $text ?? $slot }}
</span>

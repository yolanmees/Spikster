@props(['type' => 'info', 'dismissible' => false])

@php
    $styles = [
        'success' => [
            'wrapper' => 'bg-green-50 dark:bg-green-900/20 border-green-400 dark:border-green-600',
            'icon_color' => 'text-green-500',
            'text' => 'text-green-800 dark:text-green-300',
            'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />',
        ],
        'error' => [
            'wrapper' => 'bg-red-50 dark:bg-red-900/20 border-red-400 dark:border-red-600',
            'icon_color' => 'text-red-500',
            'text' => 'text-red-800 dark:text-red-300',
            'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />',
        ],
        'warning' => [
            'wrapper' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-400 dark:border-yellow-600',
            'icon_color' => 'text-yellow-500',
            'text' => 'text-yellow-800 dark:text-yellow-300',
            'icon' => '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />',
        ],
        'info' => [
            'wrapper' => 'bg-purple-50 dark:bg-purple-900/20 border-purple-400 dark:border-purple-600',
            'icon_color' => 'text-purple-600 dark:text-purple-400',
            'text' => 'text-purple-900 dark:text-purple-200',
            'icon' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />',
        ],
    ];
    $s = $styles[$type] ?? $styles['info'];
@endphp

<div {{ $attributes->merge(['class' => "flex items-start gap-3 rounded-lg border-l-4 p-4 {$s['wrapper']}"]) }}
    @if($dismissible) x-data="{ show: true }" x-show="show" @endif>
    <svg class="h-5 w-5 shrink-0 mt-0.5 {{ $s['icon_color'] }}" viewBox="0 0 20 20" fill="currentColor">
        {!! $s['icon'] !!}
    </svg>
    <div class="flex-1 text-sm {{ $s['text'] }}">{{ $slot }}</div>
    @if($dismissible)
        <button @click="show = false" type="button" class="shrink-0 {{ $s['icon_color'] }} hover:opacity-75 transition-opacity">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    @endif
</div>

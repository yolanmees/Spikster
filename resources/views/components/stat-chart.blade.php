@props([
    'chartId',
    'title',
    'timeRangeOptions' => [],
])

<x-card size="md">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>{{ $title }}</span>
            <select wire:model.live="timeRange"
                class="text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-3 py-1.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                @foreach ($timeRangeOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </x-slot>

    <canvas id="{{ $chartId }}" width="100%" height="50" class="mt-1"></canvas>

    {{ $slot }}
</x-card>

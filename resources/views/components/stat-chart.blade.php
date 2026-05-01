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
                class="text-sm rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 px-3 py-1.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-colors">
                @foreach ($timeRangeOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </x-slot>

    <canvas id="{{ $chartId }}" width="100%" height="50" class="mt-1"></canvas>

    {{ $slot }}
</x-card>

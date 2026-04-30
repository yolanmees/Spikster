{{--
    Responsive grid for stat-cards.
    Usage: <x-stat-grid :cols="4">
               <x-stat-card ... />
           </x-stat-grid>
--}}
@props(['cols' => 3])

@php
    $gridCols = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 sm:grid-cols-2',
        3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
    ][$cols] ?? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
@endphp

<div {{ $attributes->merge(['class' => "grid gap-4 $gridCols"]) }}>
    {{ $slot }}
</div>

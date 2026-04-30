{{--
    Inline Livewire loading spinner.
    Usage: <x-wire-spinner target="search" />
--}}
@props(['target' => null, 'size' => 'md'])

@php
    $sizes = ['sm' => 'h-4 w-4', 'md' => 'h-5 w-5', 'lg' => 'h-6 w-6'];
    $sz = $sizes[$size] ?? $sizes['md'];
@endphp

<div @if($target) wire:loading wire:target="{{ $target }}" @else wire:loading @endif>
    <svg class="animate-spin {{ $sz }} text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
        </path>
    </svg>
</div>

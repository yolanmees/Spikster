{{-- Thin alias kept for backward-compat. Prefer <x-text-input> directly. --}}
@props(['type' => 'text', 'placeholder' => '', 'model' => null, 'debounce' => null])
<x-text-input
    type="{{ $type }}"
    placeholder="{{ $placeholder }}"
    :model="$model"
    :debounce="$debounce"
    {{ $attributes }}
/>

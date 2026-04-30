@props(['type' => 'button', 'size' => 'md'])
<x-button type="{{ $type }}" variant="secondary" :size="$size" {{ $attributes }}>{{ $slot }}</x-button>

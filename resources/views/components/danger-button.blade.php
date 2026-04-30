@props(['type' => 'button', 'size' => 'md'])
<x-button type="{{ $type }}" variant="danger" :size="$size" {{ $attributes }}>{{ $slot }}</x-button>

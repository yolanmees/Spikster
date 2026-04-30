@props(['type' => 'button', 'size' => 'md', 'loading' => false, 'icon' => null])
<x-button type="{{ $type }}" variant="primary" :size="$size" :loading="$loading" {{ $attributes }}>
    @if($icon)<span class="shrink-0">{!! $icon !!}</span>@endif
    {{ $slot }}
</x-button>

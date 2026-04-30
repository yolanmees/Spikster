{{-- Alias for <x-page-header> with size="section". --}}
@props(['title', 'subtitle' => null])
<x-page-header :title="$title" :subtitle="$subtitle" size="section" v-bind="$attributes">
    @isset($actions)
        <x-slot name="actions">{{ $actions }}</x-slot>
    @endisset
</x-page-header>

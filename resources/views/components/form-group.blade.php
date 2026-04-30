{{--
    Form group: label + input + error.
    Usage:
        <x-form-group label="Name" for="name">
            <x-text-input id="name" name="name" wire:model="name" />
        </x-form-group>
--}}
@props(['label' => null, 'for' => null, 'required' => false, 'hint' => null])

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if($label)
        <label @if($for) for="{{ $for }}" @endif
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}@if($required)<span class="ml-0.5 text-red-500">*</span>@endif
        </label>
    @endif

    {{ $slot }}

    @if($hint)
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif

    @if($for)
        <x-input-error :for="$for" />
    @endif
</div>

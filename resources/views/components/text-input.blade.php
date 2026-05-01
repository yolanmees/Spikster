@props([
    'label'    => null,
    'placeholder' => '',
    'type'     => 'text',
    'model'    => null,
    'debounce' => null,
])

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    @if ($label)
        <label {{ $attributes->only('for') }}
            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        @isset($icon)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                {{ $icon }}
            </div>
        @endisset

        <input
            type="{{ $type }}"
            @if ($model)
                @if ($debounce)
                    wire:model.live.debounce.{{ $debounce }}="{{ $model }}"
                @else
                    wire:model="{{ $model }}"
                @endif
            @endif
            placeholder="{{ $placeholder }}"
            {{ $attributes->except(['class', 'for', 'label', 'model', 'debounce', 'type', 'placeholder'])->merge([
                'class' => implode(' ', [
                    'block w-full rounded-lg border border-zinc-300 bg-white py-2 text-sm text-zinc-950',
                    'placeholder-zinc-400 shadow-sm',
                    'focus:border-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700/20',
                    'disabled:opacity-60 disabled:cursor-not-allowed',
                    'dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500',
                    'dark:focus:border-purple-500 dark:focus:ring-purple-500/20',
                    isset($icon) ? 'pl-10 pr-3' : 'px-3',
                    isset($suffix) ? 'pr-10' : '',
                ])
            ]) }}
        >

        @isset($suffix)
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                {{ $suffix }}
            </div>
        @endisset
    </div>
</div>

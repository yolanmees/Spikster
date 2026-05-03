@props(['label' => null, 'model' => null, 'options' => []])

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label {{ $attributes->only('for') }}
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            {{ $label }}
        </label>
    @endif

    <select
        @if ($model) wire:model.live="{{ $model }}" @endif
        {{ $attributes->except(['class', 'for', 'label', 'model', 'options'])->merge([
            'class' => 'block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700/20 disabled:opacity-60 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:border-purple-400'
        ]) }}>
        {{ $slot }}
        @foreach ($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>

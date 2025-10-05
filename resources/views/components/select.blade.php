@props(['label' => null, 'model' => null, 'options' => []])

<div>
    @if ($label)
        <label {{ $attributes->only('for') }} class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $label }}
        </label>
    @endif

    <select @if ($model) wire:model.live="{{ $model }}" @endif
        {{ $attributes->merge(['class' => 'block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm']) }}>
        {{ $slot }}
    </select>
</div>

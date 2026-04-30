@props(['value' => null])
<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>

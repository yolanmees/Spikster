@props(['value' => null])
<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>

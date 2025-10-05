@props([
    'type' => 'text',
    'name' => '',
    'label' => '',
    'prefix' => '',
    'placeholder' => '',
    'value' => '',
    'error' => null,
    'success' => null,
    'disabled' => false,
])

<div class="space-y-1">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
        </label>
    @endif
    <div class="relative">
        @if ($prefix)
            <div class="flex items-center">
                <span
                    class="rounded-l-lg border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 py-2.5 px-4 text-sm text-gray-700 dark:text-gray-300">
                    {{ $prefix }}
                </span>
                <input
                    {{ $attributes->merge(['class' => 'w-full rounded-r-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 py-2.5 px-4 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all' . ($error ? ' border-red-500 focus:ring-red-500' : '') . ($success ? ' border-green-500 focus:ring-green-500' : '') . ($disabled ? ' opacity-50 cursor-not-allowed' : '')]) }}
                    type="text" name="{{ $name }}" placeholder="{{ $placeholder }}"
                    value="{{ old($name, $value) }}" {{ $disabled ? 'disabled' : '' }}>
            </div>
        @else
            <input
                {{ $attributes->merge(['class' => 'w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 py-2.5 px-4 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all' . ($error ? ' border-red-500 focus:ring-red-500' : '') . ($success ? ' border-green-500 focus:ring-green-500' : '') . ($disabled ? ' opacity-50 cursor-not-allowed' : '')]) }}
                type="{{ $type }}" name="{{ $name }}" placeholder="{{ $placeholder }}"
                value="{{ old($name, $value) }}" {{ $disabled ? 'disabled' : '' }}>
        @endif
    </div>
    @if ($error)
        <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @elseif($success)
        <p class="text-sm text-green-600 dark:text-green-400">{{ $success }}</p>
    @endif
</div>

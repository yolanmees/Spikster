@props(['state' => 'empty', 'icon' => 'folder', 'title' => 'No items found', 'message' => ''])

<div class="py-12 text-center">
    <x-icon :icon="$icon" class="mx-auto h-12 w-12 text-gray-400" />
    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ $title }}</h3>
    @if ($message)
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>
    @endif
    @if (isset($action))
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>

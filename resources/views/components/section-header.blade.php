@props(['title', 'subtitle' => null])
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $title }}</h3>
        @if($subtitle)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex items-center gap-3">{{ $actions }}</div>
    @endisset
</div>

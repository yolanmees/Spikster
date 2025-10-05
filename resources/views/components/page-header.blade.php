@props(['title', 'subtitle' => null])

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
        @if($subtitle)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex items-center space-x-3">
            {{ $actions }}
        </div>
    @endif
</div>

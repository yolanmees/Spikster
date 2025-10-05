<div class="flex justify-between items-start">
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ $description }}
        </p>
    </div>

    @if (isset($aside))
        <div>
            {{ $aside }}
        </div>
    @endif
</div>

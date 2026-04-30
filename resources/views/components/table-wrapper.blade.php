<div class="relative">
    <div wire:loading.delay class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 z-10 flex items-center justify-center rounded-lg">
        <livewire:components.loading-spinner size="lg" color="blue" message="Loading..." />
    </div>
    <div class="mt-4 -mx-4 ring-1 ring-gray-300 dark:ring-gray-700 sm:mx-0 sm:rounded-lg overflow-hidden">
        {{ $slot }}
    </div>
    @isset($pagination)
        <div class="mt-4">{{ $pagination }}</div>
    @endisset
</div>

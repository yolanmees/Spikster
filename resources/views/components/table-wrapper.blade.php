<div class="relative">
    {{-- Livewire loading overlay --}}
    <div wire:loading.delay class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/60 dark:bg-zinc-950/60 backdrop-blur-sm">
        <x-spinner size="lg" color="blue" />
    </div>

    {{-- Table container --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        {{ $slot }}
    </div>

    {{-- Pagination --}}
    @isset($pagination)
        <div class="mt-4">{{ $pagination }}</div>
    @endisset
</div>

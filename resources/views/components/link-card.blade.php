@props([
    'title',
    'description' => '',
    'icon' => null,
    'iconColor' => 'text-purple-700 dark:text-purple-400',
    'href' => '#',
    'label' => 'Open',
    'variant' => 'light',
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 flex flex-col gap-4']) }}>
    <div class="flex items-start gap-4">
        @if($icon)
        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        </div>
        @endif
        <div class="flex-1 min-w-0">
            <h3 class="text-base font-semibold text-zinc-950 dark:text-white">{{ $title }}</h3>
            @if($description)
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
            @endif
        </div>
    </div>
    <div>
        <a href="{{ $href }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium
            {{ $variant === 'light'
                ? 'bg-zinc-100 hover:bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300'
                : 'bg-purple-700 hover:bg-purple-800 text-white' }}
            transition-colors">
            {{ $label }}
        </a>
    </div>
</div>

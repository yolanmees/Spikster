@props([
    'title',
    'subtitle' => null,
    'description',
    'status' => null,
    'statusClass' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
    'href' => null,
    'buttonLabel' => 'Open',
    'buttonId' => null,
    'buttonVariant' => 'primary',
])

@php
    $buttonClasses = [
        'primary' => 'bg-zinc-950 hover:bg-zinc-800 text-white dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200',
        'secondary' => 'bg-zinc-100 hover:bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-200',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white',
    ];

    $buttonClass = $buttonClasses[$buttonVariant] ?? $buttonClasses['primary'];
@endphp

<article {{ $attributes->merge(['class' => 'rounded-lg border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex items-center gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                @isset($icon)
                    {{ $icon }}
                @else
                    <x-icon icon="cog" class="h-4 w-4 text-zinc-600 dark:text-zinc-300" />
                @endisset
            </span>
            <div class="min-w-0">
                <h3 class="truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $title }}</h3>
                @if($subtitle)
                    <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @if($status)
            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">{{ $status }}</span>
        @endif
    </div>

    <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">{{ $description }}</p>

    @isset($meta)
        <div class="mt-4 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-950">
            {{ $meta }}
        </div>
    @endisset

    <div class="mt-5">
        @isset($actions)
            {{ $actions }}
        @else
            @if($href)
                <a href="{{ $href }}"
                    class="inline-flex w-full items-center justify-center rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ $buttonClass }}">
                    {{ $buttonLabel }}
                </a>
            @else
                <button
                    type="button"
                    @if($buttonId) id="{{ $buttonId }}" @endif
                    class="inline-flex w-full items-center justify-center rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ $buttonClass }}"
                >
                    {{ $buttonLabel }}
                </button>
            @endif
        @endisset
    </div>
</article>
@props([
    'title',
    'subtitle'    => null,
    'size'        => 'page',   {{-- 'page' (h1) | 'section' (h3) --}}
    'back'        => null,     {{-- href for back button --}}
    'backLabel'   => 'Back',
])

@php
    $titleClass = $size === 'section'
        ? 'text-lg font-semibold text-zinc-900 dark:text-white'
        : 'text-2xl font-bold text-zinc-900 dark:text-white';
    $tag = $size === 'section' ? 'h3' : 'h1';
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6']) }}>
    <div class="flex items-center gap-3">
        @if($back)
            <a href="{{ $back }}"
               class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="sr-only">{{ $backLabel }}</span>
            </a>
        @endif
        <div>
            <{{ $tag }} class="{{ $titleClass }}">{{ $title }}</{{ $tag }}>
            @if($subtitle)
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @isset($actions)
        <div class="flex items-center gap-2 shrink-0">{{ $actions }}</div>
    @endisset
</div>

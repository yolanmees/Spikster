@props([
    'id'         => null,
    'title'      => null,
    'maxWidth'   => '2xl',
    'closeable'  => true,
    'closeAction' => null,
])

@php
    $maxWidthClass = [
        'sm'  => 'sm:max-w-sm',
        'md'  => 'sm:max-w-md',
        'lg'  => 'sm:max-w-lg',
        'xl'  => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        '5xl' => 'sm:max-w-5xl',
    ][$maxWidth] ?? 'sm:max-w-2xl';
@endphp

<div
    x-data="{ open: {{ $id ? 'false' : 'true' }} }"
    @if($id) id="{{ $id }}-wrapper" @endif
    @if($id) x-on:open-modal.window="$event.detail === '{{ $id }}' && (open = true)" @endif
    x-on:close-modal.window="open = false"
    x-on:keydown.escape.window="$refs.panel && open && (open = false)"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-zinc-950/70 backdrop-blur-md"
        @if($closeable)
            @if($closeAction)
                @click="open = false; $wire.{{ $closeAction }}()"
            @else
                @click="open = false"
            @endif
        @endif
    ></div>

    {{-- Panel --}}
    <div class="flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div
            x-ref="panel"
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full {{ $maxWidthClass }} transform overflow-hidden rounded-3xl border border-zinc-200/80 dark:border-zinc-700/70 bg-white/95 dark:bg-zinc-900/95 shadow-2xl shadow-zinc-900/15 dark:shadow-black/40 ring-1 ring-zinc-950/5 dark:ring-white/10 sm:my-8"
        >
            {{-- Header --}}
            @if($title || isset($header))
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/80 dark:bg-zinc-950/40">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $title ?? $header }}
                    </h3>
                    @if($closeable)
                        <button type="button"
                            @if($closeAction)
                                @click="open = false; $wire.{{ $closeAction }}()"
                            @else
                                @click="open = false"
                            @endif
                            class="inline-flex items-center justify-center w-8 h-8 rounded-xl border border-zinc-200 dark:border-zinc-700 text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 bg-white/80 dark:bg-zinc-900/80 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="sr-only">Close</span>
                        </button>
                    @endif
                </div>
            @endif

            {{-- Body --}}
            <div class="px-6 py-5 text-zinc-800 dark:text-zinc-200">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @isset($footer)
                <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/80 dark:bg-zinc-950/40 flex items-center justify-end gap-3">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>

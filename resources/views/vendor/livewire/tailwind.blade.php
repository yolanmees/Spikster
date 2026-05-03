@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between gap-4">
            {{-- Results summary --}}
            <p class="text-sm text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $paginator->firstItem() }}</span>
                <span>&ndash;</span>
                <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $paginator->lastItem() }}</span>
                <span>of</span>
                <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $paginator->total() }}</span>
            </p>

            {{-- Page buttons --}}
            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-300 dark:border-zinc-800 dark:text-zinc-700 cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </span>
                @else
                    <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" rel="prev" aria-label="Previous" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                @endif

                {{-- Page numbers (desktop) --}}
                <div class="hidden sm:flex sm:items-center sm:gap-1">
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="inline-flex h-8 w-8 items-center justify-center text-sm text-zinc-400 dark:text-zinc-500 select-none">&hellip;</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg border border-purple-700 bg-purple-700 px-2 text-sm font-medium text-white shadow-sm select-none">{{ $page }}</span>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" aria-label="Go to page {{ $page }}" class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg border border-zinc-200 px-2 text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 transition-colors">{{ $page }}</button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" rel="next" aria-label="Next" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @else
                    <span aria-disabled="true" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-300 dark:border-zinc-800 dark:text-zinc-700 cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                @endif
            </div>
        </nav>
    @endif
</div>

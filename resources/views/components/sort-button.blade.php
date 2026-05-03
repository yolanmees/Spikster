@props(['field', 'label', 'sortField', 'sortDirection'])

<button wire:click="sortBy('{{ $field }}')"
    class="group inline-flex items-center gap-1 hover:text-purple-700 dark:hover:text-purple-400 transition-colors">
    {{ $label }}
    @if($sortField === $field)
        @if($sortDirection === 'asc')
            <svg class="h-4 w-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/>
            </svg>
        @else
            <svg class="h-4 w-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
            </svg>
        @endif
    @else
        <svg class="h-4 w-4 text-gray-300 dark:text-gray-600 group-hover:text-gray-400 dark:group-hover:text-gray-500" fill="currentColor" viewBox="0 0 20 20">
            <path d="M5 10l5-5 5 5H5z M5 12l5 5 5-5H5z" opacity=".5"/>
            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l4 4a1 1 0 01-1.414 1.414L10 5.414 6.707 8.707a1 1 0 01-1.414-1.414l4-4A1 1 0 0110 3zM6.293 12.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
    @endif
</button>

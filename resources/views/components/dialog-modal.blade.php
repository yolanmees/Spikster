@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-5">
        <div class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
            {{ $title }}
        </div>

        <div class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
            {{ $content }}
        </div>
    </div>

    <div
        class="flex flex-row justify-end gap-3 px-6 py-4 bg-zinc-50/80 dark:bg-zinc-950/40 border-t border-zinc-200 dark:border-zinc-800">
        {{ $footer }}
    </div>
</x-modal>

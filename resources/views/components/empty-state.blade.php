@props(['icon' => 'folder', 'title' => 'No items found', 'message' => null])

<div class="py-14 text-center">
    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-zinc-100 dark:bg-zinc-800 mb-4">
        <x-icon :icon="$icon" class="h-7 w-7 text-zinc-400 dark:text-zinc-500" />
    </div>
    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white mb-1">{{ $title }}</h3>
    @if($message)
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $message }}</p>
    @elseif($slot->isNotEmpty())
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $slot }}</p>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>

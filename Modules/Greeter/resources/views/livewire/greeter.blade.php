<div class="p-4 space-y-4">
    <div class="text-center">
        <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $greeting }}</p>
    </div>

    <div class="flex items-center gap-2">
        <input
            wire:model="name"
            type="text"
            placeholder="Enter your name..."
            class="flex-1 px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white"
        >
        <button
            wire:click="greet"
            class="px-4 py-2 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 text-white font-semibold rounded-lg transition-all"
        >
            Greet
        </button>
    </div>

    <div class="flex justify-center">
        <button
            wire:click="toggleHistory"
            class="text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors"
        >
            {{ $showHistory ? 'Hide' : 'Show' }} recent greetings
        </button>
    </div>

    @if ($showHistory && $greetings->isNotEmpty())
        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4 space-y-2">
            @foreach ($greetings as $greeting)
                <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $greeting->name }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $greeting->message }}</p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $greeting->created_at->diffForHumans() }}</p>
                </div>
            @endforeach
        </div>
    @endif
</div>

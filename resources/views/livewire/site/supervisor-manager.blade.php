<div class="space-y-4">
    @unless ($hasCommand)
        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-lg">
            <p class="text-sm text-amber-700 dark:text-amber-300">
                No supervisor command configured. Set one in <strong>Configuration</strong> first.
            </p>
        </div>
    @endunless

    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Supervisor Processes</h3>
        <button wire:click="refresh" wire:loading.attr="disabled"
            class="px-3 py-1.5 text-sm bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 rounded-lg transition-colors">
            ↻ Refresh
        </button>
    </div>

    @if ($loading)
        <div class="p-8 text-center text-zinc-500 dark:text-zinc-400">
            <p>Loading processes...</p>
        </div>
    @elseif (empty($processes))
        <div class="p-8 text-center text-zinc-500 dark:text-zinc-400">
            <p>No supervisor processes found or daemon not reachable.</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($processes as $proc)
                <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-2 h-2 rounded-full shrink-0
                            {{ $proc['status'] === 'running' ? 'bg-green-500' : '' }}
                            {{ $proc['status'] === 'stopped' ? 'bg-zinc-400' : '' }}
                            {{ $proc['status'] === 'failed' || $proc['status'] === 'backoff' ? 'bg-red-500' : '' }}">
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white truncate">{{ $proc['name'] }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $proc['detail'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0 ml-3">
                        <button wire:click="start('{{ $proc['name'] }}')" class="px-2 py-1 text-xs bg-green-100 hover:bg-green-200 dark:bg-green-900/30 dark:hover:bg-green-800/50 text-green-700 dark:text-green-300 rounded transition-colors">Start</button>
                        <button wire:click="stop('{{ $proc['name'] }}')" class="px-2 py-1 text-xs bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-800/50 text-red-700 dark:text-red-300 rounded transition-colors">Stop</button>
                        <button wire:click="restart('{{ $proc['name'] }}')" class="px-2 py-1 text-xs bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded transition-colors">Restart</button>
                        <button wire:click="tail('{{ $proc['name'] }}')" class="px-2 py-1 text-xs bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded transition-colors">Log</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($logOutput)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="closeLog">
            <div class="bg-white dark:bg-zinc-900 rounded-xl max-w-2xl w-full max-h-[80vh] flex flex-col">
                <div class="flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h4 class="font-medium text-zinc-900 dark:text-white">Process Log</h4>
                    <button wire:click="closeLog" class="text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300">✕</button>
                </div>
                <pre class="p-4 overflow-auto text-xs font-mono text-green-600 dark:text-green-400 bg-zinc-950 rounded-b-xl whitespace-pre-wrap">{{ $logOutput }}</pre>
            </div>
        </div>
    @endif
</div>

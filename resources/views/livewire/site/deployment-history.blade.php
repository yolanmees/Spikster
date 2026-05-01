<div class="space-y-4">
    @unless ($hasRepo)
        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-lg">
            <p class="text-sm text-amber-700 dark:text-amber-300">
                No Git repository configured. Go to <strong>Integrations</strong> to set up a repository first.
            </p>
        </div>
    @endunless

    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Deployment History</h3>
        <button
            wire:click="triggerDeploy"
            wire:loading.attr="disabled"
            class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white font-semibold rounded-lg transition-all disabled:opacity-50"
        >
            <span wire:loading.remove wire:target="triggerDeploy">Deploy Now</span>
            <span wire:loading wire:target="triggerDeploy">Deploying...</span>
        </button>
    </div>

    @if ($deployments->isEmpty())
        <div class="p-8 text-center">
            <p class="text-zinc-500 dark:text-zinc-400">No deployments yet.</p>
            <p class="text-sm text-zinc-400 dark:text-zinc-500 mt-1">Click "Deploy Now" to start your first deployment.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="text-left py-3 px-2 text-zinc-500 dark:text-zinc-400 font-medium">Status</th>
                        <th class="text-left py-3 px-2 text-zinc-500 dark:text-zinc-400 font-medium">Branch</th>
                        <th class="text-left py-3 px-2 text-zinc-500 dark:text-zinc-400 font-medium">Duration</th>
                        <th class="text-left py-3 px-2 text-zinc-500 dark:text-zinc-400 font-medium">Triggered</th>
                        <th class="text-left py-3 px-2 text-zinc-500 dark:text-zinc-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deployments as $deployment)
                        <tr class="border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                            <td class="py-3 px-2">
                                <span class="inline-flex items-center gap-1.5">
                                    @if ($deployment->isRunning())
                                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                        <span class="text-blue-600 dark:text-blue-400 font-medium">Running</span>
                                    @elseif ($deployment->isSuccessful())
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                        <span class="text-green-600 dark:text-green-400 font-medium">Success</span>
                                    @elseif ($deployment->hasFailed())
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        <span class="text-red-600 dark:text-red-400 font-medium">Failed</span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                        <span class="text-yellow-600 dark:text-yellow-400 font-medium">Pending</span>
                                    @endif
                                </span>
                                @if ($deployment->error_message)
                                    <p class="text-xs text-red-500 mt-1 truncate max-w-xs">{{ $deployment->error_message }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-2 font-mono text-xs text-zinc-600 dark:text-zinc-400">
                                {{ $deployment->branch ?? '-' }}
                            </td>
                            <td class="py-3 px-2 text-zinc-600 dark:text-zinc-400">
                                {{ $deployment->getFormattedDuration() }}
                            </td>
                            <td class="py-3 px-2 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $deployment->created_at->diffForHumans() }}
                            </td>
                            <td class="py-3 px-2">
                                <div class="flex items-center gap-2">
                                    @if ($deployment->isSuccessful() && $deployment->commit_hash)
                                        <button
                                            wire:click="rollback({{ $deployment->id }})"
                                            class="text-xs text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 font-medium transition-colors"
                                        >
                                            Rollback
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

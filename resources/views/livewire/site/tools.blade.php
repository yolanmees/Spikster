<div class="space-y-6" x-data="{ activeTab: 'detect' }">
    {{-- Detection Banner --}}
    @if(!$detected)
        <div class="rounded-lg bg-amber-500/10 border border-amber-500/20 p-4 text-amber-400 text-sm">
            ⚠️ This site does not appear to be a Laravel project.
            @if(isset($detection['type']) && $detection['type'] === 'wordpress')
                WordPress detected — use WordPress tools instead.
            @else
                No artisan file found at {{ $detection['artisan_path'] ?? 'basepath' }}.
            @endif
        </div>
    @else
        {{-- Info bar --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Framework</p>
                <p class="text-sm font-semibold text-zinc-950 dark:text-white">Laravel {{ $detection['framework'] ?? '' }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Version</p>
                <p class="text-sm font-semibold text-zinc-950 dark:text-white">{{ $detection['version'] ?? 'unknown' }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Queue</p>
                <p class="text-sm font-semibold {{ $detection['has_queue'] ? 'text-green-500' : 'text-zinc-400' }}">
                    {{ $detection['has_queue'] ? 'Configured' : 'None' }}
                    @if($detection['has_horizon']) + Horizon @endif
                </p>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Schedule</p>
                <p class="text-sm font-semibold {{ $detection['has_schedule'] ? 'text-green-500' : 'text-zinc-400' }}">
                    {{ $detection['has_schedule'] ? 'Active' : 'None' }}
                </p>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="flex gap-1 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-xl w-fit">
            <button @click="activeTab='artisan'" :class="activeTab==='artisan' ? 'bg-white dark:bg-zinc-700 shadow-sm' : ''" class="px-4 py-2 rounded-lg text-sm font-medium transition-all">Artisan</button>
            <button @click="activeTab='env'" :class="activeTab==='env' ? 'bg-white dark:bg-zinc-700 shadow-sm' : ''" class="px-4 py-2 rounded-lg text-sm font-medium transition-all">.env</button>
            <button @click="activeTab='queue'" :class="activeTab==='queue' ? 'bg-white dark:bg-zinc-700 shadow-sm' : ''" class="px-4 py-2 rounded-lg text-sm font-medium transition-all">Queue</button>
            <button @click="activeTab='log'" :class="activeTab==='log' ? 'bg-white dark:bg-zinc-700 shadow-sm' : ''" class="px-4 py-2 rounded-lg text-sm font-medium transition-all">Logs</button>
            <button @click="activeTab='schedule'" :class="activeTab==='schedule' ? 'bg-white dark:bg-zinc-700 shadow-sm' : ''" class="px-4 py-2 rounded-lg text-sm font-medium transition-all">Schedule</button>
        </div>

        {{-- Artisan Tab --}}
        <div x-show="activeTab==='artisan'" class="space-y-4">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5">
                <h3 class="font-semibold mb-3">Quick Commands</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($quickCommands as $cmd)
                        <button wire:click="quickArtisan('{{ $cmd }}')" class="px-3 py-1.5 text-xs bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-lg transition font-mono">
                            php artisan {{ $cmd }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5">
                <h3 class="font-semibold mb-3">Custom Command</h3>
                <div class="flex gap-2">
                    <input wire:model="artisanCommand" placeholder="e.g., make:model User" class="flex-1 px-4 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm font-mono">
                    <button wire:click="runArtisan" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition">Run</button>
                </div>
            </div>
            @if($artisanOutput)
                <div class="bg-zinc-950 text-green-400 rounded-xl p-4 font-mono text-xs leading-relaxed overflow-auto max-h-96">
                    <pre>{{ $artisanOutput }}</pre>
                </div>
            @endif
        </div>

        {{-- .env Tab --}}
        <div x-show="activeTab==='env'" class="space-y-4">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold">Environment (.env)</h3>
                    <button wire:click="loadEnv" class="text-sm text-purple-500 hover:text-purple-400">Load</button>
                </div>
                @if($editingEnv)
                    <textarea wire:model="envContent" rows="20" class="w-full px-4 py-3 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm font-mono"></textarea>
                    <button wire:click="saveEnv" class="mt-3 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition">Save</button>
                @else
                    <p class="text-sm text-zinc-400">Click "Load" to view the .env file. Be careful — changes may break the site!</p>
                @endif
            </div>
        </div>

        {{-- Queue Tab --}}
        <div x-show="activeTab==='queue'" class="space-y-4">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold">Queue Status</h3>
                    <button wire:click="loadQueueStatus" class="text-sm text-purple-500 hover:text-purple-400">Refresh</button>
                </div>
                @if($queueStatus)
                    <pre class="bg-zinc-950 text-green-400 rounded-lg p-4 font-mono text-xs overflow-auto">{{ $queueStatus }}</pre>
                @else
                    <p class="text-sm text-zinc-400">Click "Refresh" to check queue status.</p>
                @endif
                <button wire:click="quickArtisan('queue:restart')" class="mt-3 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium transition">Restart Queue</button>
            </div>
        </div>

        {{-- Log Tab --}}
        <div x-show="activeTab==='log'" class="space-y-4">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold">Laravel Log (last {{ $logLines }} lines)</h3>
                    <button wire:click="loadLog" class="text-sm text-purple-500 hover:text-purple-400">Refresh</button>
                </div>
                @if($logContent)
                    <pre class="bg-zinc-950 text-green-400 rounded-lg p-4 font-mono text-xs overflow-auto max-h-96">{{ $logContent }}</pre>
                @else
                    <p class="text-sm text-zinc-400">Click "Refresh" to load the latest log entries.</p>
                @endif
            </div>
        </div>

        {{-- Schedule Tab --}}
        <div x-show="activeTab==='schedule'" class="space-y-4">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold">Scheduled Tasks</h3>
                    <button wire:click="loadSchedule" class="text-sm text-purple-500 hover:text-purple-400">Refresh</button>
                </div>
                @if($scheduleList)
                    <pre class="bg-zinc-950 text-green-400 rounded-lg p-4 font-mono text-xs overflow-auto">{{ $scheduleList }}</pre>
                @else
                    <p class="text-sm text-zinc-400">No scheduled tasks or click Refresh.</p>
                @endif
            </div>
        </div>
    @endif
</div>

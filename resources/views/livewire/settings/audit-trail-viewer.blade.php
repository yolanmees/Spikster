<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Audit Trail</h3>
        <button wire:click="resetFilters" class="text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors">
            Reset Filters
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search description or IP..."
                class="w-full px-3 py-2 text-sm rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
        </div>
        <div>
            <select wire:model.live="eventType"
                class="w-full px-3 py-2 text-sm rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                <option value="">All Events</option>
                @foreach ($eventTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select wire:model.live="severity"
                class="w-full px-3 py-2 text-sm rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                <option value="">All Severities</option>
                @foreach ($severities as $sev)
                    <option value="{{ $sev }}">{{ ucfirst($sev) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select wire:model.live="userId"
                class="w-full px-3 py-2 text-sm rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                <option value="">All Users</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <input wire:model.live="dateFrom" type="date"
                class="w-full px-3 py-2 text-sm rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
        </div>
        <div>
            <input wire:model.live="dateTo" type="date"
                class="w-full px-3 py-2 text-sm rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
        </div>
    </div>

    @if ($logs->isEmpty())
        <div class="p-8 text-center text-zinc-500 dark:text-zinc-400">
            <p>No audit log entries found.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="text-left py-3 px-2 text-zinc-500 dark:text-zinc-400 font-medium">Time</th>
                        <th class="text-left py-3 px-2 text-zinc-500 dark:text-zinc-400 font-medium">Event</th>
                        <th class="text-left py-3 px-2 text-zinc-500 dark:text-zinc-400 font-medium">Severity</th>
                        <th class="text-left py-3 px-2 text-zinc-500 dark:text-zinc-400 font-medium">Actor</th>
                        <th class="text-left py-3 px-2 text-zinc-500 dark:text-zinc-400 font-medium">Description</th>
                        <th class="text-left py-3 px-2 text-zinc-500 dark:text-zinc-400 font-medium">IP</th>
                        <th class="text-left py-3 px-2 text-zinc-500 dark:text-zinc-400 font-medium">Target</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr class="border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                            <td class="py-3 px-2 text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                {{ $log->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="py-3 px-2">
                                <span class="px-2 py-0.5 text-xs rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-mono">
                                    {{ $log->event_type }}
                                </span>
                            </td>
                            <td class="py-3 px-2">
                                <span class="px-2 py-0.5 text-xs rounded
                                    {{ $log->severity === 'critical' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : '' }}
                                    {{ $log->severity === 'warning' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' : '' }}
                                    {{ $log->severity === 'info' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : '' }}">
                                    {{ ucfirst($log->severity) }}
                                </span>
                            </td>
                            <td class="py-3 px-2 text-xs text-zinc-600 dark:text-zinc-400">
                                {{ $log->user?->name ?? 'System' }}
                            </td>
                            <td class="py-3 px-2 text-xs text-zinc-700 dark:text-zinc-300 max-w-xs truncate">
                                {{ $log->description }}
                                @if ($log->request_id)
                                    <span class="text-zinc-400 dark:text-zinc-500">[{{ substr($log->request_id, 0, 8) }}]</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-xs text-zinc-500 dark:text-zinc-400 font-mono">
                                {{ $log->ip_address ?? '-' }}
                            </td>
                            <td class="py-3 px-2 text-xs text-zinc-500 dark:text-zinc-400">
                                @if ($log->auditable_type)
                                    {{ class_basename($log->auditable_type) }}#{{ $log->auditable_id }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    @endif
</div>

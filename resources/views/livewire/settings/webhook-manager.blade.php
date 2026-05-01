<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Webhooks</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Send HTTP requests to external services when events occur.</p>
        </div>
        @unless ($showForm)
            <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 text-white font-semibold rounded-lg transition-all">
                Add Webhook
            </button>
        @endunless
    </div>

    @if ($showForm)
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 space-y-4">
            <h4 class="font-medium text-zinc-900 dark:text-white">{{ $editingWebhookId ? 'Edit' : 'New' }} Webhook</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Name</label>
                    <input wire:model="name" type="text" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">URL</label>
                    <input wire:model="url" type="url" placeholder="https://example.com/webhook" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
                    @error('url') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Secret (for HMAC signing, optional)</label>
                <input wire:model="secret" type="text" placeholder="Leave blank to keep existing on edit" class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Events to listen for</label>
                @error('selectedEvents') <p class="text-xs text-red-500 mb-2">{{ $message }}</p> @enderror
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach ($availableEvents as $group => $events)
                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">{{ $group }}</p>
                            @foreach ($events as $event)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="selectedEvents" value="{{ $event }}"
                                        class="rounded border-zinc-300 dark:border-zinc-600">
                                    <span class="text-zinc-700 dark:text-zinc-300">{{ $event }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button wire:click="save" class="px-4 py-2 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 text-white font-semibold rounded-lg transition-all">
                    {{ $editingWebhookId ? 'Update' : 'Create' }}
                </button>
                <button wire:click="cancel" class="px-4 py-2 text-sm text-zinc-600 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    @if ($webhooks->isEmpty() && ! $showForm)
        <div class="p-8 text-center text-zinc-500 dark:text-zinc-400">
            <p>No webhooks configured.</p>
            <p class="text-sm mt-1">Add a webhook to receive HTTP callbacks when events occur.</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($webhooks as $webhook)
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $webhook->is_active ? 'bg-green-500' : 'bg-zinc-400' }}"></span>
                                <h4 class="font-medium text-zinc-900 dark:text-white truncate">{{ $webhook->name }}</h4>
                            </div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 truncate">{{ $webhook->url }}</p>
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                @foreach ($webhook->events ?? [] as $event)
                                    <span class="px-2 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-xs rounded">{{ $event }}</span>
                                @endforeach
                            </div>
                            @if ($webhook->last_sent_at)
                                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">
                                    Last: {{ $webhook->last_sent_at->diffForHumans() }}
                                    @if ($webhook->last_response_code)
                                        · Response: {{ $webhook->last_response_code }}
                                    @endif
                                    @if ($webhook->failure_count > 0)
                                        · <span class="text-red-500">{{ $webhook->failure_count }} failure(s)</span>
                                    @endif
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 ml-4 shrink-0">
                            <button wire:click="toggle({{ $webhook->id }})" class="text-xs {{ $webhook->is_active ? 'text-amber-600' : 'text-green-600' }} hover:underline">
                                {{ $webhook->is_active ? 'Disable' : 'Enable' }}
                            </button>
                            <button wire:click="test({{ $webhook->id }})" class="text-xs text-blue-600 hover:underline">Test</button>
                            <button wire:click="edit({{ $webhook->id }})" class="text-xs text-zinc-600 hover:underline">Edit</button>
                            <button wire:click="delete({{ $webhook->id }})" wire:confirm="Delete this webhook?" class="text-xs text-red-600 hover:underline">Delete</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

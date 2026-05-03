<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold">Cloudflare DNS</h2>
        @if($configured)
            <span class="px-3 py-1 text-sm bg-green-500/10 text-green-400 rounded-full">Connected</span>
        @else
            <span class="px-3 py-1 text-sm bg-yellow-500/10 text-yellow-400 rounded-full">Not configured</span>
        @endif
    </div>

    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-400 mb-1">API Token</label>
            <input type="password" wire:model="apiToken"
                class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:border-blue-500 focus:ring-blue-500">
            <p class="text-xs text-gray-500 mt-1">Create at https://dash.cloudflare.com/profile/api-tokens (needs DNS:Edit)</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-400 mb-1">Account ID</label>
            <input type="text" wire:model="accountId"
                class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:border-blue-500 focus:ring-blue-500">
        </div>
        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg font-medium transition">
            Save & Test Connection
        </button>
    </form>

    @if($zones)
        <div class="mt-6">
            <h3 class="text-lg font-medium mb-3">Cloudflare Zones ({{ count($zones) }})</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($zones as $zone)
                    <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg border border-gray-700">
                        <div>
                            <span class="text-sm font-medium">{{ $zone['name'] }}</span>
                            <span class="block text-xs text-gray-500">{{ $zone['status'] }}</span>
                        </div>
                        <span class="text-xs text-gray-400">{{ $zone['name_servers'][0] ?? '' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-6 p-4 bg-gray-800/30 rounded-lg border border-gray-700">
        <h4 class="text-sm font-medium text-gray-400 mb-2">Quick actions</h4>
        <div class="space-y-2 text-sm">
            <p><span class="text-gray-500">php artisan cloudflare:sync</span> — Sync all panel sites to Cloudflare DNS</p>
            <p><span class="text-gray-500">php artisan cloudflare:add-domain example.com</span> — Add domain to Cloudflare</p>
        </div>
    </div>
</div>

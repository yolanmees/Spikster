<div class="grid grid-cols-6 sm:grid-cols-2 space-x-4">
    <div class="bg-white rounded-md p-4">
        <h2 class="text-lg font-medium text-gray-900">Top Sites</h2>
        <hr class="my-2">
        @foreach($sites as $site)
        <div class="flex items-center justify-between gap-y-2">
            <div class="text-sm text-gray-500 h-10">
                {{ $site->domain }}
            </div>
            <div>
                <a href="{{ route('site.edit', $site->id) }}" class="btn text-sm btn-primary">Manage</a>
            </div>
        </div>
        @endforeach

    </div>
</div>

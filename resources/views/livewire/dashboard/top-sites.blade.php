<div class="grid grid-cols-6 sm:grid-cols-2 space-x-4">
    <div class="rounded-md p-4 bg-gray-100 dark:bg-gray-800 dark:text-white">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            Top Sites
        </h2>
        <hr class="my-2 border-gray-200 dark:border-gray-700" />
        @foreach($sites as $site)
        <div class="flex items-center justify-between gap-y-2">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ $site->domain }}
            </div>
            <div>
                <a href="{{ route('site.edit', $site->id) }}">
                    <x-button>
                        Manage
                    </x-button>
                </a>
            </div>
        </div>
        @endforeach

    </div>
</div>

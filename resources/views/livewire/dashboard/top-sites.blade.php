<section class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-center justify-between border-b border-zinc-200 p-5 dark:border-zinc-800">
        <div>
            <h2 class="text-base font-semibold">Top sites</h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Most recent active sites.</p>
        </div>
        <a href="{{ route('site.list') }}"
            class="text-sm font-medium text-purple-700 hover:text-purple-800 dark:text-purple-300 dark:hover:text-purple-200 transition-colors">
            View all
        </a>
    </div>
    @if($sites->isEmpty())
        <div class="flex flex-col items-center justify-center gap-3 py-12 text-center">
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                <i data-lucide="globe" class="h-6 w-6"></i>
            </span>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">No sites created yet.</p>
            <a href="{{ route('site.list') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-zinc-950 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 transition-colors">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Create site
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                <thead class="bg-zinc-50 text-left text-xs font-medium uppercase text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400">
                    <tr>
                        <th class="px-5 py-3">Domain</th>
                        <th class="px-5 py-3">Server</th>
                        <th class="px-5 py-3">PHP</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach($sites as $site)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="whitespace-nowrap px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-700/15 text-purple-700 dark:text-purple-300 text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($site->domain, 0, 1)) }}
                                </span>
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $site->domain }}</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-zinc-500 dark:text-zinc-400">
                            {{ $site->server->name ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-zinc-500 dark:text-zinc-400">
                            {{ $site->php ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-right">
                            <a href="{{ route('site.edit', $site->id) }}"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-xs font-medium hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:bg-zinc-800 transition-colors">
                                <i data-lucide="settings" class="h-3.5 w-3.5"></i>
                                Manage
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

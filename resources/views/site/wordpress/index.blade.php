@extends('layouts.app')

@section('title')
    WordPress Management
@endsection

@section('topbar-title')
    WordPress
@endsection

@section('content')
    <div class="space-y-6">

        <x-page-header title="WordPress Management" subtitle="Install and manage WordPress for this site">
            <x-slot name="actions">
                <a href="{{ route('site.edit.section', ['site_id' => $site_id, 'section' => 'overview']) }}"
                   class="inline-flex items-center gap-2 text-sm font-medium text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white transition-colors group">
                    <i data-lucide="arrow-left" class="h-4 w-4 transition-transform group-hover:-translate-x-0.5"></i>
                    Back to Site
                </a>
            </x-slot>
        </x-page-header>

        @if (session('success'))
            <div class="flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-700/30 dark:bg-emerald-900/20">
                <i data-lucide="check-circle-2" class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400"></i>
                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="flex gap-3 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-700/30 dark:bg-red-900/20">
                <i data-lucide="alert-circle" class="mt-0.5 h-5 w-5 shrink-0 text-red-500"></i>
                <div>
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-200">There were errors with your submission</h3>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700 dark:text-red-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">

            {{-- Create WordPress --}}
            <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-3 border-b border-zinc-200 p-5 dark:border-zinc-800">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.158 12.786l-2.698 7.84c.806.236 1.657.365 2.54.365 1.047 0 2.051-.18 2.986-.51-.024-.037-.046-.078-.065-.123l-2.763-7.572zm-5.203-2.62l3.246 8.9c-1.837-.87-3.288-2.43-4.093-4.315l.847-4.585zm11.68 1.17c0-.972-.349-1.646-.648-2.168-.399-.648-.772-1.197-.772-1.845 0-.723.548-1.395 1.32-1.395.035 0 .068.004.102.006C17.157 4.368 14.754 3.5 12 3.5c-3.399 0-6.39 1.742-8.128 4.382.228.007.443.011.623.011 1.013 0 2.582-.123 2.582-.123.522-.03.583.736.061.798 0 0-.525.062-1.109.092l3.53 10.5 2.119-6.357-1.508-4.143c-.522-.03-1.016-.092-1.016-.092-.522-.03-.461-.828.061-.798 0 0 1.601.123 2.552.123.987 0 2.582-.123 2.582-.123.522-.03.583.736.061.798 0 0-.525.062-1.109.092l3.506 10.426 1.294-4.32c.56-1.795.987-3.086.987-4.197zm.664-4.827c.034.251.053.52.053.81 0 .797-.149 1.692-.597 2.815L16.03 16.76c1.863-1.084 3.113-3.108 3.113-5.425 0-1.069-.268-2.073-.744-2.952l-.1.125z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Create New WordPress</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Deploy a new WordPress installation</p>
                    </div>
                </div>
                <div class="p-5">
                    <form action="{{ route('site.wordpress.create', $site_id) }}" method="POST" class="space-y-5">
                        @csrf

                        <div>
                            <label for="path" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Install path <span class="text-red-500">*</span></label>
                            <input type="text" id="path" name="path" required
                                class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 font-mono text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                                placeholder="e.g. /blog or /wp">
                            <p class="mt-1 text-xs text-zinc-400">Relative path from the site root</p>
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Admin username <span class="text-red-500">*</span></label>
                            <input type="text" id="username" name="username" required minlength="3" maxlength="255"
                                class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                                placeholder="admin">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Admin password <span class="text-red-500">*</span></label>
                            <input type="password" id="password" name="password" required minlength="8"
                                class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                                placeholder="password">
                            <p class="mt-1 text-xs text-zinc-400">Minimum 8 characters</p>
                        </div>

                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-purple-800 transition-colors">
                            <i data-lucide="download" class="h-4 w-4"></i>
                            Install WordPress
                        </button>
                    </form>
                </div>
            </div>

            {{-- Existing Installations --}}
            <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-3 border-b border-zinc-200 p-5 dark:border-zinc-800">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.158 12.786l-2.698 7.84c.806.236 1.657.365 2.54.365 1.047 0 2.051-.18 2.986-.51-.024-.037-.046-.078-.065-.123l-2.763-7.572zm-5.203-2.62l3.246 8.9c-1.837-.87-3.288-2.43-4.093-4.315l.847-4.585zm11.68 1.17c0-.972-.349-1.646-.648-2.168-.399-.648-.772-1.197-.772-1.845 0-.723.548-1.395 1.32-1.395.035 0 .068.004.102.006C17.157 4.368 14.754 3.5 12 3.5c-3.399 0-6.39 1.742-8.128 4.382.228.007.443.011.623.011 1.013 0 2.582-.123 2.582-.123.522-.03.583.736.061.798 0 0-.525.062-1.109.092l3.53 10.5 2.119-6.357-1.508-4.143c-.522-.03-1.016-.092-1.016-.092-.522-.03-.461-.828.061-.798 0 0 1.601.123 2.552.123.987 0 2.582-.123 2.582-.123.522-.03.583.736.061.798 0 0-.525.062-1.109.092l3.506 10.426 1.294-4.32c.56-1.795.987-3.086.987-4.197zm.664-4.827c.034.251.053.52.053.81 0 .797-.149 1.692-.597 2.815L16.03 16.76c1.863-1.084 3.113-3.108 3.113-5.425 0-1.069-.268-2.073-.744-2.952l-.1.125z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white">WordPress Installations</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $wordpresses->count() }} installation(s)</p>
                    </div>
                </div>
                <div class="p-5">
                    @if ($wordpresses->count() > 0)
                        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                                <thead class="bg-zinc-50 dark:bg-zinc-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Path</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Username</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                    @foreach ($wordpresses as $wordpress)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                            <td class="whitespace-nowrap px-4 py-3.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                                                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.158 12.786l-2.698 7.84c.806.236 1.657.365 2.54.365 1.047 0 2.051-.18 2.986-.51-.024-.037-.046-.078-.065-.123l-2.763-7.572z"/></svg>
                                                    </span>
                                                    <span class="font-mono text-sm font-medium text-zinc-900 dark:text-white">{{ $wordpress->path }}</span>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3.5 text-sm text-zinc-500 dark:text-zinc-400">{{ $wordpress->username }}</td>
                                            <td class="whitespace-nowrap px-4 py-3.5 text-right">
                                                <form action="{{ route('site.wordpress.delete', $wordpress->id) }}" method="POST"
                                                      onsubmit="return confirm('Delete this WordPress installation?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-700/10 transition-colors">
                                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $wordpresses->links() }}
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800">
                                <svg class="h-7 w-7 text-zinc-400 dark:text-zinc-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12.158 12.786l-2.698 7.84c.806.236 1.657.365 2.54.365 1.047 0 2.051-.18 2.986-.51-.024-.037-.046-.078-.065-.123l-2.763-7.572zm-5.203-2.62l3.246 8.9c-1.837-.87-3.288-2.43-4.093-4.315l.847-4.585zm11.68 1.17c0-.972-.349-1.646-.648-2.168-.399-.648-.772-1.197-.772-1.845 0-.723.548-1.395 1.32-1.395.035 0 .068.004.102.006C17.157 4.368 14.754 3.5 12 3.5c-3.399 0-6.39 1.742-8.128 4.382.228.007.443.011.623.011 1.013 0 2.582-.123 2.582-.123.522-.03.583.736.061.798 0 0-.525.062-1.109.092l3.53 10.5 2.119-6.357-1.508-4.143c-.522-.03-1.016-.092-1.016-.092-.522-.03-.461-.828.061-.798 0 0 1.601.123 2.552.123.987 0 2.582-.123 2.582-.123.522-.03.583.736.061.798 0 0-.525.062-1.109.092l3.506 10.426 1.294-4.32c.56-1.795.987-3.086.987-4.197zm.664-4.827c.034.251.053.52.053.81 0 .797-.149 1.692-.597 2.815L16.03 16.76c1.863-1.084 3.113-3.108 3.113-5.425 0-1.069-.268-2.073-.744-2.952l-.1.125z"/></svg>
                            </span>
                            <h3 class="mt-4 text-sm font-semibold text-zinc-900 dark:text-white">No WordPress installations</h3>
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Get started by creating a new WordPress installation.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

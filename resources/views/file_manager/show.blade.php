@extends('layouts.app')

@section('title')
    File Manager
@endsection

@section('topbar-title')
    File Manager
@endsection

@section('content')
    <div class="space-y-6">
        <x-page-header title="File Manager" subtitle="Browse server files" size="section">
        </x-page-header>

        <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Size</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Last modified</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach ($pathContents as $content)
                            @if ($content['type'] === 'file')
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                                <i data-lucide="file" class="h-4 w-4"></i>
                                            </span>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ $content['filename'] }}</span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-sm text-zinc-500 dark:text-zinc-400">{{ number_format($content['size'] / 1000) }} KB</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-sm text-zinc-500 dark:text-zinc-400">{{ $content['last_modified'] }}</td>
                                </tr>
                            @else
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <a href="{{ route('files.show', $content['folder_name']) }}" class="flex items-center gap-3">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                                                <i data-lucide="folder" class="h-4 w-4"></i>
                                            </span>
                                            <span class="font-medium text-zinc-900 hover:text-purple-700 dark:text-white dark:hover:text-purple-400 transition-colors">{{ $content['folder_name'] }}</span>
                                        </a>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-sm text-zinc-400">&mdash;</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-sm text-zinc-400">&mdash;</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

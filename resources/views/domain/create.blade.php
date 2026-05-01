@extends('layouts.app')

@section('title')
    Add Domain
@endsection

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <x-page-header title="Add Domain" subtitle="Add a new domain to your infrastructure" size="section">
            <x-slot name="actions">
                <x-back-button :href="route('domain.list')" label="Back to Domains" />
            </x-slot>
        </x-page-header>

        <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <form method="POST" action="{{ route('domain.store') }}" class="space-y-6 p-6">
                @csrf

                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-700/30 dark:bg-red-900/20">
                        <div class="flex gap-3">
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
                    </div>
                @endif

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="domain" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Domain *</label>
                        <input type="text" name="domain" id="domain" value="{{ old('domain') }}" required
                            class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 font-mono text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                            placeholder="example.com">
                        <p class="mt-1 text-xs text-zinc-400">Enter the full domain name (e.g., example.com)</p>
                    </div>

                    <div>
                        <label for="server_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Server *</label>
                        <select name="server_id" id="server_id" required
                            class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                            <option value="">Select a server</option>
                            @foreach ($servers as $server)
                                <option value="{{ $server->server_id }}" {{ old('server_id') == $server->server_id ? 'selected' : '' }}>
                                    {{ $server->name }} ({{ $server->ip }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="site_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Site (optional)</label>
                        <select name="site_id" id="site_id"
                            class="mt-1.5 block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                            <option value="">No site</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->site_id }}" {{ old('site_id') == $site->site_id ? 'selected' : '' }}>
                                    {{ $site->domain }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-zinc-400">Link this domain to an existing site</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="is_primary" value="1" {{ old('is_primary') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-zinc-300 text-purple-700 focus:ring-purple-700 dark:border-zinc-700 dark:bg-zinc-950">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Primary domain</span>
                        </label>
                        <p class="mt-1 text-xs text-zinc-400">Mark this as the primary domain for the selected site</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-800">
                    <a href="{{ route('domain.list') }}"
                       class="inline-flex items-center justify-center rounded-lg border border-zinc-200 px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-800 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-purple-800 transition-colors">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        Create Domain
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.lucide) lucide.createIcons();
    });
</script>
@endpush

@extends('layouts.app')

@section('title')
    Domains & DNS
@endsection

@section('content')
    <div class="space-y-6">
        <x-page-header title="Domains & DNS">
            <x-slot name="description">
                Manage all domains and their DNS records across your servers
            </x-slot>
            <x-slot name="actions">
                <a href="{{ route('domain.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-950 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200 transition-colors">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Add Domain
                </a>
            </x-slot>
        </x-page-header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Total domains</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $stats['total'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                        <i data-lucide="globe-2" class="h-5 w-5"></i>
                    </span>
                </div>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Primary domains</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $stats['primary'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                        <i data-lucide="star" class="h-5 w-5"></i>
                    </span>
                </div>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Aliases</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $stats['aliases'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                        <i data-lucide="copy" class="h-5 w-5"></i>
                    </span>
                </div>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">DNS records</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $stats['total_dns_records'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                        <i data-lucide="network" class="h-5 w-5"></i>
                    </span>
                </div>
            </div>
        </div>

        @livewire('domain.domain-table')
    </div>
@endsection

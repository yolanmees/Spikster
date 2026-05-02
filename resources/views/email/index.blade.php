@extends('layouts.app')

@section('title', 'Email Management')

@section('content')
    <div class="space-y-6" x-data="{ activeTab: 'accounts' }">

        {{-- Page Header --}}
        <x-page-header title="Email Management" :subtitle="$site->domain . ' — Manage email accounts, forwarders, and settings'">
            <x-slot name="actions">
                <x-back-button :href="route('site.edit', $site->site_id)" label="Back to Site" />
            </x-slot>
        </x-page-header>

        {{-- Tabs --}}
        <x-alpine-tabs model="activeTab" :tabs="[
            ['key' => 'accounts', 'label' => 'Email Accounts',
                'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\'/></svg>'],
            ['key' => 'forwarders', 'label' => 'Forwarders',
                'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13 7l5 5m0 0l-5 5m5-5H6\'/></svg>'],
            ['key' => 'settings', 'label' => 'Settings',
                'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/></svg>'],
        ]" />

        {{-- Email Accounts Tab --}}
        <div x-show="activeTab === 'accounts'" x-cloak>
            @livewire('email.email-accounts-table', ['siteId' => $site->site_id])
        </div>

        {{-- Forwarders Tab --}}
        <div x-show="activeTab === 'forwarders'" x-cloak>
            @livewire('email.email-forwarders-table', ['siteId' => $site->site_id])
        </div>

        {{-- Settings Tab --}}
        <div x-show="activeTab === 'settings'" x-cloak>
            <div class="space-y-6">

                {{-- DKIM --}}
                <x-card>
                    <x-slot name="header">DKIM Configuration</x-slot>
                    @livewire('email.email-dkim-manager', ['siteId' => $site->site_id])
                </x-card>

                {{-- Mail Server Info --}}
                <x-card>
                    <x-slot name="header">Email Server Information</x-slot>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <h4 class="font-semibold text-gray-900 dark:text-white">Incoming Mail Server (IMAP)</h4>
                            <dl class="space-y-1 text-sm divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ([
                                    ['Server',   $site->server?->ip ?? 'N/A'],
                                    ['Port',     '993 (SSL/TLS)'],
                                    ['Security', 'SSL/TLS'],
                                ] as [$label, $value])
                                    <div class="flex justify-between py-1">
                                        <dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white font-mono text-xs">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>

                        <div class="space-y-3">
                            <h4 class="font-semibold text-gray-900 dark:text-white">Outgoing Mail Server (SMTP)</h4>
                            <dl class="space-y-1 text-sm divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ([
                                    ['Server',         $site->server?->ip ?? 'N/A'],
                                    ['Port',           '587 (STARTTLS) / 465 (SSL/TLS)'],
                                    ['Security',       'STARTTLS or SSL/TLS'],
                                    ['Authentication', 'Required'],
                                ] as [$label, $value])
                                    <div class="flex justify-between py-1">
                                        <dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white font-mono text-xs">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <x-alert type="info">
                            Use your full email address as the username (e.g., user@{{ $site->domain }})
                        </x-alert>
                    </div>
                </x-card>

            </div>
        </div>

    </div>
@endsection

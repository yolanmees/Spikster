@extends('layouts.app')

@section('title', 'FTP Management')

@section('content')
    <div class="space-y-6" x-data="{ activeTab: 'users' }">

        {{-- Page Header --}}
        <x-page-header title="FTP Management" :subtitle="$site->domain">
            <x-slot name="actions">
                <x-back-button :href="route('site.edit', $site->site_id)" label="Back to Site" />
            </x-slot>
        </x-page-header>

        {{-- Site Info --}}
        <x-info-grid :cols="3" :items="[
            ['label' => 'Site Domain', 'value' => $site->domain,              'icon' => 'globe',  'color' => 'blue'],
            ['label' => 'Server',      'value' => $site->server->name ?? 'N/A', 'icon' => 'server', 'color' => 'purple'],
            ['label' => 'Server IP',   'value' => $site->server->ip   ?? 'N/A', 'icon' => 'network','color' => 'green'],
        ]" />

        {{-- Tabs --}}
        <x-alpine-tabs model="activeTab" :tabs="[
            ['key' => 'users', 'label' => 'FTP Users',
                'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z\'/></svg>'],
            ['key' => 'info', 'label' => 'Connection Info',
                'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>'],
            ['key' => 'help', 'label' => 'Help & Guide',
                'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>'],
        ]" />

        {{-- FTP Users Tab --}}
        <div x-show="activeTab === 'users'" x-cloak>
            @livewire('ftp.ftp-users-table', ['site' => $site])
        </div>

        {{-- Connection Info Tab --}}
        <div x-show="activeTab === 'info'" x-cloak>
            <x-card>
                <x-slot name="header">FTP Server Details</x-slot>

                <x-alert type="info" class="mb-6">
                    <strong>General FTP Server Information</strong><br>
                    For specific user connection details, click the info icon next to each FTP user in the Users tab.
                </x-alert>

                <dl class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ([
                        ['FTP Server Host',         $site->server->ip ?? $site->domain, true],
                        ['FTP Port (Standard)',      '21',           true],
                        ['FTPS Port (Explicit TLS)', '21',           true],
                        ['FTPS Port (Implicit TLS)', '990',          true],
                        ['Passive Port Range',       '40000–50000',  true],
                        ['Protocol',                 'FTP / FTPS (vsftpd)', false],
                        ['Transfer Mode',            'Passive (recommended)', false],
                    ] as [$label, $value, $mono])
                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2 {{ $mono ? 'font-mono' : '' }}">
                                {{ $value }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </x-card>
        </div>

        {{-- Help Tab --}}
        <div x-show="activeTab === 'help'" x-cloak>
            <x-card>
                <x-slot name="header">FTP Access Guide</x-slot>

                <div class="space-y-6 text-sm text-gray-700 dark:text-gray-300">
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Getting Started</h4>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Create an FTP user in the "FTP Users" tab</li>
                            <li>Click the info icon next to the user to view connection details</li>
                            <li>Copy the configuration for your preferred FTP client</li>
                            <li>Connect using your FTP client</li>
                        </ol>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Recommended FTP Clients</h4>
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>FileZilla</strong> — Free, cross-platform</li>
                            <li><strong>WinSCP</strong> — Free, Windows only</li>
                            <li><strong>Cyberduck</strong> — Free, macOS & Windows</li>
                            <li><strong>Transmit</strong> — Paid, macOS only</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Security Best Practices</h4>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Always enable SSL/TLS when creating FTP users (default)</li>
                            <li>Use strong passwords (8+ characters)</li>
                            <li>Consider IP whitelisting for added security</li>
                            <li>Regularly check "Last Login" for suspicious activity</li>
                            <li>Disable unused FTP accounts immediately</li>
                        </ul>
                    </div>

                    <x-alert type="warning">
                        <strong>Connection Issues?</strong><br>
                        Verify the FTP user is active, check if the account is locked, ensure your firewall allows ports 21 and 40000–50000, and try both passive and active transfer modes.
                    </x-alert>

                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Common Terminal Commands</h4>
                        <div class="bg-gray-900 rounded-lg p-4 text-white font-mono text-sm space-y-2">
                            <p class="text-green-400"># Connect with lftp</p>
                            <p>lftp -u username,password ftp://{{ $site->server->ip ?? $site->domain }}</p>
                            <p class="text-green-400 mt-3"># Upload a file with curl</p>
                            <p>curl -T file.txt ftp://{{ $site->server->ip ?? $site->domain }}/file.txt -u username:password</p>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

    </div>
@endsection

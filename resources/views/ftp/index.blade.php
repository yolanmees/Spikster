<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    FTP Management - {{ $site->domain }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Manage FTP users and access for this site
                </p>
            </div>
            <div>
                <a href="{{ route('site.edit', $site->site_id) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Site
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Site Info Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Site Domain</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $site->domain }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Server</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $site->server->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Server IP</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $site->server->ip ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab Navigation --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6" x-data="{ activeTab: 'users' }">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                        <button @click="activeTab = 'users'"
                            :class="activeTab === 'users' ? 'border-indigo-500 text-indigo-600' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="inline-block w-5 h-5 mr-2 -mt-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            FTP Users
                        </button>
                        <button @click="activeTab = 'info'"
                            :class="activeTab === 'info' ? 'border-indigo-500 text-indigo-600' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="inline-block w-5 h-5 mr-2 -mt-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Connection Info
                        </button>
                        <button @click="activeTab = 'help'"
                            :class="activeTab === 'help' ? 'border-indigo-500 text-indigo-600' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="inline-block w-5 h-5 mr-2 -mt-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Help & Guide
                        </button>
                    </nav>
                </div>

                {{-- Tab Content --}}
                <div class="p-6">
                    {{-- FTP Users Tab --}}
                    <div x-show="activeTab === 'users'" x-cloak>
                        @livewire('ftp.ftp-users-table', ['site' => $site])
                    </div>

                    {{-- Connection Info Tab --}}
                    <div x-show="activeTab === 'info'" x-cloak>
                        <div class="space-y-6">
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            <strong>General FTP Server Information</strong><br>
                                            For specific user connection details, click the info icon next to each FTP
                                            user in the Users tab.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                                <div class="px-4 py-5 sm:px-6">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">FTP Server Details</h3>
                                </div>
                                <div class="border-t border-gray-200">
                                    <dl>
                                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">FTP Server Host</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-mono">
                                                {{ $site->server->ip ?? $site->domain }}</dd>
                                        </div>
                                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">FTP Port (Standard)</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-mono">21
                                            </dd>
                                        </div>
                                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">FTPS Port (Explicit TLS)</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-mono">21
                                            </dd>
                                        </div>
                                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">FTPS Port (Implicit TLS)</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-mono">990
                                            </dd>
                                        </div>
                                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">Passive Port Range</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-mono">
                                                40000-50000</dd>
                                        </div>
                                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">Protocol</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">FTP / FTPS
                                                (vsftpd)</dd>
                                        </div>
                                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">Transfer Mode</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">Passive
                                                (recommended)</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Help & Guide Tab --}}
                    <div x-show="activeTab === 'help'" x-cloak>
                        <div class="prose max-w-none">
                            <h3>FTP Access Guide</h3>

                            <h4>Getting Started</h4>
                            <ol>
                                <li>Create an FTP user in the "FTP Users" tab</li>
                                <li>Click the info icon (ℹ️) next to the user to view connection details</li>
                                <li>Copy the configuration for your preferred FTP client</li>
                                <li>Connect using your FTP client</li>
                            </ol>

                            <h4>Recommended FTP Clients</h4>
                            <ul>
                                <li><strong>FileZilla</strong> - Free, cross-platform (Windows, Mac, Linux)</li>
                                <li><strong>WinSCP</strong> - Free, Windows only</li>
                                <li><strong>Cyberduck</strong> - Free, macOS and Windows</li>
                                <li><strong>Transmit</strong> - Paid, macOS only</li>
                            </ul>

                            <h4>Security Best Practices</h4>
                            <ul>
                                <li><strong>Always enable SSL/TLS</strong> when creating FTP users (enabled by default)
                                </li>
                                <li>Use strong passwords with at least 8 characters</li>
                                <li>Consider IP whitelisting for added security</li>
                                <li>Regularly monitor the "Last Login" column for suspicious activity</li>
                                <li>Disable unused FTP accounts immediately</li>
                            </ul>

                            <h4>Quota Management</h4>
                            <ul>
                                <li>Each user has a disk quota limit (default: 1 GB)</li>
                                <li>Monitor quota usage in the FTP Users table</li>
                                <li>Users cannot upload files when quota is exceeded</li>
                                <li>Update quotas using the disk icon in the actions column</li>
                            </ul>

                            <h4>Troubleshooting</h4>
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 my-4">
                                <p class="text-sm text-yellow-700">
                                    <strong>Connection Issues?</strong><br>
                                    - Verify the FTP user is active (green badge)<br>
                                    - Check if the account is locked (unlock icon will appear)<br>
                                    - Ensure your firewall allows ports 21 and 40000-50000<br>
                                    - Try both passive and active transfer modes<br>
                                    - Check if IP whitelisting is blocking your connection
                                </p>
                            </div>

                            <h4>Common Commands (Terminal)</h4>
                            <div class="bg-gray-900 rounded-lg p-4 text-white font-mono text-sm">
                                <p class="text-green-400"># Connect with lftp</p>
                                <p>lftp -u username,password ftp://{{ $site->server->ip ?? $site->domain }}</p>
                                <br>
                                <p class="text-green-400"># Upload a file with curl</p>
                                <p>curl -T file.txt ftp://{{ $site->server->ip ?? $site->domain }}/file.txt -u
                                    username:password</p>
                            </div>

                            <h4>Need More Help?</h4>
                            <p>For advanced FTP configuration or support, please contact your system administrator.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Add Alpine.js if not already loaded
            if (typeof Alpine === 'undefined') {
                document.write('<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer><\/script>');
            }
        </script>
    @endpush
</x-app-layout>

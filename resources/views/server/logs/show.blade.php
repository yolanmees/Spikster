@extends('layouts.app')

@section('title')
    Log Viewer
@endsection

@section('content')
    <!-- Header -->
    <div
        class="mb-6 bg-white dark:bg-gray-800/30 rounded-xl border border-gray-200 dark:border-gray-700/50 p-4 backdrop-blur-sm">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('logs.index', ['server_id' => $server->server_id]) }}"
                    class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center transition-all">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ str_replace('_', '/', $log['name']) }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Server: {{ $server->name }} ({{ $server->ip }})
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Filter Buttons -->
                <div
                    class="inline-flex rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 p-1">
                    <button onclick="filterLevel('all')" id="filter-all"
                        class="filter-btn px-3 py-1.5 text-xs font-semibold rounded-md transition-all bg-purple-700 text-white">
                        All
                    </button>
                    <button onclick="filterLevel('error')" id="filter-error"
                        class="filter-btn px-3 py-1.5 text-xs font-semibold rounded-md transition-all text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                        <span class="inline-block w-2 h-2 rounded-full bg-red-500 mr-1"></span>
                        Error
                    </button>
                    <button onclick="filterLevel('warning')" id="filter-warning"
                        class="filter-btn px-3 py-1.5 text-xs font-semibold rounded-md transition-all text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                        <span class="inline-block w-2 h-2 rounded-full bg-yellow-500 mr-1"></span>
                        Warning
                    </button>
                    <button onclick="filterLevel('info')" id="filter-info"
                        class="filter-btn px-3 py-1.5 text-xs font-semibold rounded-md transition-all text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                        <span class="inline-block w-2 h-2 rounded-full bg-zinc-500 mr-1"></span>
                        Info
                    </button>
                </div>

                <!-- Auto-refresh Toggle -->
                <button onclick="toggleAutoRefresh()" id="auto-refresh-btn"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span id="auto-refresh-text">Auto-refresh: OFF</span>
                </button>

                <!-- Download Button -->
                <a href="{{ route('logs.download', ['server_id' => $server->server_id, 'log' => $log['name']]) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download
                </a>

                @if (!str_contains($log['name'], '_'))
                    <!-- Delete Button (only for application logs) -->
                    <button onclick="confirmDelete()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="mb-4">
        <div class="relative">
            <input type="text" id="searchLog" placeholder="Search in log content..."
                class="w-full px-4 py-3 pl-12 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all">
            <svg class="w-5 h-5 absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span id="search-results"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400"></span>
        </div>
    </div>

    <!-- Log Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800/30 rounded-lg border border-gray-200 dark:border-gray-700/50 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Lines</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white" id="total-lines">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800/30 rounded-lg border border-gray-200 dark:border-gray-700/50 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Errors</p>
                    <p class="text-lg font-bold text-red-600 dark:text-red-400" id="error-count">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800/30 rounded-lg border border-gray-200 dark:border-gray-700/50 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Warnings</p>
                    <p class="text-lg font-bold text-yellow-600 dark:text-yellow-400" id="warning-count">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800/30 rounded-lg border border-gray-200 dark:border-gray-700/50 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-zinc-100 dark:bg-zinc-800/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Info</p>
                    <p class="text-lg font-bold text-zinc-600 dark:text-zinc-400" id="info-count">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Content -->
    <x-card size="lg" dark="false">
        <x-slot name="header">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    Log Content
                </div>
                <div class="flex items-center gap-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="line-wrap" class="sr-only peer" onchange="toggleLineWrap()">
                        <div
                            class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-purple-700">
                        </div>
                        <span class="ms-2 text-sm font-medium text-gray-700 dark:text-gray-300">Wrap Lines</span>
                    </label>
                </div>
            </div>
        </x-slot>

        <div class="bg-gray-900 rounded-lg overflow-hidden">
            <div id="log-content"
                class="font-mono text-sm text-gray-100 p-4 overflow-x-auto max-h-[600px] overflow-y-auto"
                style="line-height: 1.5;">
                <!-- Log lines will be inserted here -->
            </div>
        </div>

        <div id="no-results" class="hidden text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-500 dark:text-gray-400">No matching log entries found</p>
        </div>
    </x-card>
@endsection

@section('js')
    <script>
        let autoRefreshInterval = null;
        let currentFilter = 'all';
        let logLines = [];

        // Parse and display log content
        function parseAndDisplayLog() {
            const rawLog = {!! json_encode($log['log'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
            const lines = rawLog.split('\n');
            logLines = [];

            let errorCount = 0;
            let warningCount = 0;
            let infoCount = 0;

            lines.forEach((line, index) => {
                if (!line.trim()) return;

                let level = 'default';
                let colorClass = 'text-gray-300';

                // Detect log level
                if (line.match(/\b(ERROR|CRITICAL|FATAL|Exception)\b/i)) {
                    level = 'error';
                    colorClass = 'text-red-400';
                    errorCount++;
                } else if (line.match(/\b(WARNING|WARN)\b/i)) {
                    level = 'warning';
                    colorClass = 'text-yellow-400';
                    warningCount++;
                } else if (line.match(/\b(INFO|DEBUG|NOTICE)\b/i)) {
                    level = 'info';
                    colorClass = 'text-zinc-400';
                    infoCount++;
                }

                logLines.push({
                    number: index + 1,
                    content: line,
                    level: level,
                    colorClass: colorClass,
                    original: line
                });
            });

            // Update stats
            document.getElementById('total-lines').textContent = logLines.length.toLocaleString();
            document.getElementById('error-count').textContent = errorCount.toLocaleString();
            document.getElementById('warning-count').textContent = warningCount.toLocaleString();
            document.getElementById('info-count').textContent = infoCount.toLocaleString();

            displayLines();
        }

        function displayLines(searchTerm = '') {
            const container = document.getElementById('log-content');
            const noResults = document.getElementById('no-results');
            let html = '';
            let visibleCount = 0;

            logLines.forEach(line => {
                // Apply filter
                if (currentFilter !== 'all' && line.level !== currentFilter) {
                    return;
                }

                // Apply search
                if (searchTerm && !line.content.toLowerCase().includes(searchTerm.toLowerCase())) {
                    return;
                }

                visibleCount++;

                // Highlight search term
                let displayContent = escapeHtml(line.content);
                if (searchTerm) {
                    const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                    displayContent = displayContent.replace(regex,
                        '<mark class="bg-yellow-300 dark:bg-yellow-600 text-gray-900">$1</mark>');
                }

                html += `
                <div class="log-line flex hover:bg-gray-800 transition-colors border-l-2 ${getBorderColor(line.level)} pl-2 py-0.5" data-level="${line.level}">
                    <span class="text-gray-500 select-none mr-4 text-right" style="min-width: 60px;">${line.number}</span>
                    <span class="${line.colorClass}">${displayContent}</span>
                </div>
            `;
            });

            if (visibleCount === 0) {
                container.classList.add('hidden');
                noResults.classList.remove('hidden');
            } else {
                container.classList.remove('hidden');
                noResults.classList.add('hidden');
                container.innerHTML = html;
            }

            // Update search results count
            if (searchTerm) {
                document.getElementById('search-results').textContent = `${visibleCount} results`;
            } else {
                document.getElementById('search-results').textContent = '';
            }
        }

        function getBorderColor(level) {
            switch (level) {
                case 'error':
                    return 'border-red-500';
                case 'warning':
                    return 'border-yellow-500';
                case 'info':
                    return 'border-zinc-500';
                default:
                    return 'border-gray-700';
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function escapeRegex(text) {
            return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        // Filter by level
        function filterLevel(level) {
            currentFilter = level;

            // Update button states
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('bg-purple-700', 'text-white');
                btn.classList.add('text-gray-600', 'dark:text-gray-300', 'hover:bg-gray-100',
                    'dark:hover:bg-gray-600');
            });

            const activeBtn = document.getElementById(`filter-${level}`);
            activeBtn.classList.add('bg-purple-700', 'text-white');
            activeBtn.classList.remove('text-gray-600', 'dark:text-gray-300', 'hover:bg-gray-100',
            'dark:hover:bg-gray-600');

            const searchTerm = document.getElementById('searchLog').value;
            displayLines(searchTerm);
        }

        // Search functionality
        document.getElementById('searchLog').addEventListener('input', function(e) {
            displayLines(e.target.value);
        });

        // Toggle line wrap
        function toggleLineWrap() {
            const container = document.getElementById('log-content');
            const checkbox = document.getElementById('line-wrap');

            if (checkbox.checked) {
                container.style.whiteSpace = 'pre-wrap';
            } else {
                container.style.whiteSpace = 'pre';
            }
        }

        // Auto-refresh
        function toggleAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
                document.getElementById('auto-refresh-text').textContent = 'Auto-refresh: OFF';
                document.getElementById('auto-refresh-btn').classList.remove('bg-green-600', 'text-white');
                document.getElementById('auto-refresh-btn').classList.add('bg-gray-100', 'dark:bg-gray-700',
                    'text-gray-700', 'dark:text-gray-300');
            } else {
                autoRefreshInterval = setInterval(() => {
                    window.location.reload();
                }, 10000); // Refresh every 10 seconds
                document.getElementById('auto-refresh-text').textContent = 'Auto-refresh: ON (10s)';
                document.getElementById('auto-refresh-btn').classList.add('bg-green-600', 'text-white');
                document.getElementById('auto-refresh-btn').classList.remove('bg-gray-100', 'dark:bg-gray-700',
                    'text-gray-700', 'dark:text-gray-300');
            }
        }

        // Delete log confirmation
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this log file? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('logs.delete', ['server_id' => $server->server_id, 'log' => $log['name']]) }}';

                const csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '_token';
                csrfField.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfField);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            parseAndDisplayLog();
        });
    </script>
@endsection

@props(['serverId'])

<div class="space-y-6">
    <!-- Time Range Selector - Shared for all graphs -->
    <div class="flex justify-between items-center bg-gradient-to-r from-blue-50 to-indigo-50 p-5 rounded-xl border border-blue-100 shadow-sm">
        <div>
            <h3 class="text-xl font-bold text-gray-800">Server Monitoring</h3>
            <p class="text-sm text-gray-600 mt-1">Real-time resource usage</p>
        </div>
        <select id="timeRangeSelector" 
                class="px-4 py-2 rounded-lg border-2 border-blue-200 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-200 text-sm font-medium text-gray-700 bg-white transition-all">
            <option value="1">Last Hour</option>
            <option value="6">Last 6 Hours</option>
            <option value="12">Last 12 Hours</option>
            <option value="24" selected>Last 24 Hours</option>
            <option value="168">Last 7 Days</option>
            <option value="720">Last 30 Days</option>
        </select>
    </div>

    <!-- Graphs Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- CPU Chart -->
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                </div>
                <h4 class="text-base font-semibold text-gray-800">CPU Usage</h4>
            </div>
            <div style="position: relative; height: 220px;">
                <canvas id="cpuChart"></canvas>
            </div>
        </div>

        <!-- Memory Chart -->
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-emerald-100 rounded-lg">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                </div>
                <h4 class="text-base font-semibold text-gray-800">Memory Usage</h4>
            </div>
            <div style="position: relative; height: 220px;">
                <canvas id="memoryChart"></canvas>
            </div>
        </div>

        <!-- Load Average Chart -->
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-amber-100 rounded-lg">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h4 class="text-base font-semibold text-gray-800">Load Average</h4>
            </div>
            <div style="position: relative; height: 220px;">
                <canvas id="loadChart"></canvas>
            </div>
        </div>

        <!-- Disk Usage Chart -->
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h4 class="text-base font-semibold text-gray-800">Disk Usage</h4>
            </div>
            <div style="position: relative; height: 220px;">
                <canvas id="diskChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Global chart instances
    let charts = {
        cpu: null,
        memory: null,
        load: null,
        disk: null
    };

    // Server ID from the page
    const serverId = '{{ $serverId }}';
    
    // Chart configuration template
    function getChartConfig(label, data, labels, color, gradientColor, maxValue = null) {
        return {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    borderColor: color,
                    backgroundColor: gradientColor,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: color,
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: {
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toFixed(2);
                                    if (maxValue === 100) {
                                        label += '%';
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: maxValue,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.04)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#6B7280',
                            padding: 8,
                            callback: function(value) {
                                return value + (maxValue === 100 ? '%' : '');
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 12,
                            font: {
                                size: 11
                            },
                            color: '#6B7280',
                            padding: 8
                        }
                    }
                }
            }
        };
    }

    // Load and render all charts
    function loadCharts(hours = 24) {
        console.log('Loading metrics for server:', serverId, 'hours:', hours);
        
        fetch(`/api/servers/${serverId}/metrics?hours=${hours}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                
                // Validate data
                if (!data.labels || !data.cpu || !data.memory || !data.load || !data.disk) {
                    console.error('Invalid data structure:', data);
                    return;
                }
                
                if (data.labels.length === 0) {
                    console.warn('No data points available');
                    return;
                }
                
                // Destroy existing charts
                Object.keys(charts).forEach(key => {
                    if (charts[key]) {
                        charts[key].destroy();
                        charts[key] = null;
                    }
                });

                // Create new charts with error handling
                try {
                    const cpuCtx = document.getElementById('cpuChart');
                    if (cpuCtx) {
                        const ctx = cpuCtx.getContext('2d');
                        const gradient = ctx.createLinearGradient(0, 0, 0, 220);
                        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
                        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');
                        
                        charts.cpu = new Chart(ctx, getChartConfig(
                            'CPU Usage',
                            data.cpu || [],
                            data.labels || [],
                            'rgb(59, 130, 246)',
                            gradient,
                            100
                        ));
                    }

                    const memoryCtx = document.getElementById('memoryChart');
                    if (memoryCtx) {
                        const ctx = memoryCtx.getContext('2d');
                        const gradient = ctx.createLinearGradient(0, 0, 0, 220);
                        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
                        gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
                        
                        charts.memory = new Chart(ctx, getChartConfig(
                            'Memory Usage',
                            data.memory || [],
                            data.labels || [],
                            'rgb(16, 185, 129)',
                            gradient,
                            100
                        ));
                    }

                    const loadCtx = document.getElementById('loadChart');
                    if (loadCtx) {
                        const ctx = loadCtx.getContext('2d');
                        const gradient = ctx.createLinearGradient(0, 0, 0, 220);
                        gradient.addColorStop(0, 'rgba(245, 158, 11, 0.3)');
                        gradient.addColorStop(1, 'rgba(245, 158, 11, 0.0)');
                        
                        charts.load = new Chart(ctx, getChartConfig(
                            'Load Average',
                            data.load || [],
                            data.labels || [],
                            'rgb(245, 158, 11)',
                            gradient,
                            null
                        ));
                    }

                    const diskCtx = document.getElementById('diskChart');
                    if (diskCtx) {
                        const ctx = diskCtx.getContext('2d');
                        const gradient = ctx.createLinearGradient(0, 0, 0, 220);
                        gradient.addColorStop(0, 'rgba(168, 85, 247, 0.3)');
                        gradient.addColorStop(1, 'rgba(168, 85, 247, 0.0)');
                        
                        charts.disk = new Chart(ctx, getChartConfig(
                            'Disk Usage',
                            data.disk || [],
                            data.labels || [],
                            'rgb(168, 85, 247)',
                            gradient,
                            100
                        ));
                    }
                    
                    console.log('Charts created successfully');
                } catch (error) {
                    console.error('Error creating charts:', error);
                }
            })
            .catch(error => {
                console.error('Error loading metrics:', error);
            });
    }

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM ready, initializing charts...');
        
        // Time range selector change event
        const selector = document.getElementById('timeRangeSelector');
        if (selector) {
            selector.addEventListener('change', function(e) {
                const hours = parseInt(e.target.value);
                loadCharts(hours);
            });
        }

        // Auto-refresh every 60 seconds
        setInterval(() => {
            const selector = document.getElementById('timeRangeSelector');
            const hours = selector ? parseInt(selector.value) : 24;
            loadCharts(hours);
        }, 60000);

        // Initial load
        loadCharts(24);
    });
</script>
@endpush

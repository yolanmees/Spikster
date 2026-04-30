<div>
    <x-stat-chart chart-id="cpuChart" :title="__('spikster.server_cpu_realtime_load')" :time-range-options="$timeRangeOptions" />

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
        <script>
            var cpuChartInstance;

            function renderCpuChart() {
                var ctx = document.getElementById("cpuChart");
                if (cpuChartInstance) cpuChartInstance.destroy();
                cpuChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: { labels: @json($labels), datasets: @json($dataset) },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            xAxes: [{ gridLines: { display: false }, ticks: { maxTicksLimit: 7 } }],
                            yAxes: [{ ticks: { min: 0, max: 100, maxTicksLimit: 5 }, gridLines: { color: "rgba(0,0,0,.125)" } }]
                        },
                        legend: { display: false }
                    }
                });
            }

            renderCpuChart();

            document.addEventListener('livewire:init', () => {
                Livewire.hook('commit', ({ component, succeed }) => {
                    succeed(() => {
                        queueMicrotask(() => {
                            if (component.name === 'stats.cpu') renderCpuChart();
                        });
                    });
                });
            });
        </script>
    @endpush
</div>

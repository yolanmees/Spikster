<div>
    <x-stat-chart chart-id="loadChart" :title="__('Load Average')" :time-range-options="$timeRangeOptions" />

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
        <script>
            var loadChartInstance;

            function renderLoadChart() {
                var ctx = document.getElementById("loadChart");
                if (loadChartInstance) loadChartInstance.destroy();
                loadChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: { labels: @json($labels), datasets: @json($dataset) },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            xAxes: [{ gridLines: { display: false }, ticks: { maxTicksLimit: 7 } }],
                            yAxes: [{ ticks: { min: 0, maxTicksLimit: 5 }, gridLines: { color: "rgba(0,0,0,.125)" } }]
                        },
                        legend: { display: false }
                    }
                });
            }

            renderLoadChart();

            document.addEventListener('livewire:init', () => {
                Livewire.hook('commit', ({ component, succeed }) => {
                    succeed(() => {
                        queueMicrotask(() => {
                            if (component.name === 'stats.load') renderLoadChart();
                        });
                    });
                });
            });
        </script>
    @endpush
</div>

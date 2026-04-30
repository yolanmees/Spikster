<div>
    <x-stat-chart chart-id="memChart" :title="__('Memory Usage')" :time-range-options="$timeRangeOptions" />

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
        <script>
            var memChartInstance;

            function renderMemChart() {
                var ctx = document.getElementById("memChart");
                if (memChartInstance) memChartInstance.destroy();
                memChartInstance = new Chart(ctx, {
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

            renderMemChart();

            document.addEventListener('livewire:init', () => {
                Livewire.hook('commit', ({ component, succeed }) => {
                    succeed(() => {
                        queueMicrotask(() => {
                            if (component.name === 'stats.mem') renderMemChart();
                        });
                    });
                });
            });
        </script>
    @endpush
</div>

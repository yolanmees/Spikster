<div>
    <x-stat-chart :chart-id="$type . 'Chart'" :title="$title" :time-range-options="$timeRangeOptions" />

    @push('scripts')
        <script>
            (function() {
                var chartId = '{{ $type }}Chart';
                var yMax = {{ is_null($yMax) ? 'null' : $yMax }};
                var chartInstance;

                function renderChart() {
                    var ctx = document.getElementById(chartId);
                    if (!ctx) return;
                    if (chartInstance) chartInstance.destroy();
                    chartInstance = new Chart(ctx, {
                        type: 'line',
                        data: { labels: @json($labels), datasets: @json($dataset) },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            scales: {
                                xAxes: [{ gridLines: { display: false }, ticks: { maxTicksLimit: 7 } }],
                                yAxes: [{
                                    ticks: yMax !== null ? { min: 0, max: yMax, maxTicksLimit: 5 } : { min: 0, maxTicksLimit: 5 },
                                    gridLines: { color: "rgba(0,0,0,.125)" }
                                }]
                            },
                            legend: { display: false }
                        }
                    });
                }

                renderChart();

                document.addEventListener('livewire:init', () => {
                    Livewire.hook('commit', ({ component, succeed }) => {
                        succeed(() => {
                            queueMicrotask(() => {
                                if (component.id === '{{ $this->getId() }}') renderChart();
                            });
                        });
                    });
                });
            })();
        </script>
    @endpush
</div>

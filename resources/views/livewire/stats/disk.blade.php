<div>
    <x-card header="{{ 'Disk Usage' }}" size="md" dark="false">
        <!-- Time Range Selector -->
        <div class="mb-4 flex justify-end">
            <select wire:model.live="timeRange"
                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                @foreach ($timeRangeOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <!-- Chart -->
        <canvas id="diskChart" width="100%" height="40"></canvas>
        <div class="space"></div>
    </x-card>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
        <script>
            var diskChartInstance;

            function renderDiskChart() {
                var disk = document.getElementById("diskChart");

                // Destroy existing chart if it exists
                if (diskChartInstance) {
                    diskChartInstance.destroy();
                }

                diskChartInstance = new Chart(disk, {
                    type: 'line',
                    showXLabels: 10,
                    data: {
                        labels: @json($labels),
                        datasets: @json($dataset)
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            xAxes: [{
                                time: {
                                    unit: 'date'
                                },
                                gridLines: {
                                    display: false
                                },
                                ticks: {
                                    maxTicksLimit: 7
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    min: 0,
                                    max: 100,
                                    maxTicksLimit: 5
                                },
                                gridLines: {
                                    color: "rgba(0, 0, 0, .125)",
                                }
                            }]
                        },
                        legend: {
                            display: false
                        }
                    }
                });
            }

            // Initial render
            renderDiskChart();

            // Re-render on Livewire updates
            document.addEventListener('livewire:init', () => {
                Livewire.hook('commit', ({
                    component,
                    commit,
                    respond,
                    succeed,
                    fail
                }) => {
                    succeed(({
                        snapshot,
                        effect
                    }) => {
                        queueMicrotask(() => {
                            if (component.name === 'stats.disk') {
                                renderDiskChart();
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
</div>

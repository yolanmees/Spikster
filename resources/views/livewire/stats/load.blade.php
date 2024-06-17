<div>
    <x-card header="{{ __('spikster.server_cpu_realtime_load') }}" size="md" dark="false">
        <canvas id="loadChart" width="100%" height="40"></canvas>
        <div class="space"></div>
    </x-card>
    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
    <script>
        load = document.getElementById("loadChart");
        loadChart = new Chart(load, {
            type: 'line'
            , showXLabels: 10
            , data: {
                labels: @json($labels)
                , datasets: @json($dataset)
            }
            , options: {
                scales: {
                    xAxes: [{
                        time: {
                            unit: 'date'
                        }
                        , gridLines: {
                            display: false
                        }
                        , ticks: {
                            maxTicksLimit: 7
                        }
                    }]
                    , yAxes: [{
                        ticks: {
                            min: 0
                            , maxTicksLimit: 5
                        }
                        , gridLines: {
                            color: "rgba(0, 0, 0, .125)"
                        , }
                    }]
                }
                , legend: {
                    display: false
                }
            }
        });

    </script>
    @endpush
</div>


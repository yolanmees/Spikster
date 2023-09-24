<div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-microchip fs-fw mr-1"></i>
            {{ __('cipi.server_cpu_realtime_load') }}
        </div>
        <div class="card-body">
            <canvas id="loadChart" width="100%" height="40"></canvas>
            <div class="space"></div>
        </div>
    </div>

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


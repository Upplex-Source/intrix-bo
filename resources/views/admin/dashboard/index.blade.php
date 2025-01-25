<style>
    .dt-buttons{
        display: none
    }
    .dt-length-0{
        margin-left: 10px
    }
    .apexcharts-datalabels{
        display: none
    }
    .page-title{
        color: #aad418;
        margin-bottom: -10px;
    }
</style>

<?php
$columns = [
    [
        'type' => 'default',
        'id' => 'title',
        'title' => __( 'product.title' ),
    ],
    [
        'type' => 'default',
        'id' => 'product_code',
        'title' => __( 'product.product_code' ),
    ],
    [
        'type' => 'default',
        'id' => 'stock_quantity',
        'title' => __( 'product.stock_quantity' ),
    ],
    [
        'type' => 'default',
        'id' => 'status',
        'title' => __( 'product.warehouse' ),
    ],
];
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.dashboard' ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="nk-block mb-3">
    
    <div class="row g-gs">
        <div class="col-xxl-3 col-sm-3 col-xs-3">
            <div class="card">
                <div class="nk-ecwg nk-ecwg6">
                    <div class="card-inner">
                        <div class="card-title-group">
                            <div class="card-title">
                                <h6 class="title">{{ __( 'dashboard.today_revenue' ) }}</h6>
                            </div>
                        </div>
                        <div class="data">
                            <div class="data-group">
                                <div class="amount" id="today_revenue" style="white-space: normal; word-wrap: break-word; font-size:1.5rem">
                                    <div class="spinner-grow text-secondary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- .card-inner -->
                </div><!-- .nk-ecwg -->
            </div><!-- .card -->
        </div><!-- .col -->
        <div class="col-xxl-3 col-sm-3 col-xs-3">
            <div class="card">
                <div class="nk-ecwg nk-ecwg6">
                    <div class="card-inner">
                        <div class="card-title-group">
                            <div class="card-title">
                                <h6 class="title">{{ __( 'dashboard.total_revenue' ) }}</h6>
                            </div>
                        </div>
                        <div class="data">
                            <div class="data-group">
                                <div class="amount" id="total_revenue" style="white-space: normal; word-wrap: break-word; font-size:1.5rem">
                                    <div class="spinner-grow text-secondary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- .card-inner -->
                </div><!-- .nk-ecwg -->
            </div><!-- .card -->
        </div><!-- .col -->
        <div class="col-xxl-3 col-sm-3 col-xs-3">
            <div class="card">
                <div class="nk-ecwg nk-ecwg6">
                    <div class="card-inner">
                        <div class="card-title-group">
                            <div class="card-title">
                                <h6 class="title">{{ __( 'dashboard.new_users' ) }}</h6>
                            </div>
                        </div>
                        <div class="data">
                            <div class="data-group">
                                <div class="amount" id="new_users" style="white-space: normal; word-wrap: break-word; font-size:1.5rem">
                                    <div class="spinner-grow text-secondary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- .card-inner -->
                </div><!-- .nk-ecwg -->
            </div><!-- .card -->
        </div><!-- .col -->
        <div class="col-xxl-3 col-sm-3 col-xs-3">
            <div class="card">
                <div class="nk-ecwg nk-ecwg6">
                    <div class="card-inner">
                        <div class="card-title-group">
                            <div class="card-title">
                                <h6 class="title">{{ __( 'dashboard.total_users' ) }}</h6>
                            </div>
                        </div>
                        <div class="data">
                            <div class="data-group">
                                <div class="amount" id="total_users" style="white-space: normal; word-wrap: break-word; font-size:1.5rem">
                                    <div class="spinner-grow text-secondary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- .card-inner -->
                </div><!-- .nk-ecwg -->
            </div><!-- .card -->
        </div><!-- .col -->
    </div>

    <div class="row g-gs mt-0">
        <h4 class="nk-block-title page-title">{{ __( 'dashboard.busineess_insights' ) }}</h3>

        <div class="col-xxl-6 col-sm-6 col-xs-6">
            <div class="card card-full card-chart">
                <div class="nk-ecwg nk-ecwg8 h-100">
                    <div class="card-inner">
                        <div class="card-title-group mb-3">
                            <div class="card-title">
                                <h6 class="title">{{ __( 'dashboard.total_revenue_chart' ) }}</h6>
                            </div>
                            <div class="card-tools">
                                <div class="dropdown">
                                    <a href="#" class="dropdown-toggle link link-light link-sm dropdown-indicator chart-type-selector" data-bs-toggle="dropdown" data-chart-id="chart1">Daily</a>
                                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-end">
                                        <ul class="link-list-opt">
                                            <li><a href="#" data-type="day" class=" chart-type-option active"   data-chart-id="chart1"><span>Daily</span></a></li>
                                            <li><a href="#" data-type="week"  class=" chart-type-option"  data-chart-id="chart1"><span>Weekly</span></a></li>
                                            <li><a href="#" data-type="month" class=" chart-type-option"  data-chart-id="chart1" ><span>Monthly</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="nk-ecwg8-ck">
                            <div id="chart1"></div>
                        </div>
                    </div><!-- .card-inner -->
                </div>
            </div><!-- .card -->
        </div><!-- .col -->
        
        <div class="col-xxl-6 col-sm-6 col-xs-6">
            <div class="card card-full card-chart">
                <div class="nk-ecwg nk-ecwg8 h-100">
                    <div class="card-inner">
                        <div class="card-title-group mb-3">
                            <div class="card-title">
                                <h6 class="title">{{ __( 'dashboard.total_reload' ) }}</h6>
                            </div>
                            <div class="card-tools">
                                <div class="dropdown">
                                    <a href="#" class="dropdown-toggle link link-light link-sm dropdown-indicator chart-type-selector" data-bs-toggle="dropdown" data-chart-id="chart2">Daily</a>
                                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-end">
                                        <ul class="link-list-opt">
                                            <li><a href="#" data-type="day" class=" chart-type-option active" data-chart-id="chart2" ><span>Daily</span></a></li>
                                            <li><a href="#" data-type="week"  class=" chart-type-option" data-chart-id="chart2"><span>Weekly</span></a></li>
                                            <li><a href="#" data-type="month" class=" chart-type-option" data-chart-id="chart2" ><span>Monthly</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="nk-ecwg8-ck">
                            <div id="chart2"></div>
                        </div>
                    </div><!-- .card-inner -->
                </div>
            </div><!-- .card -->
        </div><!-- .col -->
        
        <div class="col-xxl-6 col-sm-6 col-xs-6">
            <div class="card card-full card-chart">
                <div class="nk-ecwg nk-ecwg8 h-100">
                    <div class="card-inner">
                        <div class="card-title-group mb-3">
                            <div class="card-title">
                                <h6 class="title">{{ __( 'dashboard.total_cups_chart' ) }}</h6>
                            </div>
                            <div class="card-tools">
                                <div class="dropdown">
                                    <a href="#" class="dropdown-toggle link link-light link-sm dropdown-indicator chart-type-selector" data-bs-toggle="dropdown" data-chart-id="chart3">Daily</a>
                                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-end">
                                        <ul class="link-list-opt">
                                            <li><a href="#" data-type="day" class=" chart-type-option active"  data-chart-id="chart3" ><span>Daily</span></a></li>
                                            <li><a href="#" data-type="week"  class=" chart-type-option"  data-chart-id="chart3"><span>Weekly</span></a></li>
                                            <li><a href="#" data-type="month" class=" chart-type-option"  data-chart-id="chart3" ><span>Monthly</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="nk-ecwg8-ck">
                            <div id="chart3"></div>
                        </div>
                    </div><!-- .card-inner -->
                </div>
            </div><!-- .card -->
        </div><!-- .col -->
        
        <div class="col-xxl-6 col-sm-6 col-xs-6">
            <div class="card card-full card-chart">
                <div class="nk-ecwg nk-ecwg8 h-100">
                    <div class="card-inner">
                        <div class="card-title-group mb-3">
                            <div class="card-title">
                                <h6 class="title">{{ __( 'dashboard.total_users_chart' ) }}</h6>
                            </div>
                            <div class="card-tools">
                                <div class="dropdown">
                                    <a href="#" class="dropdown-toggle link link-light link-sm dropdown-indicator chart-type-selector" data-bs-toggle="dropdown" data-chart-id="chart4">Daily</a>
                                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-end">
                                        <ul class="link-list-opt">
                                            <li><a href="#" data-type="day" class=" chart-type-option active" data-chart-id="chart4" ><span>Daily</span></a></li>
                                            <li><a href="#" data-type="week"  class=" chart-type-option" data-chart-id="chart4"><span>Weekly</span></a></li>
                                            <li><a href="#" data-type="month" class=" chart-type-option" data-chart-id="chart4" ><span>Monthly</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="nk-ecwg8-ck">
                            <div id="chart4"></div>
                        </div>
                    </div><!-- .card-inner -->
                </div>
            </div><!-- .card -->
        </div><!-- .col -->

    </div><!-- .row -->

</div><!-- .nk-block -->

<script src="{{ asset( 'admin/js/apexcharts.min.js' ) . Helper::assetVersion() }}"></script>
<script src="{{ asset( 'admin/plugins/apexcharts-bundle/js/apexcharts.min.js' ) . Helper::assetVersion() }}"></script>
                                
<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let totalOrderOption = {
            noData: {  
                text: "Loading...",  
                style: {  
                    color: "#000000",  
                    fontSize: '14px',  
                    fontFamily: 'DM Sans',
                },
            },
            series: [],
            colors: ['#1ee0ac', '#33C7F4'],
            chart: {
                fontFamily: 'DM Sans, sans-serif',
                foreColor: '#9ba7b2',
                height: 200,
                type: 'bar',
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                },
                yaxis: {
                labels: {
                    formatter: function(value) {
                        return value.toFixed(2).replace(/\.00$/, ''); // Show two decimals, remove ".00" if not needed
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return value.toFixed(2); // Show exactly two decimals in the tooltip
                    }
                }
            },
            xaxis: {
                labels: {
                    formatter: function(value) {
                        return value.toFixed(2).replace(/\.00$/, ''); // Show two decimals, remove ".00" if not needed
                    }
                }
            }
        };

        // Create separate options for chart3 and chart4 with no decimals
        let chart3Options = JSON.parse(JSON.stringify(totalOrderOption));
        chart3Options.yaxis.labels.formatter = function(value) {
            return Math.floor(value); // No decimal places for chart3
        };
        chart3Options.tooltip.y.formatter = function(value) {
            return Math.floor(value); // No decimal places for chart3 tooltip
        };

        let chart4Options = JSON.parse(JSON.stringify(totalOrderOption));
        chart4Options.yaxis.labels.formatter = function(value) {
            return Math.floor(value); // No decimal places for chart4
        };
        chart4Options.tooltip.y.formatter = function(value) {
            return Math.floor(value); // No decimal places for chart4 tooltip
        };

        // Assign options to charts
        let charts = [
            { id: 'chart1', name: '{{ __( "template.orders" ) }}', options: JSON.parse(JSON.stringify(totalOrderOption)) }, // Two decimals
            { id: 'chart2', name: '{{ __( "dashboard.topup" ) }}', options: JSON.parse(JSON.stringify(totalOrderOption)) }, // Two decimals
            { id: 'chart3', name: '{{ __( "dashboard.cups" ) }}', options: chart3Options }, // No decimals
            { id: 'chart4', name: '{{ __( "dashboard.users" ) }}', options: chart4Options }  // No decimals
        ];

        // Render all charts
        charts.forEach(chartConfig => {
            let chart = new ApexCharts(document.querySelector(`#${chartConfig.id}`), chartConfig.options);
            chart.render();
        });


        // Initialize all charts
        charts.forEach(chartConfig => {
            let chart = new ApexCharts(document.querySelector(`#${chartConfig.id}`), chartConfig.options);
            chart.render();
            chartConfig.instance = chart; // Store chart instance for later updates
            loadChartData(chartConfig.id, 'day'); // Load initial data
        });

        function loadChartData(chartId, type) {
            $.ajax({
                url: '{{ route( 'admin.dashboard.totalRevenueStatistics' ) }}',
                type: 'POST',
                data: { type, chartId, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    const chartConfig = charts.find(chart => chart.id === chartId);
                    if (chartConfig) {
                        chartConfig.instance.updateOptions({
                            xaxis: {
                                categories: response.xAxis
                            }
                        });
                        chartConfig.instance.updateSeries([
                            {
                                name: chartConfig.name,
                                data: response.orderData
                            }
                        ]);
                    }
                }
            });
        }

        $(document).on('click', '.chart-type-option', function(e) {
            e.preventDefault();

            const selectedType = $(this).data('type');
            const chartId = $(this).data('chart-id');
            const displayText = $(this).text();

            // Update dropdown text
            $(`.chart-type-selector[data-chart-id="${chartId}"]`).text(displayText);
            console.log($(`.chart-type-selector[data-chart-id="${chartId}"]`))
            // Remove active class and add to the clicked option
            $(`.chart-type-option[data-chart-id="${chartId}"]`).removeClass('active');
            $(this).addClass('active');

            // Load chart data for the selected type
            loadChartData(chartId, selectedType);
        });

        getDashboardData();

        function getDashboardData() {
            $.ajax( {
                url: '{{ route( 'admin.dashboard.getDashboardData' ) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function( response ) {
                    $( '#today_revenue' ).html( response.today_revenue );
                    $( '#total_revenue' ).html( response.total_revenue );
                    $( '#new_users' ).html( response.new_users );
                    $( '#total_users' ).html( response.total_users );
                },
            } );
        }

    } );
</script>
                                <div class="nk-block-head nk-block-head-sm">
                                    <div class="nk-block-between">
                                        <div class="nk-block-head-content">
                                            <h3 class="nk-block-title page-title">{{ __( 'template.dashboard' ) }}</h3>
                                        </div><!-- .nk-block-head-content -->
                                    </div><!-- .nk-block-between -->
                                </div><!-- .nk-block-head -->
                                <div class="nk-block">
                                    <div class="row g-gs">
                                        <div class="col-xxl-3 col-sm-6">
                                            <div class="card">
                                                <div class="nk-ecwg nk-ecwg6">
                                                    <div class="card-inner">
                                                        <div class="card-title-group">
                                                            <div class="card-title">
                                                                <h6 class="title">{{ __( 'dashboard.total_owners' ) }}</h6>
                                                            </div>
                                                        </div>
                                                        <div class="data">
                                                            <div class="data-group">
                                                                <div class="amount" id="total_owners">
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
                                        <div class="col-xxl-3 col-sm-6">
                                            <div class="card">
                                                <div class="nk-ecwg nk-ecwg6">
                                                    <div class="card-inner">
                                                        <div class="card-title-group">
                                                            <div class="card-title">
                                                                <h6 class="title">{{ __( 'dashboard.total_farms' ) }}</h6>
                                                            </div>
                                                        </div>
                                                        <div class="data">
                                                            <div class="data-group">
                                                                <div class="amount" id="total_farms">
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
                                        <div class="col-xxl-3 col-sm-6">
                                            <div class="card">
                                                <div class="nk-ecwg nk-ecwg6">
                                                    <div class="card-inner">
                                                        <div class="card-title-group">
                                                            <div class="card-title">
                                                                <h6 class="title">{{ __( 'dashboard.total_buyers' ) }}</h6>
                                                            </div>
                                                        </div>
                                                        <div class="data">
                                                            <div class="data-group">
                                                                <div class="amount" id="total_buyers">
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
                                        <div class="col-xxl-3 col-sm-6">
                                            <div class="card">
                                                <div class="nk-ecwg nk-ecwg6">
                                                    <div class="card-inner">
                                                        <div class="card-title-group">
                                                            <div class="card-title">
                                                                <h6 class="title">{{ __( 'dashboard.total_orders' ) }}</h6>
                                                            </div>
                                                        </div>
                                                        <div class="data">
                                                            <div class="data-group">
                                                                <div class="amount" id="total_orders">
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
                                        <div class="col-xxl-6">
                                            <div class="card card-full">
                                                <div class="nk-ecwg nk-ecwg8 h-100">
                                                    <div class="card-inner">
                                                        <div class="card-title-group mb-3">
                                                            <div class="card-title">
                                                                <h6 class="title">{{ __( 'dashboard.order_statistics' ) }}</h6>
                                                            </div>
                                                            @if ( 1 == 2 )
                                                            <div class="card-tools">
                                                                <div class="dropdown">
                                                                    <a href="#" class="dropdown-toggle link link-light link-sm dropdown-indicator" data-bs-toggle="dropdown">Weekly</a>
                                                                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-end">
                                                                        <ul class="link-list-opt no-bdr">
                                                                            <li><a href="#"><span>Daily</span></a></li>
                                                                            <li><a href="#" class="active"><span>Weekly</span></a></li>
                                                                            <li><a href="#"><span>Monthly</span></a></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                        <div class="nk-ecwg8-ck">
                                                            <div id="chart3"></div>
                                                        </div>
                                                    </div><!-- .card-inner -->
                                                </div>
                                            </div><!-- .card -->
                                        </div><!-- .col -->
                                    </div><!-- .row -->
                                </div><!-- .nk-block -->

<script src="{{ asset( 'admin/js/apexcharts.min.js' ) . Helper::assetVersion() }}"></script>
                                
<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let totalOrderOption = {
            noData: {  
                text: "Loading...",  
                style: {  
                    color: "#000000",  
                    fontSize: '14px',  
                    fontFamily: 'DM Sans',
                } ,
            },
            series: [],
            colors: ['#1ee0ac', '#854fff'],
            chart: {
                fontFamily: 'DM Sans, sans-serif',
                foreColor: '#9ba7b2',
                height: 200,
                type: 'area',
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                },
            },
            yaxis: {
                labels: {
                    formatter: function( v ) {
                        return Math.floor( v );
                    }
                }
            }
        }

        let chart = new ApexCharts( document.querySelector( '#chart3' ), totalOrderOption );
        chart.render();
        loadChartSales( 'day' );

        function loadChartSales( type ) {

            $.ajax( {
                url: '{{ route( 'admin.dashboard.getExpensesStatistics' ) }}',
                type: 'POST',
                data: { type, _token: '{{ csrf_token() }}' },
                success: function( response ) {
                    chart.updateOptions( {
                        xaxis: {
                            categories: response.xAxis
                        }
                    } )
                    chart.updateSeries( [ {
                        name: '{{ __( 'template.orders' ) }}',
                        data: response.orderData
                    } ] );
                }
            } );
        }

        getDashboardData();

        function getDashboardData() {
            $.ajax( {
                url: '{{ route( 'admin.dashboard.getDashboardData' ) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function( response ) {
                    $( '#total_owners' ).html( response.total_owners );
                    $( '#total_farms' ).html( response.total_farms );
                    $( '#total_buyers' ).html( response.total_buyers );
                    $( '#total_orders' ).html( response.total_orders );
                },
            } );
        }
    } );
</script>
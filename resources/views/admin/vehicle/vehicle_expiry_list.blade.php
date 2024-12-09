<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.vehicle_expiry_list' ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<?php
$columns = [
    [
        'type' => 'default',
        'id' => 'select_row',
        'title' => '',
    ],
    [
        'type' => 'default',
        'id' => 'dt_no',
        'title' => 'No.',
    ],
    // [
    //     'type' => 'date',
    //     'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'datatables.created_date' ) ] ),
    //     'id' => 'created_date',
    //     'title' => __( 'datatables.created_date' ),
    // ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'vehicle.license_plate' ) ] ),
        'id' => 'license_plate',
        'title' => __( 'vehicle.license_plate' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'vehicle.driver' ) ] ),
        'id' => 'driver',
        'title' => __( 'vehicle.driver' ),
    ],
    // [
    //     'type' => 'input',
    //     'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'vehicle.name' ) ] ),
    //     'id' => 'name',
    //     'title' => __( 'vehicle.name' ),
    // ],
    // [
    //     'type' => 'input',
    //     'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'vehicle.type' ) ] ),
    //     'id' => 'type',
    //     'title' => __( 'vehicle.type' ),
    // ],
    [
        'type' => 'date',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'vehicle.road_tax_expiry_date' ) ] ),
        'id' => 'road_tax_expiry_date',
        'title' => __( 'vehicle.road_tax_expiry_date' ),
    ],
    [
        'type' => 'date',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'vehicle.insurance_expiry_date' ) ] ),
        'id' => 'insurance_expiry_date',
        'title' => __( 'vehicle.insurance_expiry_date' ),
    ],
    [
        'type' => 'date',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'vehicle.inspection_expiry_date' ) ] ),
        'id' => 'inspection_expiry_date',
        'title' => __( 'vehicle.inspection_expiry_date' ),
    ],
    [
        'type' => 'date',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'vehicle.permit_expiry_date' ) ] ),
        'id' => 'permit_expiry_date',
        'title' => __( 'vehicle.permit_expiry_date' ),
    ],
    [
        'type' => 'select',
        'options' => $data['status'],
        'id' => 'status',
        'title' => __( 'datatables.status' ),
    ],
    [
        'type' => 'default',
        'id' => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
];
?>

<x-data-tables id="vehicle_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );
    
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var typeMapper = @json( $data['type'] ),
    inServiceMapper = @json( $data['in_service'] ),
    statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#vehicle_table',
    dt_table_config = {
        language: {
            'lengthMenu': '{{ __( "datatables.lengthMenu" ) }}',
            'zeroRecords': '{{ __( "datatables.zeroRecords" ) }}',
            'info': '{{ __( "datatables.info" ) }}',
            'infoEmpty': '{{ __( "datatables.infoEmpty" ) }}',
            'infoFiltered': '{{ __( "datatables.infoFiltered" ) }}',
            'paginate': {
                'previous': '{{ __( "datatables.previous" ) }}',
                'next': '{{ __( "datatables.next" ) }}',
            }
        },
        ajax: {
            url: '{{ route( 'admin.vehicle.allVehicles' ) }}',
            data: {
                'expiry_checking' : true,
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'vehicles',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            // { data: 'path' },
            // { data: 'created_at' },
            { data: 'license_plate' },
            { data: 'employee.name' },
            // { data: 'name' },
            // { data: 'type' },
            { data: 'local_road_tax_expiry_date' },
            { data: 'local_insurance_expiry_date' },
            { data: 'local_inspection_expiry_date' },
            { data: 'local_permit_expiry_date' },
            { data: 'status' },
            { data: 'encrypted_id' },
        ],
        columnDefs: [
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                orderable: false,
                width: '1%',
                render: function( data, type, row, meta ) {
                    table_no = meta.row + 1;
                    indicator = '';

                    if ( data.local_road_tax_expiry_date_status || data.local_insurance_expiry_date_status || data.local_inspection_expiry_date_status || data.local_permit_expiry_date_status ) {
                        indicator = '<span style=" background-color: #ff0000; "class = "expiry-status" ></span>';
                    } else {
                        indicator = '<span style=" background-color: #ff7a1b; "class = "expiry-status"></span>';
                    }

                    return indicator + table_no;
                },
            },
            // {
            //     targets: parseInt( '{{ Helper::columnIndex( $columns, "photo" ) }}' ),
            //     orderable: false,
            //     width: '75px',
            //     class: "text-center",
            //     render: function( data, type, row, meta ) {
            //         return data ? ( '<img src="' + data + '" width="75px" />' ) : '<img src="{{ asset( 'admin/images/logo.png' ) }}" width="75px" style="opacity:.5;" />';
            //     },
            // },
            // {
            //     targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),
            //     width: '10%',
            //     render: function( data, type, row, meta ) {
            //         return data;
            //     },
            // },
            // {
            //     targets: parseInt( '{{ Helper::columnIndex( $columns, "type" ) }}' ),
            //     render: function( data, type, row, meta ) {
            //         return typeMapper[data];
            //     },
            // },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "road_tax_expiry_date" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "insurance_expiry_date" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "inspection_expiry_date" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "permit_expiry_date" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "status" ) }}' ),
                render: function( data, type, row, meta ) {
                    return statusMapper[data];
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                width: '1%',
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @canany( [ 'edit vehicles', 'delete vehicles' ] )
                    let edit, status = '';

                    @can( 'edit vehicles' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete vehicles' )
                    status = row['status'] == 10 ? 
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.suspend' ) }}</span></a></li>' : 
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.activate' ) }}</span></a></li>';
                    @endcan
                    
                    let html = 
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
                                    `+status+`
                                </ul>
                            </div>
                        </div>
                        `;
                        return html;
                    @else
                    return '-';
                    @endcanany
                },
            },
        ],
    },
    table_no = 0,
    timeout = null;

    document.addEventListener( 'DOMContentLoaded', function() {

        $( '#permit_expiry_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( '#road_tax_expiry_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( '#insurance_expiry_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( '#inspection_expiry_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.vehicle.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.vehicle.updateVehicleStatus' ) }}',
                type: 'POST',
                data: {
                    'id': $( this ).data( 'id' ),
                    'status': $( this ).data( 'status' ),
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    dt_table.draw( false );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
                },
            } );
        } );
    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.warehouses' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add warehouses' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.warehouse.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- .nk-block-head-content -->
        @endcan

        <div class="modal fade" id="warehouse_products_modal" tabindex="-1" role="dialog" aria-labelledby="warehouseProductsLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('template.warehouse_products') }}</h5>
                        <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close"><em class="icon ni ni-cross"></em></a>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <div id="warehouse_products_list" class="mt-3">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  

    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<?php
$columns = [
    [
        'type' => 'default',
        'id' => 'dt_no',
        'title' => 'No.',
    ],
    [
        'type' => 'date',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'datatables.created_date' ) ] ),
        'id' => 'created_date',
        'title' => __( 'datatables.created_date' ),
    ],
    [
        'type' => 'default',
        'id' => 'image',
        'title' => __( 'warehouse.image' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'warehouse.title' ) ] ),
        'id' => 'title',
        'title' => __( 'warehouse.title' ),
    ],
    // [
    //     'type' => 'default',
    //     'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'warehouse.number_of_product' ) ] ),
    //     'id' => 'number_of_product',
    //     'title' => __( 'warehouse.number_of_product' ),
    // ],
    // [
    //     'type' => 'input',
    //     'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'warehouse.stock_quantity' ) ] ),
    //     'id' => 'stock_quantity',
    //     'title' => __( 'warehouse.stock_quantity' ),
    // ],
    // [
    //     'type' => 'input',
    //     'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'warehouse.stock_worth' ) ] ),
    //     'id' => 'stock_worth',
    //     'title' => __( 'warehouse.stock_worth' ),
    // ],
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

<x-data-tables id="warehouse_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );
    
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#warehouse_table',
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
            url: '{{ route( 'admin.warehouse.allWarehouses' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'warehouses',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: 'created_at' },
            { data: 'image_path' },
            { data: 'title' },
            // { data: 'number_of_product' },
            // { data: 'stock_quantity' },
            // { data: 'stock_worth' },
            { data: 'status' },
            { data: 'encrypted_id' },
        ],
        columnDefs: [
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                orderable: false,
                width: '1%',
                render: function( data, type, row, meta ) {
                    return table_no += 1;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "image" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    if ( data ) {

                        return '<img src="' + ( data ? data : '{{ asset( 'admin/images/placeholder.png' ) }}' ) + '" width="75px" />';

                    } else {

                        return '<img src="' + '{{ asset( 'admin/images/placeholder.png' ) }}' + '" width="75px" />'
                        
                    }
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "parent_warehouse" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "remarks" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data : '-' ;
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

                    @canany( [ 'edit warehouses', 'delete warehouses' ] )
                    let edit, status = '';

                    @can( 'edit warehouses' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    edit = '<li class="dt-stock" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.view_inventories' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete warehouses' )
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

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.warehouse.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.warehouse.updateWarehouseStatus' ) }}',
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

        $( document ).on( 'click', '.dt-stock', function() {

            $productsList.html('<p>{{ __("warehouse.loading_products") }}</p>');

            $.ajax({
                url: '{{ route( 'admin.warehouse.oneWarehouse' ) }}',
                method: 'GET',
                data: {
                    'id': $( this ).data( 'id' ),
                    '_token': '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.products.length > 0) {
                        let productHtml = '<ul class="list-group">';
                        response.products.forEach(product => {
                            productHtml += `<li class="list-group-item">${product.name} (RM ${product.price})</li>`;
                        });
                        productHtml += '</ul>';
                        $productsList.html(productHtml);
                    } else {
                        $productsList.html('<p>{{ __("warehouse.no_products_found") }}</p>');
                    }
                },
                error: function () {
                    $productsList.html('<p>{{ __("warehouse.error_loading_products") }}</p>');
                }
            });
        });

    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) }}"></script>

<style>
    @media (max-width: 768px) { /* Target mobile screens */
        .modal-body {
            max-height: 50vh; /* Adjust height as needed */
            overflow-y: auto;
        }
    }
</style>
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.orders' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @if( 1 == 2 )
        @can( 'add orders' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.order.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- .nk-block-head-content -->
        @endcan
        @endif
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<?php
$order_view = 'order_view';

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
    [
        'type' => 'date',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'datatables.created_date' ) ] ),
        'id' => 'created_date',
        'title' => __( 'datatables.created_date' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'order.reference' ) ] ),
        'id' => 'reference',
        'title' => __( 'order.reference' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'order.user' ) ] ),
        'id' => 'user',
        'title' => __( 'order.user' ),
    ],
    [
        'type' => 'default',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'order.total' ) ] ),
        'id' => 'total_price',
        'title' => __( 'order.total' ) . ' (' . __( 'order.rm' ) . ')',
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

<x-data-tables id="order_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<div class="modal fade" id="modal_order_view" tabindex="-1">
    <div class="modal-dialog modal-lg"> <!-- Increased size for better layout -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __( 'order.order_details' ) }}</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Side: User & Address Details -->
                    <div class="col-md-6">
                        <!-- User Information -->
                        <div class="mb-3">
                            <div class="border-bottom py-2" data-bs-toggle="collapse" data-bs-target="#userInformation" aria-expanded="false" aria-controls="userInformation" style="cursor: pointer;">
                                <strong>User Information</strong>
                                <em class="icon ni ni-chevron-down"></em>
                            </div>
                            <div class="mt-2" id="userInformation">

                                <div class="mb-2 row">
                                    <label class="col-sm-5 col-form-label">User</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_fullname" readonly>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label class="col-sm-5 col-form-label">Email</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_email" readonly>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label class="col-sm-5 col-form-label">Phone Number</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_phone_number" readonly>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label class="col-sm-5 col-form-label">Reference</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_reference" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Details -->
                        <div class="mb-3">
                            <div class="border-bottom py-2" data-bs-toggle="collapse" data-bs-target="#orderAddress" aria-expanded="false" aria-controls="orderAddress" style="cursor: pointer;">
                                <strong>Address Details</strong>
                                <em class="icon ni ni-chevron-down"></em>
                            </div>
                            <div class="collapse mt-2" id="orderAddress">
                                <div class="mb-2 row">
                                    <label class="col-sm-5 col-form-label">Address 1</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_address_1" readonly>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label class="col-sm-5 col-form-label">Address 2</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_address_2" readonly>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label class="col-sm-5 col-form-label">City</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_city" readonly>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label class="col-sm-5 col-form-label">State</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_state" readonly>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label class="col-sm-5 col-form-label">Post Code</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_postcode" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Payment & Order Details -->
                    <div class="col-md-6">
                        <!-- Payment Details -->
                        <div class="mb-3">
                            <div class="border-bottom py-2" data-bs-toggle="collapse" data-bs-target="#paymentDetails" aria-expanded="false" aria-controls="paymentDetails" style="cursor: pointer;">
                                <strong>Payment Details</strong>
                                <em class="icon ni ni-chevron-down"></em>
                            </div>
                            <div class="collapse mt-2" id="paymentDetails">

                                <div class="mb-2 row">
                                    <label class="col-sm-5 col-form-label">Payment Method</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_type" readonly>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label class="col-sm-5 col-form-label">Subtotal</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_subtotal" readonly>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label class="col-sm-5 col-form-label">Tax</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_tax" readonly>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label class="col-sm-5 col-form-label">Discount</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_discount" readonly>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label class="col-sm-5 col-form-label">Total Price</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_total" readonly>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="{{ $order_view }}_status" class="col-sm-5 col-form-label">{{ __( 'order.status' ) }}</label>
                                    <div class="col-sm-7">
                                        <select class="form-select form-select-sm" id="{{ $order_view }}_status">
                                            @foreach ( $data[ 'status' ] as $key => $status)
                                                <option value="{{ $key }}">{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label class="col-sm-5 col-form-label">Remarks</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control-plaintext" id="{{ $order_view }}_remarks" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Details -->
                        <div class="mb-3">
                            <div class="border-bottom py-2" data-bs-toggle="collapse" data-bs-target="#orderDetails" aria-expanded="false" aria-controls="orderDetails" style="cursor: pointer;">
                                <strong>Order Details</strong>
                                <em class="icon ni ni-chevron-down"></em>
                            </div>
                            <div class="mt-2" id="orderDetails">
                                <div class="selections mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="{{ $order_view }}_id">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __( 'template.cancel' ) }}</button>
                <button type="button" class="btn btn-sm btn-primary">{{ __( 'template.save_changes' ) }}</button>
            </div>
        </div>
    </div>
</div>

<script>

window['columns'] = @json( $columns );
    
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#order_table',
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
            url: '{{ route( 'admin.order.allOrders' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'orders',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: null },
            { data: 'created_at' },
            { data: 'reference' },
            { data: 'fullname' },
            { data: 'total_price' },
            { data: 'status' },
            { data: 'encrypted_id' },
        ],
        columnDefs: [

            {
                // Add checkboxes to the first column
                targets: 0,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return `<input type="checkbox" class="select-row" data-id="${row.encrypted_id}">`;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                orderable: false,
                width: '1%',
                render: function (data, type, row, meta) {
                    // Calculate the row number dynamically based on the page info
                    const pageInfo = dt_table.page.info();
                    return pageInfo.start + meta.row + 1; // Adjust for 1-based numbering
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "user" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data ? data : row.company_name;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "total_price" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data;
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

                    @canany( [ 'edit orders', 'delete orders' ] )
                    let edit = '', 
                    status = '';
 
                    @can( 'edit orders' )
                    edit += '<li class="dt-view" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.view' ) }}</span></a></li>';
                    edit += '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete orders' )
                    status = row['status'] == 10 ? 
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="20"><a href="#"><em class="icon ni ni-na"></em><span>{{ __( 'datatables.order_canceled' ) }}</span></a></li>' : 
                    '<li class="dt-status" data-id="' + row['encrypted_id'] + '" data-status="10"><a href="#"><em class="icon ni ni-check-circle"></em><span>{{ __( 'datatables.order_placed' ) }}</span></a></li>';
                    @endcan
                    
                    let html = 
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
                                </ul>
                            </div>
                        </div>
                        `;
                        console.log(html)
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

        let ov = '#{{ $order_view }}',
            modalmt5Detail = new bootstrap.Modal( document.getElementById( 'modal_order_view' ) );

        $( '#created_date' ).flatpickr( {
            mode: 'range',
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.order.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-status', function() {

            $.ajax( {
                url: '{{ route( 'admin.order.updateOrderStatus' ) }}',
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

        $( document ).on( 'click', '.dt-view', function() {

            $( '#modal_order_view .form-control-plaintext' ).val( '-' );
            $( '#modal_order_view .form-select' ).val( 2 );
            $( '#modal_order_view textarea' ).val();
            $( '#modal_order_view textarea' ).val();

            let id = $( this ).data( 'id' );

            $.ajax( {
                url: '{{ route( 'admin.order.oneOrder' ) }}',
                type: 'POST',
                data: {
                    id,
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {

                    $('#{{ $order_view }}_id').val(response.id);
                    $('#{{ $order_view }}_fullname').val(response.fullname ? response.fullname : response.company_name || '-');
                    $('#{{ $order_view }}_email').val(response.email || '-');
                    $('#{{ $order_view }}_phone_number').val(response.phone_number || '-');
                    $('#{{ $order_view }}_status').val(response.status || '-');
                    $('#{{ $order_view }}_reference').val(response.reference || '-');
                    $('#{{ $order_view }}_type').val('Online Payment');
                    $('#{{ $order_view }}_subtotal').val( 'RM ' + ( response.subtotal || '0.00' ) );
                    $('#{{ $order_view }}_tax').val( 'RM ' + ( response.tax || '0.00' ) );
                    $('#{{ $order_view }}_discount').val( 'RM ' + ( response.discount || '0.00' ) );
                    $('#{{ $order_view }}_total').val( 'RM ' + ( response.total_price || '0.00' ) );
                    $('#{{ $order_view }}_address_1').val(response.address_1 || '-');
                    $('#{{ $order_view }}_address_2').val(response.address_2 || '-');
                    $('#{{ $order_view }}_city').val(response.city || '-');
                    $('#{{ $order_view }}_state').val(response.state || '-');
                    $('#{{ $order_view }}_postcode').val(response.postcode || '-');
                    $('#{{ $order_view }}_remarks').val(response.remarks || '-');

                    $('#modal_order_view .selections').empty();

                    const orderMetas = response.orderMetas || [];
                    orderMetas.forEach((meta) => {

                        $('#modal_order_view .selections').append(
                            `<div>
                                <h6>Product: ${meta.product.title} (${meta.product.code})</h6>
                                <h6>Variant: ${meta.product_variant.title}</h6>
                                <h6>Price: ${meta.product.price} ( x ${meta.quantity} unit)</h6>
                            </div><hr>`
                        );
                    });

                    const addOnMetas = response.addOnMetas || [];
                    addOnMetas.forEach((meta) => {

                        $('#modal_order_view .selections').append(
                            `<div>
                                <h6>Add On: ${meta.add_on.title} (${meta.add_on.code})</h6>
                                <h6>Price: ${meta.product.discount_price} ( x ${meta.quantity} unit)</h6>
                            </div><hr>`
                        );
                    });

                    const freeGift = response.freeGift;

                    if( freeGift ) {
                        $('#modal_order_view .selections').append(
                            `<div>
                                <h6>Add On: ${freeGift.title} (${freeGift.code})</h6>
                                <h6>Price: ${freeGift.discount_price} ( x ${freeGift.quantity} unit)</h6>
                            </div><hr>`
                        );
                    }

                    modalmt5Detail.show();
                    
                },
                error: function( error ) {
                    modalmt5Detail.hide();
                    $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                    modalDanger.show();
                },
            } );
        } );

        $( '#modal_order_view .btn-primary' ).on( 'click', function() {

            let formData = new FormData();
            formData.append( 'id', $( ov + '_id' ).val() );
            formData.append( 'status', $( ov + '_status' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.order.updateOrderStatusView' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    modalmt5Detail.hide();
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.show();
                    dt_table.draw( true );
                },
                error: function( error ) {
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $( ov + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        });

                    } else {
                        modalmt5Detail.hide();
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.show();    
                    }
                },
            } );
        } );

    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) . Helper::assetVersion() }}"></script>
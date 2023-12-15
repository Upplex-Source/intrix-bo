<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.bookings' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        @can( 'add bookings' )
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="#" id="generate_invoice_btn" class="btn btn-info">{{ __( 'template.generate_invoice' ) }}</a>
                        </li>
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.booking.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
                        </li>
                        <li class="nk-block-tools-opt">
                            <button type="button" class="btn btn-secondary dt-export">{{ __( 'template.export' ) }}</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- .nk-block-head-content -->
        @endcan
        
        <div class="modal fade" id="generate_invoice_modal">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __( 'template.generate_invoice' ) }}</h5>
                        <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close"><em class="icon ni ni-cross"></em></a>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label" for="company">{{ __( 'booking.company' ) }}</label>
                            <div class="form-control-wrap">
                                <select class="form-select" id="company_id" >
                                    <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'booking.company' ) ] ) }}</option>
                                    @foreach( $data['company'] as $key => $company )
                                    <option value="{{ $company['id'] }}">{{ $company['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="customer">{{ __( 'booking.customer_name' ) }}</label>
                            <div class="form-control-wrap">
                                <select class="form-control" id="customer_id" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'booking.customer_name' ) ] ) }}">
                                </select>
                                 <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="invoice_number">{{ __( 'booking.invoice_number' ) }}</label>
                            <div class="form-control-wrap">
                                {{-- <select class="form-control" id="invoice_number" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'booking.invoice_number' ) ] ) }}">
                                    <option value>{{ __( 'datatables.select_x', [ 'title' => __( 'booking.invoice_number' ) ] ) }}</option>
                                </select> --}}
                                <input type="text" class="form-control" id="invoice_number">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="invoice_date">{{ __( 'booking.invoice_date' ) }}</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="invoice_date">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="delivery_order_number">{{ __( 'booking.delivery_order_number' ) }}</label>
                            <div class="form-control-wrap">
                                <select class="form-control" multiple="multiple" id="delivery_order_number" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'booking.delivery_order_number' ) ] ) }}">
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-lg btn-secondary preview" data-type="preview" id="m_preview">{{ __( 'template.preview' ) }}</button>
                            <button type="button" class="btn btn-lg btn-primary submit" data-type="generate" id="m_generate_and_save">{{ __( 'template.generate_and_save' ) }}</button>
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
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'booking.reference' ) ] ),
        'id' => 'reference',
        'title' => __( 'booking.reference' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'booking.invoice_number' ) ] ),
        'id' => 'invoice_number',
        'title' => __( 'booking.invoice_number' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'booking.delivery_order_number' ) ] ),
        'id' => 'delivery_order_number',
        'title' => __( 'booking.delivery_order_number' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'booking.license_plate' ) ] ),
        'id' => 'license_plate',
        'title' => __( 'booking.license_plate' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'booking.driver' ) ] ),
        'id' => 'driver',
        'title' => __( 'booking.driver' ),
    ],
    [
        'type' => 'default',
        'id' => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
];
?>

<x-data-tables id="booking_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );
    
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#booking_table',
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
            url: '{{ route( 'admin.booking.allBookings' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'bookings',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: 'created_at' },
            { data: 'reference' },
            { data: 'invoice_number' },
            { data: 'delivery_order_number' },
            { data: 'vehicle.license_plate' },
            { data: 'driver.name' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "created_date" ) }}' ),
                width: '10%',
                render: function( data, type, row, meta ) {
                    return data;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "invoice_number" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                }
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "delivery_order_number" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                }
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "vehicle" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                }
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "driver" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data ? data : '-';
                }
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

                    @canany( [ 'edit bookings', 'delete bookings' ] )
                    let edit, status = '';

                    @can( 'edit bookings' )
                    edit = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
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
    timeout = null,
    exportPath = '{{ route( 'admin.booking.export' ) }}';

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
            window.location.href = '{{ route( 'admin.booking.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        let trc = '#generate_invoice_btn',
            aim = new bootstrap.Modal( document.getElementById( 'generate_invoice_modal' ) );

        $( trc ).click( function() {
            aim.toggle();
        } );

        $('#generate_invoice_modal').on('hidden.bs.modal', function (e) {
            clearInputs();
        });

        $( '#customer_id' ).select2( {
            dropdownParent: $('#generate_invoice_modal'),
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            allowClear: true,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.customer.allCustomers' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        name: params.term, // search term
                        status: 10,
                        start: params.page ? params.page : 0,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.customers.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.name,
                        } );
                    } );

                    return {
                        results: processedResult,
                        pagination: {
                            more: ( params.page * 10 ) < data.recordsFiltered
                        }
                    };
                }
            },
        } );

        $( '#delivery_order_number' ).select2( {
            dropdownParent: $('#generate_invoice_modal'),
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.booking.allBookings' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        delivery_order_number: params.term, // search term
                        // company_id: $('#company_id').val(),
                        status: 10,
                        start: params.page ? params.page : 0,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.bookings.map( function( v, i ) {
                        processedResult.push( {
                            id: v.delivery_order_number,
                            text: v.delivery_order_number,
                        } );
                    } );

                    return {
                        results: processedResult,
                        pagination: {
                            more: ( params.page * 10 ) < data.recordsFiltered
                        }
                    };
                }
            },
        } );

        $( '#invoice_date' ).flatpickr();
        updateInvoiceNumberState();
        updateInvoiceDateState();

        function clearInputs(){
            $('#company_id').val(null);
            $('#customer_id').val(null).trigger('change');
            $('#invoice_number').val(null).trigger('change');
            $('#invoice_number').prop('disabled', true);
            $('#invoice_date').nextAll('.flatpickr-input').val('dd/mm/yyyy');
            $('#delivery_order_number').val(null).trigger('change');
        }

        $('#company_id').on('change', function() {
            updateInvoiceNumberState();
        });
        $('#invoice_number').on('change', function() {
            updateInvoiceDateState();
        });

        function updateInvoiceNumberState() {
            if ($('#company_id').val() == '' || !$('#company_id').val()) {
                clearInputs();
            } else {
                $('#invoice_number').prop('disabled', false);

                // $( '#invoice_number' ).select2( {
                //     dropdownParent: $('#generate_invoice_modal'),
                //     language: '{{ App::getLocale() }}',
                //     theme: 'bootstrap-5',
                //     width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                //     placeholder: $( this ).data( 'placeholder' ),
                //     closeOnSelect: false,
                //     ajax: {
                //         method: 'POST',
                //         url: '{{ route( 'admin.booking.allBookings' ) }}',
                //         dataType: 'json',
                //         delay: 250,
                //         data: function (params) {
                //             return {
                //                 invoice_number: params.term, // search term
                //                 company_id: $('#company_id').val(),
                //                 status: 10,
                //                 start: params.page ? params.page : 0,
                //                 length: 10,
                //                 _token: '{{ csrf_token() }}',
                //             };
                //         },
                //         processResults: function (data, params) {
                //             params.page = params.page || 1;

                //             let processedResult = [];

                //             data.bookings.forEach(function(v, i) {
                //                 var invoiceNumber = v.invoice_number;

                //                 if (!processedResult.some(item => item.id === invoiceNumber)) {
                //                     processedResult.push({
                //                         id: invoiceNumber,
                //                         text: invoiceNumber,
                //                     });
                //                 }
                //             });

                //             return {
                //                 results: processedResult,
                //                 pagination: {
                //                     more: ( params.page * 10 ) < data.recordsFiltered
                //                 }
                //             };
                //         }
                //     },
                // } );                
            }
        }

        function updateInvoiceDateState() {
            if ($('#invoice_number').val() == '' || !$('#invoice_number').val()) {
                $('#invoice_date').nextAll('.flatpickr-input').prop('disabled', true);
            } else {
                $('#invoice_date').nextAll('.flatpickr-input').prop('disabled', false)
                $.ajax({
                    method: 'POST',
                    url: '{{ route('admin.booking.allBookings') }}',
                    dataType: 'json',
                    delay: 250,
                    data: {
                        invoice_number: $('#invoice_number').val(),
                        company_id: $('#company_id').val(),
                        start: 0,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        $('#invoice_date').val(response.bookings[0].invoice_date);
                        $('#invoice_date').nextAll('.flatpickr-input').val(response.bookings[0].invoice_date);
                    },
                    error: function(error) {
                        console.error(error);
                    },
                });

            }
        }

        $( '.submit' ).click( function(){
            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            resetInputValidation();
            
            let type = $(this).data('type');
            let formData = new FormData();

            let url = type == 'preview' ? '{{ route( 'admin.invoice.previewInvoice' ) }}' : '{{ route( 'admin.invoice.createInvoice' ) }}';
            
            formData.append( 'type', type);
            formData.append( 'company_id', $( '#company_id' ).val() );
            formData.append( 'customer_id', $( '#customer_id' ).val() );
            formData.append( 'invoice_number', $( '#invoice_number' ).val() );
            formData.append( 'invoice_date', $( '#invoice_date' ).val() );
            formData.append( 'delivery_order_number', $( '#delivery_order_number' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
    
                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.invoice.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );
    
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( '#' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                        $( '.form-control.is-invalid:first' ).get( 0 ).scrollIntoView( { block: 'center' } );
                        $( '.form-select.is-invalid:first' ).get( 0 ).scrollIntoView( { block: 'center' } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        });

        $( '.preview' ).click( function(){
            var url =  `{{ route('admin.invoice.previewInvoice', ['company_id' => ':company_id', 'customer_id' => ':customer_id', 'invoice_date' => ':invoice_date', 'invoice_number' => ':invoice_number', 'delivery_order_number' => ':delivery_order_number']) }}`
            url = url.replace('%3Acompany_id', $('#company_id').val());
            url = url.replace('%3Acustomer_id', $('#customer_id').val());
            url = url.replace('%3Ainvoice_date', $('#invoice_date').val());
            url = url.replace('%3Ainvoice_number', $('#invoice_number').val());
            url = url.replace('%3Adelivery_order_number', $('#delivery_order_number').val());
            url = url.replaceAll('amp;', '');
            window.location.href = url;
        });
    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) }}"></script>
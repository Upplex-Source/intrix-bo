<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.invoices' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        {{-- @can( 'add invoices' )
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
        @endcan --}}
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('template.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">{{ __('template.confirm') }}</button>
            </div>
        </div>
    </div>
</div>

<?php
$columns = [
    [
        'type' => 'default',
        'id' => 'dt_no',
        'title' => 'No.',
    ],
    [
        'type' => 'input',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'invoice.company' ) ] ),
        'id' => 'company',
        'title' => __( 'invoice.company' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'invoice.customer' ) ] ),
        'id' => 'customer',
        'title' => __( 'invoice.customer' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'invoice.invoice_number' ) ] ),
        'id' => 'invoice_number',
        'title' => __( 'invoice.invoice_number' ),
    ],
    [
        'type' => 'date',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'invoice.invoice_date' ) ] ),
        'id' => 'invoice_date',
        'title' => __( 'invoice.invoice_date' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'invoice.delivery_order_number' ) ] ),
        'id' => 'delivery_order_number',
        'title' => __( 'invoice.delivery_order_number' ),
    ],
    [
        'type' => 'default',
        'id' => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
];
?>

<x-data-tables id="invoice_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );
    
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var statusMapper = @json( $data['status'] ),
    dt_table,
    dt_table_name = '#invoice_table',
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
            url: '{{ route( 'admin.invoice.allInvoices' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'invoices',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 1, 'desc' ]],
        columns: [
            { data: null },
            { data: 'company.name' },
            { data: 'customer.name' },
            { data: 'invoice_number' },
            { data: 'invoice_date' },
            { data: 'do_number' },
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "company" ) }}' ),
                render: function( data, type, row, meta ) {
                    return data;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "customer" ) }}' ),
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
                targets: parseInt( '{{ Helper::columnIndex( $columns, "invoice_date" ) }}' ),
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
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                width: '1%',
                className: 'text-center',
                render: function( data, type, row, meta ) {
                    @canany( [ 'edit invoices', 'delete invoices' ] )
                    let edit, status, deletes, download = '';

                    @can( 'edit invoices' )
                    preview = '<li class="dt-edit" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-edit"></em><span>{{ __( 'template.edit' ) }}</span></a></li>';
                    @endcan

                    @can( 'preview invoices' )
                    edit = '<li class="dt-preview" data-id="' + row['encrypted_id'] + '" ><a href="#"><em class="icon ni ni-eye"></em><span>{{ __( 'template.preview' ) }}</span></a></li>';
                    @endcan

                    @can( 'delete invoices' )
                    deletes = '<li class="dt-delete" data-invoice-date="' + row['invoice_date'] +'" data-invoice-number="' + row['invoice_number'] +'" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-trash"></em><span>{{ __( 'template.delete' ) }}</span></a></li>';
                    @endcan

                    @can( 'download invoices' )
                    download = '<li class="dt-download" data-id="' + row['encrypted_id'] + '"><a href="#"><em class="icon ni ni-download"></em><span>{{ __( 'template.download' ) }}</span></a></li>';
                    @endcan
                    
                    let html = 
                        `
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-icon btn-trigger" href="#" type="button" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                            <div class="dropdown-menu">
                                <ul class="link-list-opt">
                                    `+edit+`
                                    `+preview+`
                                    `+deletes+`
                                    `+download+`
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
        $( document ).on( 'click', '.dt-edit', function() {
            window.location.href = '{{ route( 'admin.invoice.edit' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-download', function() {
            window.location.href = '{{ route( 'admin.invoice.downloadInvoice' ) }}?id=' + $( this ).data( 'id' );
        } );

        $( document ).on( 'click', '.dt-delete', function() {
            let encryptedId = $(this).data('id');
            let invoiceNumber = $(this).data('invoice-number');
            let invoiceDate = $(this).data('invoice-date');

            let deleteModalLabel = `{{ __('invoice.delete_invoice', ['invoice_number' => ':invoice_number']) }}`;
            deleteModalLabel = deleteModalLabel.replace(':invoice_number', invoiceNumber);

            let deleteMessage = `{{ __('invoice.confirm_delete', ['invoice_number' => ':invoice_number']) }}`;
            deleteMessage = deleteMessage.replace(':invoice_number', invoiceNumber);
            deleteMessage = deleteMessage.replace(':invoice_date', invoiceDate);

            $('#deleteModalLabel').html(deleteModalLabel);
            $('#deleteMessage').html(deleteMessage);
            $('#deleteModal').modal('show');
            $('#confirmDelete').attr('data-id', encryptedId);
        } );

        $( document ).on( 'click', '.dt-preview', function() {
            let id = $(this).data('id');
            var url =  `{{ route('admin.invoice.previewInvoice', ['id' => ':id']) }}`
            url = url.replace('%3Aid', id);

            window.open(url, '_blank');
        } );


         $( '#confirmDelete' ).click( function(){
            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', $(this).data('id') );
            formData.append( '_token', '{{ csrf_token() }}' );
            
            let url = "{{ route( 'admin.invoice.deleteInvoice' ) }}";
    
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
    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) }}"></script>
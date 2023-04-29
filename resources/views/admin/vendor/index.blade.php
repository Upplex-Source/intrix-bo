<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.vendors' ) }}</h3>
        </div><!-- .nk-block-head-content -->
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route( 'admin.vendor.add' ) }}" class="btn btn-primary">{{ __( 'template.add' ) }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- .nk-block-head-content -->
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
        'type' => 'default',
        'id' => 'photo',
        'title' => __( 'vendor.photo' ),
    ],
    [
        'type' => 'date',
        'placeholder' => __( 'datatables.search_x', [ 'title' => __( 'datatables.created_date' ) ] ),
        'id' => 'created_date',
        'title' => __( 'datatables.created_date' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'vendor.name' ) ] ),
        'id' => 'name',
        'title' => __( 'vendor.name' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'vendor.email' ) ] ),
        'id' => 'email',
        'title' => __( 'vendor.email' ),
    ],
    [
        'type' => 'input',
        'placeholder' =>  __( 'datatables.search_x', [ 'title' => __( 'vendor.phone_number' ) ] ),
        'id' => 'phone_number',
        'title' => __( 'vendor.phone_number' ),
    ],
    [
        'type' => 'default',
        'id' => 'type',
        'title' => __( 'vendor.type' ),
    ],
    [
        'type' => 'default',
        'id' => 'dt_action',
        'title' => __( 'datatables.action' ),
    ],
]
?>

<x-data-tables id="vendor_table" enableFilter="true" enableFooter="false" columns="{{ json_encode( $columns ) }}" />

<script>

window['columns'] = @json( $columns );
    
@foreach ( $columns as $column )
@if ( $column['type'] != 'default' )
window['{{ $column['id'] }}'] = '';
@endif
@endforeach

var typeMapper = @json( $data['type_mapper'] ),
    dt_table,
    dt_table_name = '#vendor_table',
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
            url: '{{ route( 'admin.vendor.allVendors' ) }}',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            dataSrc: 'vendors',
        },
        lengthMenu: [[10, 25],[10, 25]],
        order: [[ 2, 'desc' ]],
        columns: [
            { data: null },
            { data: 'path' },
            { data: 'created_at' },
            { data: 'name' },
            { data: 'email' },
            { data: 'phone_number' },
            { data: 'type' },
            { data: 'id' },
        ],
        columnDefs: [
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "dt_no" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return table_no += 1;
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "photo" ) }}' ),
                orderable: false,
                render: function( data, type, row, meta ) {
                    return '<img src="' + ( data ? data : '{{ asset( 'admin/images/default-small.png' ) }}' ) + '" width="75px" />';
                },
            },
            {
                targets: parseInt( '{{ Helper::columnIndex( $columns, "type" ) }}' ),
                render: function( data, type, row, meta ) {
                    return typeMapper[data];
                },
            },
            {
                targets: parseInt( '{{ count( $columns ) - 1 }}' ),
                orderable: false,
                width: '10%',
                className: 'text-center',
                render: function( data, type, row, meta ) {

                    @canany( [ 'edit administrator', 'delete administrator' ] )
                    let edit, status = '';

                    @can( 'edit administrator' )
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
            window.location.href = '{{ route( 'admin.vendor.edit' ) }}?id=' + $( this ).data( 'id' );
        } );
    } );
</script>

<script src="{{ asset( 'admin/js/dataTable.init.js' ) }}"></script>
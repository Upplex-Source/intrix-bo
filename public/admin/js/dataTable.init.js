document.addEventListener( 'DOMContentLoaded', function() {

    dt_table = $( dt_table_name ).DataTable( {
        language: dt_table_config.language,
        ajax: {
            type: 'POST',
            url: dt_table_config.ajax.url,
            data: dt_table_config.ajax.data,
            dataSrc: dt_table_config.ajax.dataSrc,
            error: function( xhr, error, code ) {
                console.log(xhr);
                console.log(error);
                console.log(code);
            },
        },
        lengthMenu: dt_table_config.lengthMenu,
        processing: true,
        serverSide: true,
        order: dt_table_config.order,
        ordering: true,
        scrollX: true,
        searchCols: dt_table_config.searchCols ? dt_table_config.searchCols : [],
        columns: dt_table_config.columns,
        columnDefs: dt_table_config.columnDefs,
        dom: `<"row justify-between g-2"
        <"col-7 col-sm-4 text-start"f>
        <"col-5 col-sm-8 text-end"l>
        <"datatable-wrap my-3"t>
        >
        <"row align-items-center"
        <"col-sm-12 col-md-7"p>
        <"col-sm-12 col-md-5 text-start text-md-end"i>
        >`,
        createdRow: function( row ) {
            $( row ).addClass( 'nk-tb-item' );
        },
        initComplete: function() {
            $( dt_table_name + '_filter' ).remove();

            let rawName = dt_table_name.replace( '#', '' );
            let lengthSelect = $( dt_table_name + '_wrapper select[name="' + rawName + '_length"]' );
            lengthSelect.addClass( 'custom-select custom-select-sm form-control form-control-sm' );
            lengthSelect.wrap( '<div class="form-control-select ms-1"></div>' )
        },
        drawCallback: function( response ) {
            if( response.json.subTotal != undefined ) {
                if( Array.isArray( response.json.subTotal ) ) {
                    $.each( response.json.subTotal, function( i, v ) {
                        $( '.dataTables_scrollFoot .subtotal' ).eq(i).html( v );
                        $( '.dataTables_scrollFoot .grandtotal' ).eq(i).html( response.json.grandTotal[i] );
                    } );
                }
            }
        }
    } );

    $( dt_table_name ).on( 'page.dt length.dt order.dt search.dt', function() {
        table_no = dt_table.page.info().page * dt_table.page.info().length;
    } );

    $( dt_table_name ).on( 'preXhr.dt', function( e, settings, data ) {
        
        window['columns'].forEach( function( v, i ) {
            if ( v.type != 'default' ) {
                data[v.id] = window[v.id];
            }
        } );
    } );

    $( '.listing-filter > input' ).on( 'keydown keypress', function(e) {

        let that = $( this );
        clearTimeout( timeout );
        timeout = setTimeout( function(){
            window[that.data( 'id' )] = that.val();
            console.log(window[that.data( 'id' )]);
            dt_table.draw();
        }, 500 );
    } );

    $( '.listing-filter > select' ).on( 'change', function() {

        let that = $( this );
        window[that.data( 'id' )] = that.val();
        dt_table.draw();
    } );

} );
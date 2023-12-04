<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.companies' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
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
                            <select class="form-control" id="invoice_number" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'booking.invoice_number' ) ] ) }}">
                                <option value>{{ __( 'datatables.select_x', [ 'title' => __( 'booking.invoice_number' ) ] ) }}</option>
                            </select>
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
                <div class="text-end">
                    <button id="cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        $( '#cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.invoice.index' ) }}';
        } );

        $( '#submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'customer_id', $( '#customer_id' ).val() );
            formData.append( 'company_id', $( '#company_id' ).val() );
            formData.append( 'invoice_number', $( '#invoice_number' ).val() );
            formData.append( 'invoice_date', $( '#invoice_date' ).val() ); 
            formData.append( 'delivery_order_number', $( '#delivery_order_number' ).val() ); 
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.invoice.updateInvoice' ) }}',
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
                    console.log(error)
                    $( 'body' ).loading( 'stop' );
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( '#' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        let invoiceDate = $( '#invoice_date' ).flatpickr();

        let customerSelect2 = $( '#customer_id' ).select2( {
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

        let deliverOrderNumberSelect2 = $( '#delivery_order_number' ).select2( {
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
                        company_id: $('#company_id').val(),
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
        let invoiceNumberSelect2 = $( '#invoice_number' ).select2( {
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
                        invoice_number: params.term, // search term
                        company_id: $('#company_id').val(),
                        status: 10,
                        start: params.page ? params.page : 0,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.bookings.forEach(function(v, i) {
                        var invoiceNumber = v.invoice_number;

                        if (!processedResult.some(item => item.id === invoiceNumber)) {
                            processedResult.push({
                                id: invoiceNumber,
                                text: invoiceNumber,
                            });
                        }
                    });

                    return {
                        results: processedResult,
                        pagination: {
                            more: ( params.page * 10 ) < data.recordsFiltered
                        }
                    };
                }
            },
        } );        

        getInvoice();

        function getInvoice() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.invoice.oneInvoice' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    if ( response.customer.name ) {
                        let option1 = new Option( response.customer.name, response.customer_id, true, true );
                        customerSelect2.append( option1 );
                        customerSelect2.trigger( 'change' );
                    }

                    if ( response.do_number ) {
                        let do_number = response.do_number.split(",");
                        do_number.map(item => {
                            let option1 = new Option( item, item, true, true );
                            deliverOrderNumberSelect2.append( option1 );
                            deliverOrderNumberSelect2.trigger( 'change' );
                        })
                    }

                    if ( response.invoice_number ) {
                        let option1 = new Option( response.invoice_number, response.invoice_number, true, true );
                        invoiceNumberSelect2.append( option1 );
                        invoiceNumberSelect2.trigger( 'change' );
                    }

                    $( '#customer_id' ).val( response.customer_id );
                    $( '#company_id' ).val( response.company_id );
                    $( '#invoice_number' ).val( response.invoice_number );

                    invoiceDate.setDate( response.invoice_date );

                    $( 'body'  ).loading( 'stop' );
                },
                error: function (response) {
                    console.log('{{ request('id') }}')
                    console.log(response);
                }
            } );
        }        
    } );
</script>
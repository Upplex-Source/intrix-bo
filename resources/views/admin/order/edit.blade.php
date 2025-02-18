<?php
$order_edit = 'order_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.orders' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row gx-5">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_email" class="col-sm-4 col-form-label">{{ __( 'user.email' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_edit }}_email">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_fullname" class="col-sm-4 col-form-label">{{ __( 'user.fullname' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_edit }}_fullname">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_company_name" class="col-sm-4 col-form-label">{{ __( 'user.company_name' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_edit }}_company_name" placeholder="optional">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_phone_number" class="col-sm-4 col-form-label">{{ __( 'user.phone_number' ) }}</label>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <button class="flex-shrink-0 inline-flex items-center input-group-text" type="button">
                                +60
                            </button>
                            <input type="text" class="form-control" id="{{ $order_edit }}_phone_number">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>                    
                </div>

                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_address_1" class="col-sm-4 col-form-label">{{ __( 'order.address_1' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_edit }}_address_1">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_address_2" class="col-sm-4 col-form-label">{{ __( 'order.address_2' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_edit }}_address_2">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_city" class="col-sm-4 col-form-label">{{ __( 'order.city' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_edit }}_city">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_state" class="col-sm-4 col-form-label">{{ __( 'customer.state' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_edit }}_state" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'customer.state' ) ] ) }}</option>
                            <option value="Johor">Johor</option>
                            <option value="Kedah">Kedah</option>
                            <option value="Kelantan">Kelantan</option>
                            <option value="Malacca">Malacca</option>
                            <option value="Negeri Sembilan">Negeri Sembilan</option>
                            <option value="Pahang">Pahang</option>
                            <option value="Penang">Penang</option>
                            <option value="Perlis">Perlis</option>
                            <option value="Sabah">Sabah</option>
                            <option value="Sarawak">Sarawak</option>
                            <option value="Selangor">Selangor</option>
                            <option value="Terengganu">Terengganu</option>
                            <option value="Kuala Lumpur">Kuala Lumpur</option>
                            <option value="Labuan">Labuan</option>
                            <option value="Putrajaya">Putrajaya</option>
                            <option value="Perak">Perak</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_postcode" class="col-sm-4 col-form-label">{{ __( 'order.postcode' ) }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="{{ $order_edit }}_postcode">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_remarks" class="col-sm-4 col-form-label">{{ __( 'order.remarks' ) }}</label>
                    <div class="col-sm-6">
                        <textarea class="form-control" name="{{ $order_edit }}_remarks" id="{{ $order_edit }}_remarks" cols="30" rows="5"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_payment_plan" class="col-sm-4 col-form-label">{{ __( 'order.payment_plan' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_edit }}_payment_plan" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'order.payment_plan' ) ] ) }}</option>
                            <option value="1">Upfront</option>
                            <option value="2">Monthly</option>
                            <option value="3">Outright</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_product" class="col-sm-4 col-form-label">{{ __( 'order.product' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_edit }}_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'order.product' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_color" class="col-sm-4 col-form-label">{{ __( 'order.color' ) }}</label>
                    <div class="col-sm-6">
                        <select class="form-select" id="{{ $order_edit }}_color" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'order.color' ) ] ) }}</option>
                            <option value="1">CHROME</option>
                            <option value="2">MATTE BLACK</option>
                            <option value="3">SATIN GOLD</option>
                            <option value="4">GUNMETAL GREY</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $order_edit }}_quantity" class="col-sm-4 col-form-label">{{ __( 'order.quantity' ) }}</label>
                    <div class="col-sm-6">
                        <input type="number" class="form-control" id="{{ $order_edit }}_quantity">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

            </div>
            <div class="col-md-12 col-lg-12 order-details">
            </div>

        </div>
        <div class="text-end">
            <button id="{{ $order_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
            &nbsp;
            <button id="{{ $order_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let oc = '#{{ $order_edit }}',
            orderDetailsContainer = $(".order-details");

        $( oc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.order.index' ) }}';
        } );

        $( oc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( '_token', '{{ csrf_token() }}' );
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'email', $( oc + '_email' ).val() );
            formData.append( 'fullname', $( oc + '_fullname' ).val() );
            formData.append( 'phone_number', $( oc + '_phone_number' ).val() );
            formData.append( 'address_1', $( oc + '_address_1' ).val() );
            formData.append( 'address_2', $( oc + '_address_2' ).val() );
            formData.append( 'city', $( oc + '_city' ).val() );
            formData.append( 'state', $( oc + '_state' ).val() );
            formData.append( 'postcode', $( oc + '_postcode' ).val() );
            formData.append( 'remarks', $( oc + '_remarks' ).val() );
            formData.append( 'payment_plan', $( oc + '_payment_plan' ).val() );
            formData.append( 'products', $(oc + '_product').val() );
            formData.append( 'color', $(oc + '_color').val() );
            formData.append( 'quantity', $(oc + '_quantity').val() );

            $.ajax( {
                url: '{{ route( 'admin.order.updateOrder' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event ) {
                        window.location.href = '{{ route( 'admin.module_parent.order.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {

                            if ( key.includes( 'order_items' ) ) {

                                let stringKey = key.split( '.' );

                                $( '#order_details_' + stringKey[1] ).find( oc + '_' + stringKey[2] ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );

                                return true;
                            }else{

                                $( oc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                            }

                        } );

                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );
        
        let userSelect2 = $( oc + '_user' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.user.allUsers' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        status: 10,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.users.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.username,
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
        
        let productSelect2 = $( oc + '_product' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: true,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.product.allProducts' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        status: 10,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.products.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.title,
                            price: v.price,
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

        productSelect2.on('change', function () {
            let productId = $(this).val();
            let colorSelect = $('#{{ $order_edit }}_color');

            if (!productId) {
                colorSelect.html('<option value="">{{ __( "datatables.select_x", ["title" => __( "order.color" )] ) }}</option>');
                return;
            }

            // AJAX call to get colors for selected product
            $.ajax({
                url: '{{ route("admin.product.allProducts") }}', // Ensure this route exists in your Laravel backend
                type: 'POST',
                data: { product: productId,
                    '_token': '{{ csrf_token() }}',
                    length: 10,
                    offset: 0,
                 },
                dataType: 'json',
                success: function (response) {

                    product = response.products[0]

                    colorSelect.empty().append('<option value="">{{ __( "datatables.select_x", ["title" => __( "order.color" )] ) }}</option>');

                    if (product.product_variants) {
                        $.each(product.product_variants, function (index, variant) {

                            colorSelect.append('<option value="' + variant.color + '">' + variant.title + '</option>');
                        });
                    }
                },
                error: function () {
                    console.error('Error fetching product colors');
                }
            });
        });

        // get order details
        getOrder();

        function getOrder() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.order.oneOrder' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    if ( response.user ) {
                        let option1 = new Option( response.user.username, response.user.id, true, true );
                        userSelect2.append( option1 );
                        userSelect2.trigger( 'change' );
                    }

                    if ( response.vending_machine ) {
                        let option1 = new Option( response.vending_machine.title, response.vending_machine.id, true, true );
                        vendingMachineSelect2.append( option1 );
                        vendingMachineSelect2.trigger( 'change' );
                    }

                    response.orderMetas.forEach((orderMeta, index) => {

                        const productId = orderMeta.product.id;
                        const productName = orderMeta.product.title;
                        const productPrice = orderMeta.product.price;
                        const subTotal = orderMeta.subtotal;

                        let option = new Option(productName, productId, true, true); 
                        productSelect2.append(option);

                        let data = {
                            id: productId,
                            price: productPrice,
                        };

                        let $current_option_data = $( oc + '_product' ).select2('data').find(function (currentOption) {
                            return currentOption.id == data['id']
                        });

                        if ($current_option_data) {
                            $current_option_data['price'] = data['price'];
                        }

                    });

                    productSelect2.trigger('change');

                    setTimeout(() => {
                        $(oc + '_color').val(response.orderMetas[0]?.product_variant?.color || '');
                    }, 200);

                    $( oc + '_fullname' ).val( response.fullname );
                    $( oc + '_company_name' ).val( response.company_name );
                    $( oc + '_phone_number' ).val( response.phone_number );
                    $( oc + '_email' ).val( response.email );
                    $( oc + '_address_1' ).val( response.address_1 );
                    $( oc + '_address_2' ).val( response.address_2 );
                    $( oc + '_city' ).val( response.city );
                    $( oc + '_state' ).val( response.state );
                    $( oc + '_postcode' ).val( response.postcode );
                    $( oc + '_remarks' ).val( response.remarks );
                    $( oc + '_payment_plan' ).val( response.payment_plan );
                    $( oc + '_quantity' ).val( response.orderMetas[0] ? response.orderMetas[0].quantity : 1 );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

    } );
</script>
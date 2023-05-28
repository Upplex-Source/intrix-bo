<?php
$booking_edit = 'booking_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.bookings' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_reference" class="col-sm-5 col-form-label">{{ __( 'booking.reference' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_reference">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_customer_name" class="col-sm-5 col-form-label">{{ __( 'booking.customer_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_customer_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_invoice_number" class="col-sm-5 col-form-label">{{ __( 'booking.invoice_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_invoice_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_invoice_date" class="col-sm-5 col-form-label">{{ __( 'booking.invoice_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_invoice_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_vehicle" class="col-sm-5 col-form-label">{{ __( 'booking.vehicle' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_edit }}_vehicle" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'booking.vehicle' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_delivery_order_number" class="col-sm-5 col-form-label">{{ __( 'booking.delivery_order_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_delivery_order_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_delivery_order_date" class="col-sm-5 col-form-label">{{ __( 'booking.delivery_order_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_delivery_order_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <hr>
                <h5 class="card-title mb-4">{{ __( 'booking.pickup_address' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_pickup_address_address_1" class="col-sm-5 col-form-label">{{ __( 'booking.address_1' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $booking_edit }}_pickup_address_address_1"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_pickup_address_address_2" class="col-sm-5 col-form-label">{{ __( 'booking.address_2' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $booking_edit }}_pickup_address_address_2" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_pickup_address_city" class="col-sm-5 col-form-label">{{ __( 'booking.city' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_pickup_address_city">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_pickup_address_postcode" class="col-sm-5 col-form-label">{{ __( 'booking.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_pickup_address_postcode" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_pickup_address_state" class="col-sm-5 col-form-label">{{ __( 'booking.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_edit }}_pickup_address_state" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'booking.state' ) ] ) }}</option>
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
                <hr>
                <h5 class="card-title mb-4">{{ __( 'booking.dropoff_address' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_dropoff_address_destination" class="col-sm-5 col-form-label">{{ __( 'booking.destination' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_dropoff_address_destination">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_dropoff_address_address_1" class="col-sm-5 col-form-label">{{ __( 'booking.address_1' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $booking_edit }}_dropoff_address_address_1"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_dropoff_address_address_2" class="col-sm-5 col-form-label">{{ __( 'booking.address_2' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $booking_edit }}_dropoff_address_address_2" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_dropoff_address_city" class="col-sm-5 col-form-label">{{ __( 'booking.city' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_dropoff_address_city">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_dropoff_address_postcode" class="col-sm-5 col-form-label">{{ __( 'booking.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_dropoff_address_postcode" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_dropoff_address_state" class="col-sm-5 col-form-label">{{ __( 'booking.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_edit }}_dropoff_address_state" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'booking.state' ) ] ) }}</option>
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
                <hr>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_pickup_date" class="col-sm-5 col-form-label">{{ __( 'booking.pickup_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_pickup_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_dropoff_date" class="col-sm-5 col-form-label">{{ __( 'booking.dropoff_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_dropoff_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <hr>
                <h5 class="card-title mb-4">{{ __( 'booking.customer_amount' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_company" class="col-sm-5 col-form-label">{{ __( 'booking.company' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_edit }}_company" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'booking.company' ) ] ) }}</option>
                            @foreach( $data['company'] as $key => $company )
                            <option value="{{ $company['id'] }}">{{ $company['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_customer_type" class="col-sm-5 col-form-label">{{ __( 'booking.customer_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_edit }}_customer_type" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'booking.customer_type' ) ] ) }}</option>
                            @foreach( $data['type'] as $key => $type )
                            <option value="{{ $key }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_customer_quantity" class="col-sm-5 col-form-label">{{ __( 'booking.quantity' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $booking_edit }}_customer_quantity" value="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_customer_uom" class="col-sm-5 col-form-label">{{ __( 'booking.uom' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_edit }}_customer_uom" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'booking.uom' ) ] ) }}</option>
                            @foreach( $data['uom'] as $key => $uom )
                            <option value="{{ $key }}">{{ $uom }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_customer_rate" class="col-sm-5 col-form-label">{{ __( 'booking.customer_rate' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $booking_edit }}_customer_rate" value="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_customer_total_amount" class="col-sm-5 col-form-label">{{ __( 'booking.total_amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $booking_edit }}_customer_total_amount" value="0.00" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_customer_remarks" class="col-sm-5 col-form-label">{{ __( 'booking.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_edit }}_customer_remarks" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <hr>
                <h5 class="card-title mb-4">{{ __( 'booking.driver_amount' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_driver" class="col-sm-5 col-form-label">{{ __( 'booking.driver' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_edit }}_driver" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'booking.driver' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_driver_quantity" class="col-sm-5 col-form-label">{{ __( 'booking.quantity' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $booking_edit }}_driver_quantity" value="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_driver_uom" class="col-sm-5 col-form-label">{{ __( 'booking.uom' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_edit }}_driver_uom" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'booking.uom' ) ] ) }}</option>
                            @foreach( $data['uom'] as $key => $uom )
                            <option value="{{ $key }}">{{ $uom }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_driver_rate" class="col-sm-5 col-form-label">{{ __( 'booking.driver_rate' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $booking_edit }}_driver_rate" value="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_driver_total_amount" class="col-sm-5 col-form-label">{{ __( 'booking.total_amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $booking_edit }}_driver_total_amount" value="0.00" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_driver_percentage" class="col-sm-5 col-form-label">{{ __( 'booking.percentage' ) }} (%)</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $booking_edit }}_driver_percentage" value="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_edit }}_driver_final_amount" class="col-sm-5 col-form-label">{{ __( 'booking.final_amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $booking_edit }}_driver_final_amount" value="0.00" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $booking_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $booking_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let be = '#{{ $booking_edit }}';

        let invoiceDate = $( be + '_invoice_date' ).flatpickr();
        let deliveryOrderDate = $( be + '_delivery_order_date' ).flatpickr();
        let pickupDate = $( be + '_pickup_date' ).flatpickr( {
            enableTime: true,
        } );
        let dropoffDate = $( be + '_dropoff_date' ).flatpickr( {
            enableTime: true,
        } );

        $( be + '_customer_quantity' ).on( 'keyup change', function() {
            $( be + '_driver_quantity' ).val( $( this ).val() );
            calculateCustomerTotalAmount();
        } );
        $( be + '_customer_rate' ).on( 'keyup change', function() {
            $( be + '_driver_rate' ).val( $( this ).val() );
            calculateCustomerTotalAmount();
        } );
        $( be + '_driver_quantity' ).on( 'keyup change', function() {
            calculateDriverTotalAmount();
        } );
        $( be + '_driver_rate' ).on( 'keyup change', function() {
            calculateDriverTotalAmount();
        } );
        $( be + '_driver_percentage' ).on( 'keyup change', function() {
            calculateDriverTotalAmount();
        } );
        
        $( be + '_customer_uom' ).on( 'change', function() {
            $( be + '_driver_uom' ).val( $( this ).val() );
        } );

        $( be + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.booking.index' ) }}';
        } );

        $( be + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'reference', $( be + '_reference' ).val() );
            formData.append( 'customer_name', $( be + '_customer_name' ).val() );
            formData.append( 'invoice_number', $( be + '_invoice_number' ).val() );
            formData.append( 'invoice_date', $( be + '_invoice_date' ).val() );
            formData.append( 'vehicle', $( be + '_vehicle' ).val() );
            formData.append( 'delivery_order_number', $( be + '_delivery_order_number' ).val() );
            formData.append( 'delivery_order_date', $( be + '_delivery_order_date' ).val() );

            formData.append( 'pickup_address_address_1', $( be + '_pickup_address_address_1' ).val() );
            formData.append( 'pickup_address_address_2', $( be + '_pickup_address_address_2' ).val() );
            formData.append( 'pickup_address_city', $( be + '_pickup_address_city' ).val() );
            formData.append( 'pickup_address_postcode', $( be + '_pickup_address_postcode' ).val() );
            formData.append( 'pickup_address_state', $( be + '_pickup_address_state' ).val() );

            formData.append( 'dropoff_address_destination', $( be + '_dropoff_address_destination' ).val() );
            formData.append( 'dropoff_address_address_1', $( be + '_dropoff_address_address_1' ).val() );
            formData.append( 'dropoff_address_address_2', $( be + '_dropoff_address_address_2' ).val() );
            formData.append( 'dropoff_address_city', $( be + '_dropoff_address_city' ).val() );
            formData.append( 'dropoff_address_postcode', $( be + '_dropoff_address_postcode' ).val() );
            formData.append( 'dropoff_address_state', $( be + '_dropoff_address_state' ).val() );
            
            formData.append( 'pickup_date', $( be + '_pickup_date' ).val() );
            formData.append( 'dropoff_date', $( be + '_dropoff_date' ).val() );

            formData.append( 'company', null === $( be + '_company' ).val() ? '' : $( be + '_company' ).val() );
            formData.append( 'customer_type', $( be + '_customer_type' ).val() );
            formData.append( 'customer_quantity', $( be + '_customer_quantity' ).val() );
            formData.append( 'customer_uom', $( be + '_customer_uom' ).val() );
            formData.append( 'customer_rate', $( be + '_customer_rate' ).val() );
            formData.append( 'customer_total_amount', $( be + '_customer_total_amount' ).val() );
            formData.append( 'customer_remarks', $( be + '_customer_remarks' ).val() );
            
            console.log( $( be + '_company' ).val() );
            console.log( $( be + '_driver' ).val() );

            formData.append( 'driver', null === $( be + '_driver' ).val() ? '' : $( be + '_driver' ).val() );
            formData.append( 'driver_quantity', $( be + '_driver_quantity' ).val() );
            formData.append( 'driver_uom', $( be + '_driver_uom' ).val() );
            formData.append( 'driver_rate', $( be + '_driver_rate' ).val() );
            formData.append( 'driver_total_amount', $( be + '_driver_total_amount' ).val() );
            formData.append( 'driver_percentage', $( be + '_driver_percentage' ).val() );
            formData.append( 'driver_final_amount', $( be + '_driver_final_amount' ).val() );

            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.booking.updateBooking' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.booking.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( be + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                        $( '.form-control.is-invalid:first' ).get( 0 ).scrollIntoView( { block: 'center' } );
                        $( '.form-select.is-invalid:first' ).get( 0 ).scrollIntoView( { block: 'center' } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        let vehicleSelect2 = $( be + '_vehicle' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.vehicle.allVehicles' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        status: 10,
                        start: params.page ? params.page : 0,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.vehicles.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.name + ' (' + v.license_plate + ')',
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

        let driverSelect2 = $( be + '_driver' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.employee.allEmployees' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        designation: 1,
                        status: 10,
                        start: params.page ? params.page : 0,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.employees.map( function( v, i ) {
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

        function calculateCustomerTotalAmount() {

            let customerQuantity = $( be + '_customer_quantity' ).val(),
                customerRate = $( be + '_customer_rate' ).val();

            let customerTotalAmount = parseFloat( customerQuantity ) * parseFloat( customerRate );

            $( be + '_customer_total_amount' ).val( customerTotalAmount.toFixedDown() );

            calculateDriverTotalAmount();
        }

        function calculateDriverTotalAmount() {

            let driverQuantity = $( be + '_driver_quantity' ).val(),
                driverRate = $( be + '_driver_rate' ).val(),
                driverPercentage = $( be + '_driver_percentage' ).val();

            let driverTotalAmount = parseFloat( driverQuantity ) * parseFloat( driverRate );

            $( be + '_driver_total_amount' ).val( driverTotalAmount.toFixedDown() );
            $( be + '_driver_final_amount' ).val( ( driverTotalAmount * parseFloat( driverPercentage ) / 100 ).toFixedDown() );
        }

        getBooking();

        function getBooking() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.booking.oneBooking' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( be + '_reference' ).val( response.reference );
                    $( be + '_customer_name' ).val( response.customer_name );
                    $( be + '_invoice_number' ).val( response.invoice_number );
                    invoiceDate.setDate( response.invoice_date );

                    let option1 = new Option( response.vehicle.name + ' (' + response.vehicle.license_plate + ')', response.vehicle.id, true, true );
                    vehicleSelect2.append( option1 );
                    vehicleSelect2.trigger( 'change' );

                    $( be + '_delivery_order_number' ).val( response.delivery_order_number );
                    deliveryOrderDate.setDate( response.delivery_order_date );

                    $( be + '_pickup_address_address_1' ).val( response.display_pickup_address.a1 );
                    $( be + '_pickup_address_address_2' ).val( response.display_pickup_address.a2 );
                    $( be + '_pickup_address_city' ).val( response.display_pickup_address.c );
                    $( be + '_pickup_address_postcode' ).val( response.display_pickup_address.p );
                    $( be + '_pickup_address_state' ).val( response.display_pickup_address.s );

                    $( be + '_dropoff_address_destination' ).val( response.display_drop_off_address.d );
                    $( be + '_dropoff_address_address_1' ).val( response.display_drop_off_address.a1 );
                    $( be + '_dropoff_address_address_2' ).val( response.display_drop_off_address.a2 );
                    $( be + '_dropoff_address_city' ).val( response.display_drop_off_address.c );
                    $( be + '_dropoff_address_postcode' ).val( response.display_drop_off_address.p );
                    $( be + '_dropoff_address_state' ).val( response.display_drop_off_address.s );

                    pickupDate.setDate( response.pickup_date );
                    dropoffDate.setDate( response.dropoff_date );

                    $( be + '_company' ).val( response.company_id );
                    $( be + '_customer_type' ).val( response.customer_type );
                    $( be + '_customer_quantity' ).val( response.customer_quantity );
                    $( be + '_customer_uom' ).val( response.customer_unit_of_measurement );
                    $( be + '_customer_rate' ).val( response.customer_rate );
                    $( be + '_customer_total_amount' ).val( response.customer_total_amount );
                    $( be + '_customer_remarks' ).val( response.customer_remarks );

                    let option2 = new Option( response.driver.name, response.driver.id, true, true );
                    driverSelect2.append( option2 );
                    driverSelect2.trigger( 'change' );

                    $( be + '_driver_quantity' ).val( response.driver_quantity );
                    $( be + '_driver_uom' ).val( response.driver_unit_of_measurement );
                    $( be + '_driver_rate' ).val( response.driver_rate );
                    $( be + '_driver_total_amount' ).val( response.driver_total_amount );
                    $( be + '_driver_percentage' ).val( response.driver_percentage );
                    $( be + '_driver_final_amount' ).val( response.driver_final_amount );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
    } );
</script>
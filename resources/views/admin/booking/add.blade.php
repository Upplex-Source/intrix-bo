<?php
$booking_create = 'booking_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.bookings' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<?php
$bookingIncrement = $data['booking_increment'];
?>

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_reference" class="col-sm-5 col-form-label">{{ __( 'booking.reference' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_reference" value="{{ date( 'Y/m' ) . ' ' . ( $bookingIncrement + 1 ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_customer_name" class="col-sm-5 col-form-label">{{ __( 'booking.customer_name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_customer_name" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_invoice_number" class="col-sm-5 col-form-label">{{ __( 'booking.invoice_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_invoice_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_invoice_date" class="col-sm-5 col-form-label">{{ __( 'booking.invoice_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_invoice_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_vehicle" class="col-sm-5 col-form-label">{{ __( 'booking.vehicle' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_create }}_vehicle" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'booking.vehicle' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_delivery_order_number" class="col-sm-5 col-form-label">{{ __( 'booking.delivery_order_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_delivery_order_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_delivery_order_date" class="col-sm-5 col-form-label">{{ __( 'booking.delivery_order_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_delivery_order_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'booking.delivery_order_image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $booking_create }}_delivery_order_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <hr>
                <h5 class="card-title mb-4">{{ __( 'booking.pickup_address' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_pickup_address_address_1" class="col-sm-5 col-form-label">{{ __( 'booking.address_1' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $booking_create }}_pickup_address_address_1" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_pickup_address_address_2" class="col-sm-5 col-form-label">{{ __( 'booking.address_2' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $booking_create }}_pickup_address_address_2" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_pickup_address_postcode" class="col-sm-5 col-form-label">{{ __( 'booking.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_pickup_address_postcode" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_pickup_address_state" class="col-sm-5 col-form-label">{{ __( 'booking.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_create }}_pickup_address_state" >
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
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_pickup_address_city" class="col-sm-5 col-form-label">{{ __( 'booking.city' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_pickup_address_city" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <hr>
                <h5 class="card-title mb-4">{{ __( 'booking.dropoff_address' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_dropoff_address_destination" class="col-sm-5 col-form-label">{{ __( 'booking.destination' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_dropoff_address_destination" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_dropoff_address_address_1" class="col-sm-5 col-form-label">{{ __( 'booking.address_1' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $booking_create }}_dropoff_address_address_1" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_dropoff_address_address_2" class="col-sm-5 col-form-label">{{ __( 'booking.address_2' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $booking_create }}_dropoff_address_address_2" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_dropoff_address_postcode" class="col-sm-5 col-form-label">{{ __( 'booking.postcode' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_dropoff_address_postcode" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_dropoff_address_state" class="col-sm-5 col-form-label">{{ __( 'booking.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_create }}_dropoff_address_state" >
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
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_dropoff_address_city" class="col-sm-5 col-form-label">{{ __( 'booking.city' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_dropoff_address_city" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <hr>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_pickup_date" class="col-sm-5 col-form-label">{{ __( 'booking.pickup_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_pickup_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_dropoff_date" class="col-sm-5 col-form-label">{{ __( 'booking.dropoff_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_dropoff_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <hr>
                <h5 class="card-title mb-4">{{ __( 'booking.customer_amount' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_company" class="col-sm-5 col-form-label">{{ __( 'booking.company' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_create }}_company" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'booking.company' ) ] ) }}</option>
                            @foreach( $data['company'] as $key => $company )
                            <option value="{{ $company['id'] }}">{{ $company['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_customer_type" class="col-sm-5 col-form-label">{{ __( 'booking.customer_type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_create }}_customer_type" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'booking.customer_type' ) ] ) }}</option>
                            @foreach( $data['type'] as $key => $type )
                            <option value="{{ $key }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_customer_quantity" class="col-sm-5 col-form-label">{{ __( 'booking.quantity' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $booking_create }}_customer_quantity" value="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_customer_uom" class="col-sm-5 col-form-label">{{ __( 'booking.uom' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_create }}_customer_uom" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'booking.uom' ) ] ) }}</option>
                            @foreach( $data['uom'] as $key => $uom )
                            <option value="{{ $key }}">{{ $uom }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_customer_rate" class="col-sm-5 col-form-label">{{ __( 'booking.customer_rate' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $booking_create }}_customer_rate" value="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_customer_total_amount" class="col-sm-5 col-form-label">{{ __( 'booking.total_amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $booking_create }}_customer_total_amount" value="0.00" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_customer_remarks" class="col-sm-5 col-form-label">{{ __( 'booking.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $booking_create }}_customer_remarks" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <hr>
                <h5 class="card-title mb-4">{{ __( 'booking.driver_amount' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_driver" class="col-sm-5 col-form-label">{{ __( 'booking.driver' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_create }}_driver" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'booking.driver' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_driver_quantity" class="col-sm-5 col-form-label">{{ __( 'booking.quantity' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $booking_create }}_driver_quantity" value="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_driver_uom" class="col-sm-5 col-form-label">{{ __( 'booking.uom' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $booking_create }}_driver_uom" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'booking.uom' ) ] ) }}</option>
                            @foreach( $data['uom'] as $key => $uom )
                            <option value="{{ $key }}">{{ $uom }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_driver_rate" class="col-sm-5 col-form-label">{{ __( 'booking.driver_rate' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $booking_create }}_driver_rate" value="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_driver_total_amount" class="col-sm-5 col-form-label">{{ __( 'booking.total_amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $booking_create }}_driver_total_amount" value="0.00" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_driver_percentage" class="col-sm-5 col-form-label">{{ __( 'booking.percentage' ) }} (%)</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $booking_create }}_driver_percentage" value="0">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $booking_create }}_driver_final_amount" class="col-sm-5 col-form-label">{{ __( 'booking.final_amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $booking_create }}_driver_final_amount" value="0.00" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $booking_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $booking_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let bc = '#{{ $booking_create }}',
            fileID = '';

        $( bc + '_invoice_date' ).flatpickr();
        $( bc + '_delivery_order_date' ).flatpickr();
        $( bc + '_pickup_date' ).flatpickr( {
            enableTime: true,
        } );
        $( bc + '_dropoff_date' ).flatpickr( {
            enableTime: true,
        } );

        $( bc + '_customer_quantity' ).on( 'keyup change', function() {

            if ( isNaN( $( this ).val() ) ) {
                return 0;
            }

            if ( $( this ).val() == '' ) {
                $( this ).val( 0 );
            }

            $( bc + '_driver_quantity' ).val( $( this ).val() );
            calculateCustomerTotalAmount();
        } );
        $( bc + '_customer_rate' ).on( 'keyup change', function() {

            if ( isNaN( $( this ).val() ) ) {
                return 0;
            }

            if ( $( this ).val() == '' ) {
                $( this ).val( 0 );
            }

            $( bc + '_driver_rate' ).val( $( this ).val() );
            calculateCustomerTotalAmount();
        } );
        $( bc + '_driver_quantity' ).on( 'keyup change', function() {

            if ( isNaN( $( this ).val() ) ) {
                return 0;
            }

            if ( $( this ).val() == '' ) {
                $( this ).val( 0 );
            }

            calculateDriverTotalAmount();
        } );
        $( bc + '_driver_rate' ).on( 'keyup change', function() {

            if ( isNaN( $( this ).val() ) ) {
                return 0;
            }

            if ( $( this ).val() == '' ) {
                $( this ).val( 0 );
            }

            calculateDriverTotalAmount();
        } );
        $( bc + '_driver_percentage' ).on( 'keyup change', function() {

            if ( isNaN( $( this ).val() ) ) {
                return 0;
            }

            if ( $( this ).val() == '' ) {
                $( this ).val( 0 );
            }

            calculateDriverTotalAmount();
        } );
        
        $( bc + '_customer_uom' ).on( 'change', function() {
            $( bc + '_driver_uom' ).val( $( this ).val() );
        } );

        $( bc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.booking.index' ) }}';
        } );

        $( bc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'reference', $( bc + '_reference' ).val() );
            formData.append( 'customer_name', $( bc + '_customer_name' ).val() );
            formData.append( 'invoice_number', $( bc + '_invoice_number' ).val() );
            formData.append( 'invoice_date', $( bc + '_invoice_date' ).val() );
            formData.append( 'vehicle', null === $( bc + '_vehicle' ).val() ? '' : $( bc + '_vehicle' ).val() );
            formData.append( 'delivery_order_number', $( bc + '_delivery_order_number' ).val() );
            formData.append( 'delivery_order_date', $( bc + '_delivery_order_date' ).val() );
            formData.append( 'delivery_order_image', fileID );

            formData.append( 'pickup_address_address_1', $( bc + '_pickup_address_address_1' ).val() );
            formData.append( 'pickup_address_address_2', $( bc + '_pickup_address_address_2' ).val() );
            formData.append( 'pickup_address_city', $( bc + '_pickup_address_city' ).val() );
            formData.append( 'pickup_address_postcode', $( bc + '_pickup_address_postcode' ).val() );
            formData.append( 'pickup_address_state', $( bc + '_pickup_address_state' ).val() );

            formData.append( 'dropoff_address_destination', $( bc + '_dropoff_address_destination' ).val() );
            formData.append( 'dropoff_address_address_1', $( bc + '_dropoff_address_address_1' ).val() );
            formData.append( 'dropoff_address_address_2', $( bc + '_dropoff_address_address_2' ).val() );
            formData.append( 'dropoff_address_city', $( bc + '_dropoff_address_city' ).val() );
            formData.append( 'dropoff_address_postcode', $( bc + '_dropoff_address_postcode' ).val() );
            formData.append( 'dropoff_address_state', $( bc + '_dropoff_address_state' ).val() );
            
            formData.append( 'pickup_date', $( bc + '_pickup_date' ).val() );
            formData.append( 'dropoff_date', $( bc + '_dropoff_date' ).val() );

            formData.append( 'company', $( bc + '_company' ).val() );
            formData.append( 'customer_type', $( bc + '_customer_type' ).val() );
            formData.append( 'customer_quantity', $( bc + '_customer_quantity' ).val() );
            formData.append( 'customer_uom', $( bc + '_customer_uom' ).val() );
            formData.append( 'customer_rate', $( bc + '_customer_rate' ).val() );
            formData.append( 'customer_total_amount', $( bc + '_customer_total_amount' ).val() );
            formData.append( 'customer_remarks', $( bc + '_customer_remarks' ).val() );
            
            console.log( $( bc + '_company' ).val() );
            console.log( $( bc + '_driver' ).val() );

            formData.append( 'driver', null === $( bc + '_driver' ).val() ? '' : $( bc + '_driver' ).val() );
            formData.append( 'driver_quantity', $( bc + '_driver_quantity' ).val() );
            formData.append( 'driver_uom', $( bc + '_driver_uom' ).val() );
            formData.append( 'driver_rate', $( bc + '_driver_rate' ).val() );
            formData.append( 'driver_total_amount', $( bc + '_driver_total_amount' ).val() );
            formData.append( 'driver_percentage', $( bc + '_driver_percentage' ).val() );
            formData.append( 'driver_final_amount', $( bc + '_driver_final_amount' ).val() );

            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.booking.createBooking' ) }}',
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
                            $( bc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
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

        $( bc + '_vehicle' ).select2( {
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

        $( bc + '_driver' ).select2( {
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

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( bc + '_delivery_order_image', { 
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            removedfile: function( file ) {
                fileID = null;
                file.previewElement.remove();
            },
            success: function( file, response ) {
                console.log( file );
                console.log( response );
                if ( response.status == 200 )  {
                    fileID = response.data.id;
                }
            }
        } );

        function calculateCustomerTotalAmount() {

            let customerQuantity = $( bc + '_customer_quantity' ).val(),
                customerRate = $( bc + '_customer_rate' ).val();

            let customerTotalAmount = parseFloat( customerQuantity ) * parseFloat( customerRate );

            $( bc + '_customer_total_amount' ).val( customerTotalAmount.toFixedDown() );

            calculateDriverTotalAmount();
        }

        function calculateDriverTotalAmount() {

            let driverQuantity = $( bc + '_driver_quantity' ).val(),
                driverRate = $( bc + '_driver_rate' ).val(),
                driverPercentage = $( bc + '_driver_percentage' ).val();

            let driverTotalAmount = parseFloat( driverQuantity ) * parseFloat( driverRate );

            $( bc + '_driver_total_amount' ).val( driverTotalAmount.toFixedDown() );
            $( bc + '_driver_final_amount' ).val( ( driverTotalAmount * parseFloat( driverPercentage ) / 100 ).toFixedDown() );
        }
    } );
</script>
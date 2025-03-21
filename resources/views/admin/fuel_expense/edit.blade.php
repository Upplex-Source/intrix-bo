<?php
$fuel_expense_edit = 'fuel_expense_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.fuel_expenses' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $fuel_expense_edit }}_station" class="col-sm-5 col-form-label">{{ __( 'expenses.station' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $fuel_expense_edit }}_station" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'expenses.station' ) ] ) }}</option>
                            @foreach( $data['station'] as $key => $station )
                            <option value="{{ $key }}">{{ $station }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $fuel_expense_edit }}_location" class="col-sm-5 col-form-label">{{ __( 'expenses.location' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $fuel_expense_edit }}_location">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $fuel_expense_edit }}_company" class="col-sm-5 col-form-label">{{ __( 'expenses.company' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $fuel_expense_edit }}_company" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'expenses.company' ) ] ) }}</option>
                            @foreach( $data['company'] as $key => $company )
                            <option value="{{ $company['id'] }}">{{ $company['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $fuel_expense_edit }}_vehicle" class="col-sm-5 col-form-label">{{ __( 'expenses.vehicle' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $fuel_expense_edit }}_vehicle" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'expenses.vehicle' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $fuel_expense_edit }}_amount" class="col-sm-5 col-form-label">{{ __( 'expenses.amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $fuel_expense_edit }}_amount">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $fuel_expense_edit }}_transaction_time" class="col-sm-5 col-form-label">{{ __( 'datatables.transaction_time' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $fuel_expense_edit }}_transaction_time">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $fuel_expense_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $fuel_expense_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fee = '#{{ $fuel_expense_edit }}';

        let transactionTime = $( fee + '_transaction_time' ).flatpickr( {
            enableTime: true,
            enableSeconds:true,
        } );

        $( fee + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.fuel_expense.index' ) }}';
        } );

        $( fee + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'station', $( fee + '_station' ).val() );
            formData.append( 'location', $( fee + '_location' ).val() );
            formData.append( 'company', null === $( fee + '_company' ).val() ? '' : $( fee + '_company' ).val() );
            formData.append( 'vehicle', null === $( fee + '_vehicle' ).val() ? '' : $( fee + '_vehicle' ).val() );
            formData.append( 'amount', $( fee + '_amount' ).val() );
            formData.append( 'transaction_time', $( fee + '_transaction_time' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
            url: '{{ route( 'admin.fuel_expense.updateFuelExpense' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.fuel_expense.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( fee + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                        // $( '.form-control.is-invalid:first' ).get( 0 ).scrollIntoView( { block: 'center' } );
                        // $( '.form-select.is-invalid:first' ).get( 0 ).scrollIntoView( { block: 'center' } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        let vehicleSelect2 = $( fee + '_vehicle' ).select2( {
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
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
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
                            text: v.license_plate,
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

        getFuelExpense();

        function getFuelExpense() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.fuel_expense.oneFuelExpense' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    let option = new Option( response.vehicle.license_plate, response.vehicle.id, true, true );
                    vehicleSelect2.append( option );
                    vehicleSelect2.trigger( 'change' );

                    $( fee + '_station' ).val( response.station );
                    $( fee + '_location' ).val( response.location );
                    $( fee + '_company' ).val( response.company_id );
                    $( fee + '_amount' ).val( response.amount );
                    transactionTime.setDate( response.local_transaction_time );

                    $( 'body' ).loading( 'stop' );
                }
            } );
        }
    } );
</script>
<?php
$service_reminder_edit = 'service_reminder_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.service_reminders' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $service_reminder_edit }}_vehicle" class="col-sm-5 col-form-label">{{ __( 'service.vehicle' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $service_reminder_edit }}_vehicle">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'service.vehicle' ) ] ) }}</option>
                            @foreach( $data['vehicles'] as $vehicle )
                            <option value="{{ $vehicle['id'] }}">{{ $vehicle['name'] }} ({{ $vehicle['license_plate'] }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_reminder_edit }}_service" class="col-sm-5 col-form-label">{{ __( 'service.service' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $service_reminder_edit }}_service">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'service.service' ) ] ) }}</option>
                            @foreach( $data['services'] as $service )
                            <option value="{{ $service['encrypted_id'] }}">{{ $service['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_reminder_edit }}_service_date" class="col-sm-5 col-form-label">{{ __( 'service.service_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $service_reminder_edit }}_service_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_reminder_edit }}_due_date" class="col-sm-5 col-form-label">{{ __( 'service.due_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $service_reminder_edit }}_due_date" disabled>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $service_reminder_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $service_reminder_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let sre = '#{{ $service_reminder_edit }}';

        let serviceDate = $( sre + '_service_date' ).flatpickr( {
            onClose: function( selected, dateStr, instance ) {
                // let service = JSON.parse( $( sre + '_service' ).val() );
                // console.log( service );
                calculateDate()
            }
        } );
        let dueDate = $( sre + '_due_date' ).flatpickr();

        $( sre + '_service' ).on( 'change', function() {
            // let service = JSON.parse( $( sre + '_service' ).val() );
            calculateDate()
        } );

        function calculateDate() {

            if ( $( sre + '_service_date' ).val() == '' || $( sre + '_service' ).val() == '' ) {
                return 0;
            }

            $.ajax( {
                url: '{{ route( 'admin.service.oneService' ) }}',
                type: 'POST',
                data: {
                    'id': $( sre + '_service' ).val(),
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    let date = new Date( $( sre + '_service_date' ).val() );
                    date.setDate( date.getDate() + response.service_interval );
                    dueDate.setDate( date );
                }
            } );
        }

        $( sre + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.service_reminder.index' ) }}';
        } );

        $( sre + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'vehicle', $( sre + '_vehicle' ).val() );
            formData.append( 'service', $( sre + '_service' ).val() );
            formData.append( 'service_date', $( sre + '_service_date' ).val() );
            formData.append( 'due_date', $( sre + '_due_date' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.service_reminder.updateServiceReminder' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.service_reminder.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( sre + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getServiceReminder();

        function getServiceReminder() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.service_reminder.oneServiceReminder' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( sre + '_vehicle' ).val( response.vehicle_id );
                    $( sre + '_service' ).val( response.service.encrypted_id );
                    serviceDate.setDate( response.service_date );
                    // dueDate.setDate( response.due_date );

                    calculateDate();

                    $( 'body' ).loading( 'stop' );
                }
            } );
        }
    } );
</script>
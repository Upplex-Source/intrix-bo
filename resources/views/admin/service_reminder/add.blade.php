<?php
$service_reminder_create = 'service_reminder_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.service_reminders' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $service_reminder_create }}_vehicle" class="col-sm-5 col-form-label">{{ __( 'service.vehicle' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $service_reminder_create }}_vehicle">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'service.vehicle' ) ] ) }}</option>
                            @foreach( $data['vehicles'] as $vehicle )
                            <option value="{{ $vehicle['id'] }}">{{ $vehicle['name'] }} ({{ $vehicle['license_plate'] }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_reminder_create }}_service" class="col-sm-5 col-form-label">{{ __( 'service.service' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $service_reminder_create }}_service">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'service.service' ) ] ) }}</option>
                            @foreach( $data['services'] as $service )
                            <option value="{{ json_encode( $service ) }}">{{ $service['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_reminder_create }}_service_date" class="col-sm-5 col-form-label">{{ __( 'service.service_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $service_reminder_create }}_service_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_reminder_create }}_due_date" class="col-sm-5 col-form-label">{{ __( 'service.due_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control-plaintext" id="{{ $service_reminder_create }}_due_date" disabled>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $service_reminder_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $service_reminder_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let src = '#{{ $service_reminder_create }}';

        $( src + '_service_date' ).flatpickr( {
            onClose: function( selected, dateStr, instance ) {
                let service = JSON.parse( $( src + '_service' ).val() );
                calculateDate( service )
            }
        } );
        let dueDate = $( src + '_due_date' ).flatpickr();

        $( src + '_service' ).on( 'change', function() {
            let service = JSON.parse( $( src + '_service' ).val() );
            calculateDate( service )
        } );

        function calculateDate( service ) {

            if ( $( src + '_service_date' ).val() == '' || $( src + '_service' ).val() == '' ) {
                return 0;
            }

            let date = new Date( $( src + '_service_date' ).val() );
            date.setDate( date.getDate() + service.service_interval );
            dueDate.setDate( date );
        }

        $( src + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.service_reminder.index' ) }}';
        } );

        $( src + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let serviceID = $( src + '_service' ).val() == '' ? '' : JSON.parse( $( src + '_service' ).val() ).id;

            let formData = new FormData();
            formData.append( 'vehicle', $( src + '_vehicle' ).val() );
            formData.append( 'service', serviceID );
            formData.append( 'service_date', $( src + '_service_date' ).val() );
            formData.append( 'due_date', $( src + '_due_date' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.service_reminder.createServiceReminder' ) }}',
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
                            $( src + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );
    } );
</script>
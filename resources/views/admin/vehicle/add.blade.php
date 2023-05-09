<?php
$vehicle_create = 'vehicle_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.vehicles' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <div class="mb-3">
                    <label>{{ __( 'datatables.photo' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $vehicle_create }}_photo" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_maker" class="col-sm-5 col-form-label">{{ __( 'vehicle.maker' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_maker">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_model" class="col-sm-5 col-form-label">{{ __( 'vehicle.model' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_model">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_color" class="col-sm-5 col-form-label">{{ __( 'vehicle.color' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_color">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_license_plate" class="col-sm-5 col-form-label">{{ __( 'vehicle.license_plate' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_license_plate">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_type" class="col-sm-5 col-form-label">{{ __( 'vehicle.type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vehicle_create }}_type">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'vehicle.type' ) ] ) }}</option>
                            <option value="1">{{ __( 'vehicle.parts' ) }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_in_service" class="col-sm-5 col-form-label">{{ __( 'vehicle.in_service' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vehicle_create }}_in_service">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'vehicle.in_service' ) ] ) }}</option>
                            <option value="0">{{ __( 'datatables.no' ) }}</option>
                            <option value="1">{{ __( 'datatables.yes' ) }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $vehicle_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $vehicle_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let vc = '#{{ $vehicle_create }}',
            fileID = '';
            
        $( vc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.vehicle.index' ) }}';
        } );

        $( vc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'photo', fileID );
            formData.append( 'maker', $( vc + '_maker' ).val() );
            formData.append( 'model', $( vc + '_model' ).val() );
            formData.append( 'color', $( vc + '_color' ).val() );
            formData.append( 'license_plate', $( vc + '_license_plate' ).val() );
            formData.append( 'in_service', $( vc + '_in_service' ).val() );
            formData.append( 'type', $( vc + '_type' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.vehicle.createVehicle' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.vehicle.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( vc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( vc + '_photo', { 
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            removedfile: function( file ) {
                fileID = null;
                file.previewElement.remove();
            },
            success: function( file, response ) {
                if ( response.status == 200 )  {
                    fileID = response.data.id;
                }
            }
        } );
    } );
</script>
<?php
$vehicle_edit = 'vehicle_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.vehicles' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <div class="mb-3">
                    <label>{{ __( 'datatables.photo' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $vehicle_edit }}_photo" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_maker" class="col-sm-5 col-form-label">{{ __( 'vehicle.maker' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_maker">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_model" class="col-sm-5 col-form-label">{{ __( 'vehicle.model' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_model">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_color" class="col-sm-5 col-form-label">{{ __( 'vehicle.color' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_color">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_license_plate" class="col-sm-5 col-form-label">{{ __( 'vehicle.license_plate' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_license_plate">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_type" class="col-sm-5 col-form-label">{{ __( 'vehicle.type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vehicle_edit }}_type">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'vehicle.type' ) ] ) }}</option>
                            <option value="1">{{ __( 'vehicle.parts' ) }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_in_service" class="col-sm-5 col-form-label">{{ __( 'vehicle.in_service' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vehicle_edit }}_in_service">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'vehicle.in_service' ) ] ) }}</option>
                            <option value="0">{{ __( 'datatables.no' ) }}</option>
                            <option value="1">{{ __( 'datatables.yes' ) }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $vehicle_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $vehicle_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {
        
        getVehicle();

        let ve = '#{{ $vehicle_edit }}',
            fileID = '';

        $( ve + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.vendor.index' ) }}';
        } );

        $( ve + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );
        } );

        $( ve + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ Helper::decode( request( 'id' ) ) }}' );
            if ( fileID ) {
                formData.append( 'photo', fileID );
            }
            formData.append( 'maker', $( ve + '_maker' ).val() );
            formData.append( 'model', $( ve + '_model' ).val() );
            formData.append( 'color', $( ve + '_color' ).val() );
            formData.append( 'license_plate', $( ve + '_license_plate' ).val() );
            formData.append( 'type', $( ve + '_type' ).val() );
            formData.append( 'in_service', $( ve + '_in_service' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.vehicle.updateVehicle' ) }}',
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
                            $( ve + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        function getVehicle() {
            
            Dropzone.autoDiscover = false;

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.vehicle.oneVehicle' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( ve + '_maker' ).val( response.maker );
                    $( ve + '_model' ).val( response.model );
                    $( ve + '_color' ).val( response.color );
                    $( ve + '_license_plate' ).val( response.license_plate );
                    $( ve + '_type' ).val( response.type );
                    $( ve + '_in_service' ).val( response.in_service );

                    fileID = response.photo;

                    let imagePath = response.path;

                    const dropzone = new Dropzone( ve + '_photo', {
                        url: '{{ route( 'admin.file.upload' ) }}',
                        maxFiles: 1,
                        acceptedFiles: 'image/jpg,image/jpeg,image/png',
                        addRemoveLinks: true,
                        init: function() {
                            if ( imagePath ) {
                                let myDropzone = this,
                                    mockFile = { name: 'Default', size: 1024 };

                                myDropzone.displayExistingFile( mockFile, imagePath );
                            }
                        },
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

                    $( 'body' ).loading( 'stop' );
                }
            } );
        }
    } );
</script>
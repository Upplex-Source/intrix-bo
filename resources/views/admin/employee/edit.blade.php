<?php
$employee_edit = 'employee_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.employees' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3">
                    <label>{{ __( 'datatables.photo' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $employee_edit }}_photo" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $employee_edit }}_name" class="col-sm-5 col-form-label">{{ __( 'employee.name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $employee_edit }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $employee_edit }}_email" class="col-sm-5 col-form-label">{{ __( 'employee.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $employee_edit }}_email">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $employee_edit }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'employee.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $employee_edit }}_phone_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $employee_edit }}_identification_number" class="col-sm-5 col-form-label">{{ __( 'employee.identification_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $employee_edit }}_identification_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $employee_edit }}_license_number" class="col-sm-5 col-form-label">{{ __( 'employee.license_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $employee_edit }}_license_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $employee_edit }}_license_expiry_date" class="col-sm-5 col-form-label">{{ __( 'employee.license_expiry_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $employee_edit }}_license_expiry_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $employee_edit }}_designation" class="col-sm-5 col-form-label">{{ __( 'employee.designation' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $employee_edit }}_designation">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'employee.designation' ) ] ) }}</option>
                            @foreach( $data['designation'] as $key => $designation )
                            <option value="{{ $key }}">{{ $designation }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $employee_edit }}_remarks" class="col-sm-5 col-form-label">{{ __( 'employee.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $employee_edit }}_remarks" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $employee_edit }}_driver_amount" class="col-sm-5 col-form-label">{{ __( 'employee.driver_amount' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $employee_edit }}_driver_amount" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $employee_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $employee_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let de = '#{{ $employee_edit }}',
            fileID = '';

        let expiryDate = $( de + '_license_expiry_date' ).flatpickr( {
            disableMobile: true,
        } );

        $( de + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.employee.index' ) }}';
        } );

        $( de + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            if ( fileID ) {
                formData.append( 'photo', fileID );
            }
            formData.append( 'name', $( de + '_name' ).val() );
            formData.append( 'email', $( de + '_email' ).val() );
            formData.append( 'phone_number', $( de + '_phone_number' ).val() );
            formData.append( 'identification_number', $( de + '_identification_number' ).val() );
            formData.append( 'license_number', $( de + '_license_number' ).val() );
            formData.append( 'license_expiry_date', $( de + '_license_expiry_date' ).val() );
            formData.append( 'designation', $( de + '_designation' ).val() );
            formData.append( 'remarks', $( de + '_remarks' ).val() );
            formData.append( 'driver_amount', $( de + '_driver_amount' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.employee.updateEmployee' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.employee.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( de + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getEmployee();

        function getEmployee() {

            Dropzone.autoDiscover = false;

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );
            
            $.ajax( {
                url: '{{ route( 'admin.employee.oneEmployee' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( de + '_name' ).val( response.name );
                    $( de + '_email' ).val( response.email );
                    $( de + '_phone_number' ).val( response.phone_number );
                    $( de + '_identification_number' ).val( response.identification_number );
                    $( de + '_license_number' ).val( response.license_number );
                    expiryDate.setDate( response.license_expiry_date );
                    $( de + '_designation' ).val( response.designation );
                    $( de + '_remarks' ).val( response.remarks );
                    $( de + '_driver_amount' ).val( response.driver_amount );

                    fileID = response.photo;

                    let imagePath = response.path;

                    const dropzone = new Dropzone( de + '_photo', {
                        url: '{{ route( 'admin.file.upload' ) }}',
                        maxFiles: 1,
                        acceptedFiles: 'image/jpg,image/jpeg,image/png',
                        addRemoveLinks: true,
                        init: function() {
                            if ( imagePath ) {
                                let myDropzone = this,
                                    mockFile = { name: 'Default', size: 1024, accepted: true };

                                myDropzone.files.push( mockFile );
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
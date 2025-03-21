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
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
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
                    <label for="{{ $vehicle_edit }}_driver" class="col-sm-5 col-form-label">{{ __( 'vehicle.driver' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vehicle_edit }}_driver" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'vehicle.driver' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_company" class="col-sm-5 col-form-label">{{ __( 'vehicle.company' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vehicle_edit }}_company" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'vehicle.company' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_name" class="col-sm-5 col-form-label">{{ __( 'vehicle.model' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_name">
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
                    <label for="{{ $vehicle_edit }}_trailer_number" class="col-sm-5 col-form-label">{{ __( 'vehicle.trailer_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_trailer_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_road_tax_number" class="col-sm-5 col-form-label">{{ __( 'vehicle.road_tax_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_road_tax_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_road_tax_expiry_date" class="col-sm-5 col-form-label">{{ __( 'vehicle.road_tax_expiry_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_road_tax_expiry_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_insurance_number" class="col-sm-5 col-form-label">{{ __( 'vehicle.insurance_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_insurance_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_insurance_start_date" class="col-sm-5 col-form-label">{{ __( 'vehicle.insurance_start_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_insurance_start_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_insurance_expiry_date" class="col-sm-5 col-form-label">{{ __( 'vehicle.insurance_expiry_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_insurance_expiry_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @if ( 1 == 2 )
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_permit_number" class="col-sm-5 col-form-label">{{ __( 'vehicle.permit_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_permit_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @endif
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_permit" class="col-sm-5 col-form-label">{{ __( 'vehicle.permit' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vehicle_edit }}_permit">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'vehicle.permit' ) ] ) }}</option>
                            <option value="1">A</option>
                            <option value="2">C</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_permit_start_date" class="col-sm-5 col-form-label">{{ __( 'vehicle.permit_start_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_permit_start_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_permit_expiry_date" class="col-sm-5 col-form-label">{{ __( 'vehicle.permit_expiry_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_permit_expiry_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_inspection_expiry_date" class="col-sm-5 col-form-label">{{ __( 'vehicle.inspection_expiry_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_inspection_expiry_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_tngsn" class="col-sm-5 col-form-label">{{ __( 'vehicle.tngsn' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_edit }}_tngsn" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @if ( 1 == 2 )
                <div class="mb-3 row">
                    <label for="{{ $vehicle_edit }}_type" class="col-sm-5 col-form-label">{{ __( 'vehicle.type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vehicle_edit }}_type">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'vehicle.type' ) ] ) }}</option>
                            @foreach( $data['type'] as $key => $type )
                            <option value="{{ $key }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @endif
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

        let ve = '#{{ $vehicle_edit }}',
            fileID = '';

        let roadTaxExpiryDate = $( ve + '_road_tax_expiry_date' ).flatpickr( {
            disableMobile: true,
        } );

        let insuranceStartDate = $( ve + '_insurance_start_date' ).flatpickr( {
            disableMobile: true,
        } );

        let insuranceExpiryDate = $( ve + '_insurance_expiry_date' ).flatpickr( {
            disableMobile: true,
        } );

        let permitStartDate = $( ve + '_permit_start_date' ).flatpickr( {
            disableMobile: true,
        } );

        let permitExpiryDate = $( ve + '_permit_expiry_date' ).flatpickr( {
            disableMobile: true,
        } );

        let inspectionExpiryDate = $( ve + '_inspection_expiry_date' ).flatpickr( {
            disableMobile: true,
        } );
        
        $( ve + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.vehicle.index' ) }}';
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
            formData.append( 'id', '{{ request( 'id' ) }}' );
            if ( fileID ) {
                formData.append( 'photo', fileID );
            }
            formData.append( 'driver', $( ve + '_driver' ).val() );
            formData.append( 'company', $( ve + '_company' ).val() );
            formData.append( 'name', $( ve + '_name' ).val() );
            formData.append( 'license_plate', $( ve + '_license_plate' ).val() );
            formData.append( 'trailer_number', $( ve + '_trailer_number' ).val() );
            formData.append( 'road_tax_number', $( ve + '_road_tax_number' ).val() );
            formData.append( 'road_tax_expiry_date', $( ve + '_road_tax_expiry_date' ).val() );
            formData.append( 'insurance_number', $( ve + '_insurance_number' ).val() );
            formData.append( 'insurance_start_date', $( ve + '_insurance_start_date' ).val() );
            formData.append( 'insurance_expiry_date', $( ve + '_insurance_expiry_date' ).val() );
            formData.append( 'tngsn', $( ve + '_tngsn' ).val() );
            formData.append( 'permit_number', $( ve + '_permit_number' ).val() );
            formData.append( 'permit', $( ve + '_permit' ).val() );
            formData.append( 'permit_start_date', $( ve + '_permit_start_date' ).val() );
            formData.append( 'permit_expiry_date', $( ve + '_permit_expiry_date' ).val() );
            formData.append( 'inspection_expiry_date', $( ve + '_inspection_expiry_date' ).val() );
            formData.append( 'type', $( ve + '_type' ).val() );
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

        let driverSelect2 = $( ve + '_driver' ).select2( {
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
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
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

        let companySelect2 = $( ve + '_company' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.company.allCompanies' ) }}',
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

                    data.companies.map( function( v, i ) {
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

        getVehicle();

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

                    let option = new Option( response.employee.name, response.employee.id, true, true );
                    driverSelect2.append( option );
                    driverSelect2.trigger( 'change' );

                    let option2 = new Option( response.company.name, response.company.id, true, true );
                    companySelect2.append( option2 );
                    companySelect2.trigger( 'change' );

                    $( ve + '_name' ).val( response.name )
                    $( ve + '_license_plate' ).val( response.license_plate );
                    $( ve + '_trailer_number' ).val( response.trailer_number );
                    $( ve + '_road_tax_number' ).val( response.road_tax_number );
                    roadTaxExpiryDate.setDate( response.local_road_tax_expiry_date );
                    $( ve + '_insurance_number' ).val( response.insurance_number );
                    insuranceStartDate.setDate( response.local_insurance_start_date );
                    insuranceExpiryDate.setDate( response.local_insurance_expiry_date );
                    $( ve + '_permit_number' ).val( response.permit_number );
                    $( ve + '_tngsn' ).val( response.tngsn );
                    $( ve + '_permit' ).val( response.permit_type );
                    permitStartDate.setDate( response.local_permit_start_date );
                    permitExpiryDate.setDate( response.local_permit_expiry_date );
                    inspectionExpiryDate.setDate( response.local_inspection_expiry_date );
                    $( ve + '_type' ).val( response.type );

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
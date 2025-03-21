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
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
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
                    <label for="{{ $vehicle_create }}_driver" class="col-sm-5 col-form-label">{{ __( 'vehicle.driver' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vehicle_create }}_driver" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'vehicle.driver' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_company" class="col-sm-5 col-form-label">{{ __( 'vehicle.company' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vehicle_create }}_company" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'vehicle.company' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_name" class="col-sm-5 col-form-label">{{ __( 'vehicle.model' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_name">
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
                    <label for="{{ $vehicle_create }}_trailer_number" class="col-sm-5 col-form-label">{{ __( 'vehicle.trailer_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_trailer_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_road_tax_number" class="col-sm-5 col-form-label">{{ __( 'vehicle.road_tax_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_road_tax_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_road_tax_expiry_date" class="col-sm-5 col-form-label">{{ __( 'vehicle.road_tax_expiry_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_road_tax_expiry_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_insurance_number" class="col-sm-5 col-form-label">{{ __( 'vehicle.insurance_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_insurance_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_insurance_start_date" class="col-sm-5 col-form-label">{{ __( 'vehicle.insurance_start_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_insurance_start_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_insurance_expiry_date" class="col-sm-5 col-form-label">{{ __( 'vehicle.insurance_expiry_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_insurance_expiry_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @if ( 1 == 2 )
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_permit_number" class="col-sm-5 col-form-label">{{ __( 'vehicle.permit_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_permit_number" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @endif
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_permit" class="col-sm-5 col-form-label">{{ __( 'vehicle.permit' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vehicle_create }}_permit">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'vehicle.permit' ) ] ) }}</option>
                            <option value="1">A</option>
                            <option value="2">C</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_permit_start_date" class="col-sm-5 col-form-label">{{ __( 'vehicle.permit_start_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_permit_start_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_permit_expiry_date" class="col-sm-5 col-form-label">{{ __( 'vehicle.permit_expiry_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_permit_expiry_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_inspection_expiry_date" class="col-sm-5 col-form-label">{{ __( 'vehicle.inspection_expiry_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_inspection_expiry_date" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_tngsn" class="col-sm-5 col-form-label">{{ __( 'vehicle.tngsn' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $vehicle_create }}_tngsn" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                @if ( 1 == 2 )
                <div class="mb-3 row">
                    <label for="{{ $vehicle_create }}_type" class="col-sm-5 col-form-label">{{ __( 'vehicle.type' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $vehicle_create }}_type">
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
            
        $( vc + '_road_tax_expiry_date' ).flatpickr( {
            disableMobile: true,
        } );

        $( vc + '_insurance_start_date' ).flatpickr( {
            disableMobile: true,
        } );

        $( vc + '_insurance_expiry_date' ).flatpickr( {
            disableMobile: true,
        } );

        $( vc + '_permit_start_date' ).flatpickr( {
            disableMobile: true,
        } );

        $( vc + '_permit_expiry_date' ).flatpickr( {
            disableMobile: true,
        } );

        $( vc + '_inspection_expiry_date' ).flatpickr( {
            disableMobile: true,
        } );
        
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
            formData.append( 'driver', $( vc + '_driver' ).val() );
            formData.append( 'company', $( vc + '_company' ).val() );
            formData.append( 'name', $( vc + '_name' ).val() );
            formData.append( 'license_plate', $( vc + '_license_plate' ).val() );
            formData.append( 'trailer_number', $( vc + '_trailer_number' ).val() );
            formData.append( 'road_tax_number', $( vc + '_road_tax_number' ).val() );
            formData.append( 'road_tax_expiry_date', $( vc + '_road_tax_expiry_date' ).val() );
            formData.append( 'insurance_number', $( vc + '_insurance_number' ).val() );
            formData.append( 'insurance_start_date', $( vc + '_insurance_start_date' ).val() );
            formData.append( 'insurance_expiry_date', $( vc + '_insurance_expiry_date' ).val() );
            formData.append( 'permit_number', $( vc + '_permit_number' ).val() );
            formData.append( 'permit', $( vc + '_permit' ).val() );
            formData.append( 'permit_start_date', $( vc + '_permit_start_date' ).val() );
            formData.append( 'permit_expiry_date', $( vc + '_permit_expiry_date' ).val() );
            formData.append( 'inspection_expiry_date', $( vc + '_inspection_expiry_date' ).val() );
            formData.append( 'tngsn', $( vc + '_tngsn' ).val() );
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

        $( vc + '_driver' ).select2( {
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

        $( vc + '_company' ).select2( {
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
<?php
$service_record_create = 'service_record_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.service_records' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $service_record_create }}_vehicle" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.vehicle' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $service_record_create }}_vehicle" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'maintenance_record.vehicle' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_record_create }}_company" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.company' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $service_record_create }}_company" >
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'maintenance_record.company' ) ] ) }}</option>
                            @foreach( $data['company'] as $key => $company )
                            <option value="{{ $company['id'] }}">{{ $company['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_record_create }}_service_date" class="col-sm-5 col-form-label">{{ __( 'datatables.service_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $service_record_create }}_service_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_record_create }}_workshop" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.workshop' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $service_record_create }}_workshop">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_record_create }}_meter_reading" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.meter_reading' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $service_record_create }}_meter_reading">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_record_create }}_document_reference" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.document_reference' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $service_record_create }}_document_reference" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $service_record_create }}_remarks" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $service_record_create }}_remarks" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'maintenance_record.documents' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $service_record_create }}_documents" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __( 'template.service_list' ) }}</h5>
                    <div>
                        <em class="text-primary fs-2 icon ni ni-plus-round" id="{{ $service_record_create }}_add" role="button"></em>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <div style="overflow-x: auto;">
                        <table class="table" id="service_item_table">
                            <thead class="tb-tnx-head">
                                <tr>
                                    <td class="text-center">{{ __( 'datatables.action' ) }}</td>
                                    <td class="">{{ __( 'maintenance_record.service_type' ) }}</td>
                                    <td class="">{{ __( 'maintenance_record.description' ) }}</td>
                                </tr>
                            </thead>
                            <tbody class="empty">
                                <tr>
                                    <td colspan="10" class="text-center">{{ __( 'maintenance_record.add_record_to_continue' ) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $service_record_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $service_record_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_item_modal">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.service_list' ) ) ] ) }}</h5>
                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close"><em class="icon ni ni-cross"></em></a>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="{{ $service_record_create }}_service_type">{{ __( 'maintenance_record.service_type' ) }}</label>
                    <div class="form-control-wrap">
                        <select class="form-control" id="{{ $service_record_create }}_service_type">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'maintenance_record.service_type' ) ] ) }}</option>
                            @foreach ( $data['service_types'] as $key => $type )
                            <option value="{{ $key }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div id="service_type_engine_oil" class="hidden">
                    <div class="form-group">
                        <label class="form-label" for="{{ $service_record_create }}_grades">{{ __( 'maintenance_record.grades' ) }}</label>
                        <div class="form-control-wrap">
                            <input type="text" class="form-control" id="{{ $service_record_create }}_grades" placeholder="{{ __( 'template.optional' ) }}">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="{{ $service_record_create }}_qty">{{ __( 'maintenance_record.qty_x', [ 'title' => __( 'maintenance_record.lt' ) ] ) }}</label>
                        <div class="form-control-wrap">
                            <input type="text" class="form-control" id="{{ $service_record_create }}_qty" placeholder="{{ __( 'template.optional' ) }}">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="{{ $service_record_create }}_next_service">{{ __( 'maintenance_record.next_service' ) }}</label>
                        <div class="form-control-wrap">
                            <input type="text" class="form-control" id="{{ $service_record_create }}_next_service" placeholder="{{ __( 'template.optional' ) }}">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div id="service_type_axle" class="hidden">
                    <div class="form-group">
                        <label class="form-label" for="{{ $service_record_create }}_axle_oil">{{ __( 'maintenance_record.axle_oil' ) }}</label>
                        <div class="form-control-wrap">
                            <select class="form-control" id="{{ $service_record_create }}_axle_oil">
                                <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'maintenance_record.axle_oil' ) ] ) }}</option>
                                <option value="1">{{ __( 'maintenance_record.rear' ) }}</option>
                                <option value="2">{{ __( 'maintenance_record.front' ) }}</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div id="service_type_others" class="hidden">
                    <div class="form-group">
                        <label class="form-label" for="{{ $service_record_create }}_description">{{ __( 'maintenance_record.description' ) }}</label>
                        <div class="form-control-wrap">
                            <input type="text" class="form-control" id="{{ $service_record_create }}_description" placeholder="{{ __( 'template.optional' ) }}">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="form-group"><button type="button" class="btn btn-lg btn-primary" id="{{ $service_record_create }}_m_submit">{{ __( 'template.save_changes' ) }}</button></div>
            </div>
        </div>
    </div>
</div>

<style>
    .service_item_row > td {
        vertical-align: middle;
    }
</style>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let serviceTypeMapper = @json( $data['service_types'] ),
            src = '#{{ $service_record_create }}',
            aim = new bootstrap.Modal( document.getElementById( 'add_item_modal' ) ),
            fileID = [];

        $( src + '_service_date' ).flatpickr();

        $( src + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.maintenance_record.serviceRecords' ) }}';
        } );

        $( src + '_submit' ).click( function() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let items = [];
            $( '.service_item_row' ).each( function( i, v ) {

                let itemObject = {
                    type: $( v ).find( '.service_type' ).data( 'type' ),
                    description: '',
                };

                if ( $( v ).find( '.service_type' ).data( 'type' ) == 1 ) {
                    itemObject['description'] = {
                        'grades': $( v ).find( '.sl_grades' ).data( 'value' ),
                        'qty': $( v ).find( '.sl_qty' ).data( 'value' ),
                        'next_service': $( v ).find( '.sl_next_service' ).data( 'value' ),
                    };
                } else if ( $( v ).find( '.service_type' ).data( 'type' ) == 8 ) {
                    itemObject['description'] = $( v ).find( '.sl_axle_oil' ).data( 'value' );
                } else {
                    itemObject['description'] = $( v ).find( '.description' ).html();
                }

                items.push( itemObject );

                // items.push( {
                //     type: $( v ).find( '.service_type' ).data( 'type' ),
                //     description: $( v ).find( '.service_type' ).data( 'type' ) == 1 ? {
                //         'grades': $( v ).find( '.sl_grades' ).data( 'value' ),
                //         'qty': $( v ).find( '.sl_qty' ).data( 'value' ),
                //         'next_service': $( v ).find( '.sl_next_service' ).data( 'value' ),
                //     } : $( v ).find( '.description' ).html()
                // } );
            } );

            let formData = new FormData();
            formData.append( 'vehicle', null === $( src + '_vehicle' ).val() ? '' : $( src + '_vehicle' ).val() );
            formData.append( 'company', $( src + '_company' ).val() );
            formData.append( 'service_date', $( src + '_service_date' ).val() );
            formData.append( 'workshop', $( src + '_workshop' ).val() );
            formData.append( 'meter_reading', $( src + '_meter_reading' ).val() );
            formData.append( 'document_reference', $( src + '_document_reference' ).val() );
            formData.append( 'remarks', $( src + '_remarks' ).val() );
            formData.append( 'documents', fileID );
            formData.append( 'items', JSON.stringify( items ) );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.maintenance_record.createServiceRecord' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.maintenance_record.serviceRecords' ) }}';
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

        $( src + '_service_type' ).on( 'change', function() {
            $( '#service_type_engine_oil' ).addClass( 'hidden' );
            $( '#service_type_axle' ).addClass( 'hidden' );
            $( '#service_type_others' ).addClass( 'hidden' );
            if ( $( this ).val() == 1 ) {
                $( '#service_type_engine_oil' ).removeClass( 'hidden' );
            } else if ( $( this ).val() == 8 ) {
                $( '#service_type_axle' ).removeClass( 'hidden' );
            } else {
                $( '#service_type_others' ).removeClass( 'hidden' );
            }
        } );

        $( src + '_add' ).click( function() {
            aim.toggle();
        } );

        $( src + '_m_submit' ).click( function() {

            let currentButton = $( this );

            let html = '',
                type = $( src + '_service_type' ).val(),
                array = [
                    $( src + '_grades' ).val(),
                    $( src + '_qty' ).val(),
                    $( src + '_next_service' ).val(),
                ],
                array2 = [
                    $( src + '_axle_oil' ).val(),
                ],
                tbody = $( '#service_item_table tbody' ),
                time = Date.now();

            if ( type == '' ) {
                return false;
            }

            buttonSubmitting( currentButton );

            $.ajax( {
                url: '{{ route( 'admin.maintenance_record.validateItemServiceRecord' ) }}',
                type: 'POST',
                data: {
                    type,
                    grades: array[0],
                    qty: array[1],
                    next_service: array[2],
                    axle_oil: array2[0],
                    description: $( src + '_description' ).val(),
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {

                    buttonSubmitted( currentButton );

                    let moreDescription = '';

                    if ( response.type == 1 ) {

                        array[0] = '<span class="sl_grades" data-value="' + array[0] + '">' + array[0] + '</span>';
                        array[1] = '<span class="sl_qty" data-value="' + array[1] + '">' + array[1] + '</span>';
                        array[2] = '<span class="sl_next_service" data-value="' + array[2] + '">' + array[2] + '</span>';

                        moreDescription = array.join( '<br>' );

                    } else if ( response.type == 8 ) {

                        moreDescription = '<span class="sl_axle_oil" data-value="' + array2[0] + '">' + ( array2[0] == 1 ? 'Rear' : 'Front' ) + '</span>';

                    } else {

                        moreDescription = $( src + '_description' ).val();
                    }

                    html +=
                    `
                    <tr class="service_item_row `+ time +`">
                        <td class="text-center">
                            <em class="text-primary fs-4 icon ni ni-minus-round align-middle sir_remove" data-id="` + time + `" role="button"></em>
                        </td>
                        <td class="service_type" data-type="` + type + `">` + ( serviceTypeMapper[type] ) + `</td>
                        <td class="description">` + moreDescription + `</td>
                    </tr>
                    `;

                    if ( tbody.hasClass( 'empty' ) ) {
                        tbody.empty();    
                    }

                    tbody.removeClass( 'empty' ).append( html );

                    aim.hide();
                },
                error: function( error ) {

                    buttonSubmitted( currentButton );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( src + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        aim.toggle();
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        $( document ).on( 'click', '.sir_remove', function() {
            let id = $( this ).data( 'id' );
            
            $( '.' + id ).remove();

            let = iirCount = $( '.service_item_row' ).length;

            if ( iirCount == 0 ) {
                let html = 
                $( '#service_item_table tbody' ).empty().addClass( 'empty' ).append( `
                <tr>
                    <td colspan="10" class="text-center">{{ __( 'maintenance_record.add_record_to_continue' ) }}</td>
                </tr>` );
            }
        } );

        $( src + '_vehicle' ).select2( {
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
                            text: '(' + v.license_plate + ')',
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

        $( '#add_item_modal' ).on( 'hidden.bs.modal', function() {
            $( '#service_type_engine_oil' ).addClass( 'hidden' );
            $( '#service_type_others' ).addClass( 'hidden' );
        } );

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( src + '_documents', {
            url: '{{ route( 'admin.file.upload' ) }}',
            acceptedFiles: 'image/jpg,image/jpeg,image/png,application/pdf',
            addRemoveLinks: true,
            init: function() {
                this.on( 'addedfile', function( file ) {

                    if ( file.type ) {
                        if ( !file.type.match(/image.*/) ) {
                            this.emit( 'thumbnail', file, '{{ asset( 'admin/images/file_pdf.png' ) }}' );
                        }
                    } else {
                        if ( file.fileType == 'pdf' ) {
                            this.emit( 'thumbnail', file, '{{ asset( 'admin/images/file_pdf.png' ) }}' );
                        }
                    }

                    $( file.previewElement ).bind( 'click', function() {

                        if ( file.xhr ) {
                            window.open( '{{ asset( 'storage') }}/' + JSON.parse( file.xhr.response ).data.file );
                        }

                        window.open( file.assetUrl );
                    } );
                } );
            },
            removedfile: function( file ) {
                let previewID = $( file.previewElement ).data( 'id' );

                let index = fileID.indexOf( previewID );

                if ( index !== -1 ) {
                    fileID.splice( index, 1 );
                }
                
                file.previewElement.remove();
            },
            success: function( file, response ) {
                if ( response.status == 200 )  {
                    file.previewElement.setAttribute( 'data-id', response.data.id );
                    fileID.push( response.data.id );
                }
            }
        } );
    } );
</script>
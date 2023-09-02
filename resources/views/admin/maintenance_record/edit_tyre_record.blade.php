<?php
$tyre_record_edit = 'tyre_record_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.tyre_records' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $tyre_record_edit }}_vehicle" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.vehicle' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $tyre_record_edit }}_vehicle" data-placeholder="{{ __( 'template.optional' ) }} - {{ __( 'datatables.select_x', [ 'title' => __( 'maintenance_record.vehicle' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $tyre_record_edit }}_purchase_date" class="col-sm-5 col-form-label">{{ __( 'datatables.purchase_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $tyre_record_edit }}_purchase_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $tyre_record_edit }}_purchase_bill_reference" class="col-sm-5 col-form-label">{{ __( 'maintenance_record.purchase_bill_reference' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $tyre_record_edit }}_purchase_bill_reference">
                        <div class="invalid-feedback"></div>
                    </div>
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
                    <h5 class="card-title mb-0">{{ __( 'template.item_list' ) }}</h5>
                    <div>
                        <em class="text-primary fs-2 icon ni ni-plus-round" id="{{ $tyre_record_edit }}_add" role="button"></em>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <div style="overflow-x: auto;">
                        <table class="table" id="tyre_item_table">
                            <thead class="tb-tnx-head">
                                <tr>
                                    <td class="text-center">{{ __( 'datatables.action' ) }}</td>
                                    <td class="">{{ __( 'maintenance_record.service_type' ) }}</td>
                                    <td class="">{{ __( 'maintenance_record.description' ) }}</td>
                                    <td class="">{{ __( 'maintenance_record.serial_number' ) }}</td>
                                    <td class="">{{ __( 'maintenance_record.supplier' ) }}</td>
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
                    <button id="{{ $tyre_record_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $tyre_record_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_item_modal">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.item_list' ) ) ] ) }}</h5>
                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close"><em class="icon ni ni-cross"></em></a>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="{{ $tyre_record_edit }}_tyre">{{ __( 'maintenance_record.tyre' ) }}</label>
                    <div class="form-control-wrap">
                        <select class="form-control" id="{{ $tyre_record_edit }}_tyre" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'maintenance_record.tyre' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="{{ $tyre_record_edit }}_serial_number">{{ __( 'maintenance_record.serial_number', [ 'title' => __( 'maintenance_record.serial_number' ) ] ) }}</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control" id="{{ $tyre_record_edit }}_serial_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="form-group"><button type="button" class="btn btn-lg btn-primary" id="{{ $tyre_record_edit }}_m_submit">{{ __( 'template.save_changes' ) }}</button></div>
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

        getTyreRecord();

        let tre = '#{{ $tyre_record_edit }}',
            aim = new bootstrap.Modal( document.getElementById( 'add_item_modal' ) );

        let purchaseDate = $( tre + '_purchase_date' ).flatpickr();

        $( tre + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.maintenance_record.tyreRecords' ) }}';
        } );

        $( tre + '_submit' ).click( function() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let items = [];
            $( '.tyre_item_row' ).each( function( i, v ) {
                items.push( {
                    tyre: $( v ).find( '.service_type' ).data( 'value' ),
                    serial_number: $( v ).find( '.serial_number' ).html(),
                } );
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'vehicle', null === $( tre + '_vehicle' ).val() ? '' : $( tre + '_vehicle' ).val() );
            formData.append( 'purchase_date', $( tre + '_purchase_date' ).val() );
            formData.append( 'purchase_bill_reference', $( tre + '_purchase_bill_reference' ).val() );
            formData.append( 'items', JSON.stringify( items ) );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.maintenance_record.updateTyreRecord' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.maintenance_record.tyreRecords' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( tre + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        $( tre + '_add' ).click( function() {
            aim.toggle();
        } );

        $( tre + '_m_submit' ).click( function() {

            resetInputValidation();

            let html = '',
                tbody = $( '#tyre_item_table tbody' ),
                time = Date.now();

            $.ajax( {
                url: '{{ route( 'admin.maintenance_record.validateItemTyreRecord' ) }}',
                type: 'POST',
                data: {
                    tyre: null === $( tre + '_tyre' ).val() ? '' : $( tre + '_tyre' ).val(),
                    serial_number: $( tre + '_serial_number' ).val(),
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {

                    html +=
                    `
                    <tr class="tyre_item_row `+ time +`">
                        <td class="text-center">
                            <em class="text-primary fs-4 icon ni ni-minus-round align-middle sir_remove" data-id="` + time + `" role="button"></em>
                        </td>
                        <td class="service_type" data-value="` + response.data.id + `">Tyre</td>
                        <td class="description">` + response.data.name + `</td>
                        <td class="serial_number">` + $( tre + '_serial_number' ).val() + `</td>
                        <td class="supplier">` + response.data.supplier.name + `</td>
                    </tr>
                    `;

                    if ( tbody.hasClass( 'empty' ) ) {
                        tbody.empty();    
                    }

                    tbody.removeClass( 'empty' ).append( html );

                    aim.hide();
                },
                error: function( error ) {
                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( tre + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
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

            let = iirCount = $( '.tyre_item_row' ).length;

            if ( iirCount == 0 ) {
                let html = 
                $( '#tyre_item_table tbody' ).empty().addClass( 'empty' ).append( `
                <tr>
                    <td colspan="10" class="text-center">{{ __( 'maintenance_record.add_record_to_continue' ) }}</td>
                </tr>` );
            }
        } );

        let vehicleSelect2 = $( tre + '_vehicle' ).select2( {
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

        $( tre + '_tyre' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.tyre.allTyres' ) }}',
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

                    data.tyres.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: '(' + v.code + ') ' + v.name,
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

        function getTyreRecord() {

            $( 'body' ).loading( { 
                message: '{{ __( 'template.loading' ) }}',
            } );

            $.ajax( {
                url: '{{ route( 'admin.maintenance_record.oneTyreRecord' ) }}',
                type: 'POST',
                data: {
                    id: '{{ request( 'id' ) }}',
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );

                    if ( response.vehicle ) {
                        let option1 = new Option( response.vehicle.name + ' (' + response.vehicle.license_plate + ')', response.vehicle.id, true, true );
                        vehicleSelect2.append( option1 );
                        vehicleSelect2.trigger( 'change' );
                    }

                    purchaseDate.setDate( response.local_purchase_date );
                    $( tre + '_purchase_bill_reference' ).val( response.purchase_bill_reference );

                    let items = response.items,
                        html = '',
                        tbody = $( '#tyre_item_table tbody' );

                    items.map( function( v, i ) {

                        html +=
                        `
                        <tr class="tyre_item_row `+ v.id +`">
                            <td class="text-center">
                                <em class="text-primary fs-4 icon ni ni-minus-round align-middle sir_remove" data-id="` + v.id + `" role="button"></em>
                            </td>
                            <td class="service_type" data-value="` + v.tyre_id + `">Tyre</td>
                            <td class="description">` + v.tyre.name + `</td>
                            <td class="serial_number">` + v.serial_number + `</td>
                            <td class="supplier">` + v.tyre.supplier.name + `</td>
                        </tr>
                        `;
                    } );

                    if ( items.length != 0 ) {
                        $( '#tyre_item_table tbody' ).removeClass( 'empty' );
                        tbody.empty().append( html );
                    }
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( tre + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        }

        $( '#add_item_modal' ).on( 'hidden.bs.modal', function() {
            $( '#service_type_engine_oil' ).addClass( 'hidden' );
            $( '#service_type_others' ).addClass( 'hidden' );
        } );
    } );
</script>
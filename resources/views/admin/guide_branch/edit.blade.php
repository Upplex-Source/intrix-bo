<?php
$guide_branch_edit = 'guide_branch_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.guide_branches' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $guide_branch_edit }}_state" class="col-sm-5 col-form-label">{{ __( 'guide_branch.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select form-select-sm" id="{{ $guide_branch_edit }}_state" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'guide_branch.state' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $guide_branch_edit }}" class="col-sm-5 col-form-label">
                        {{ __( 'guide_branch.title' ) }}
                    </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $guide_branch_edit }}_title" name="branch_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $guide_branch_edit }}" class="col-sm-5 col-form-label">
                        {{ __( 'guide_branch.phone_number' ) }}
                    </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $guide_branch_edit }}_phone_number" name="phone_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $guide_branch_edit }}_address" class="col-sm-5 col-form-label">{{ __( 'guide_branch.address' ) }}</label>
                    <div class="col-sm-7">
                        <textarea type="text" class="form-control form-control-sm" id="{{ $guide_branch_edit }}_address" rows="4"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $guide_branch_edit }}" class="col-sm-5 col-form-label">
                        {{ __( 'guide_branch.latitude' ) }}
                    </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $guide_branch_edit }}_latitude" name="latitude">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $guide_branch_edit }}" class="col-sm-5 col-form-label">
                        {{ __( 'guide_branch.longitude' ) }}
                    </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $guide_branch_edit }}_longitude" name="longitude">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

            </div>

                <div class="text-end">
                    <button id="{{ $guide_branch_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $guide_branch_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $guide_branch_edit }}',
                fileID = '';

        $( document ).on( 'click', '.remove-state', function () {
            $( this ).closest( '.state-input' ).remove();
        });

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.guide_branch.index' ) }}';
        } );

        let startDate = $( fe + '_start_date' ).flatpickr( {
            disableMobile: false,
        } );

        let endDate = $( fe + '_expired_date' ).flatpickr( {
            disableMobile: false,
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'title', $( fe + '_title' ).val() );
            formData.append( 'phone_number', $( fe + '_phone_number' ).val() );
            formData.append( 'address', $( fe + '_address' ).val() );
            formData.append( 'state', $( fe + '_state' ).val() );
            formData.append( 'latitude', $( fe + '_latitude' ).val() );
            formData.append( 'longitude', $( fe + '_longitude' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.guide_branch.updateGuideBranch' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.guide_branch.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( fe + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getGuide();

        function getGuide() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.guide_branch.oneGuideBranch' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    
                    $( fe + '_title' ).val( response.title );
                    $( fe + '_phone_number' ).val( response.phone_number );
                    $( fe + '_address' ).val( response.address );
                    $( fe + '_state' ).val( response.state );
                    $( fe + '_latitude' ).val( response.latitude );
                    $( fe + '_longitude' ).val( response.longitude );

                    if( response.state ){
                        let option = new Option( response.state.name, response.state.id, true, true );
                        stateSelect2.append( option )
                    }

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

        let stateSelect2 = $( fe + '_state' ).select2( {
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: true,
            allowClear: true,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.guide_state.allGuideStates' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        title: params.term, // search term
                        start: params.page ? params.page : 0,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.states.map( function( v, i ) {
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
            }
        } );

    } );
</script>
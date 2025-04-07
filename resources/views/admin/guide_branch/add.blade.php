<?php
$guide_branch_create = 'guide_branch_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.guide_branches' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            {{-- Branch Info --}}
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                
                <div class="mb-3 row">
                    <label for="{{ $guide_branch_create }}_state" class="col-sm-5 col-form-label">{{ __( 'guide_branch.state' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select form-select-sm" id="{{ $guide_branch_create }}_state" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'guide_branch.state' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $guide_branch_create }}" class="col-sm-5 col-form-label">
                        {{ __( 'guide_branch.title' ) }}
                    </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $guide_branch_create }}_title" name="branch_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $guide_branch_create }}" class="col-sm-5 col-form-label">
                        {{ __( 'guide_branch.phone_number' ) }}
                    </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $guide_branch_create }}_phone_number" name="phone_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $guide_branch_create }}_address" class="col-sm-5 col-form-label">{{ __( 'guide_branch.address' ) }}</label>
                    <div class="col-sm-7">
                        <textarea type="text" class="form-control form-control-sm" id="{{ $guide_branch_create }}_address" rows="4"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $guide_branch_create }}" class="col-sm-5 col-form-label">
                        {{ __( 'guide_branch.latitude' ) }}
                    </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $guide_branch_create }}_latitude" name="latitude">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $guide_branch_create }}" class="col-sm-5 col-form-label">
                        {{ __( 'guide_branch.longitude' ) }}
                    </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $guide_branch_create }}_longitude" name="longitude">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="row mt-4">
            <div class="col text-end">
                <button id="{{ $guide_branch_create }}_cancel" type="button" class="btn btn-outline-secondary">
                    {{ __( 'template.cancel' ) }}
                </button>
                &nbsp;
                <button id="{{ $guide_branch_create }}_submit" type="submit" class="btn btn-primary">
                    {{ __( 'template.save_changes' ) }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $guide_branch_create }}',
                fileID = '';

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.guide_branch.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'title', $( fc + '_title' ).val() );
            formData.append( 'phone_number', $( fc + '_phone_number' ).val() );
            formData.append( 'address', $( fc + '_address' ).val() );
            formData.append( 'state', $( fc + '_state' ).val() );
            formData.append( 'latitude', $( fc + '_latitude' ).val() );
            formData.append( 'longitude', $( fc + '_longitude' ).val() );
            

            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.guide_branch.createGuideBranch' ) }}',
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
                            $( fc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        let stateSelect2 = $( fc + '_state' ).select2( {
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
                        start: ( page - 1 ) * 10,
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
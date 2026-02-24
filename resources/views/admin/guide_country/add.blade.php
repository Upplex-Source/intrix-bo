<?php
$guide_country_create = 'guide_country_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.guide_countries' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            {{-- Country Info --}}
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $guide_country_create }}_name" class="col-sm-5 col-form-label">
                        {{ __( 'guide_country.name' ) }}
                    </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $guide_country_create }}_name" name="country_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $guide_country_create }}_calling_code" class="col-sm-5 col-form-label">
                        {{ __( 'guide_country.calling_code' ) }}
                    </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $guide_country_create }}_calling_code" name="calling_code">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

            {{-- States Info --}}
            <div class="col-md-12 col-lg-6 mt-3">
                <h5 class="card-title mb-4">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.guide_states' ) ) ] ) }}</h5>

                <div id="states-wrapper">
                    <div class="row mb-2 state-input">
                        <div class="col-10">
                            <input type="text" class="form-control" name="states[]" placeholder="{{ __( 'guide_state.name' ) }}">
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-danger remove-state">&times;</button>
                        </div>
                    </div>
                </div>

                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-state">
                        {{ __( 'template.add_state' ) }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="row mt-4">
            <div class="col text-end">
                <button id="{{ $guide_country_create }}_cancel" type="button" class="btn btn-outline-secondary">
                    {{ __( 'template.cancel' ) }}
                </button>
                &nbsp;
                <button id="{{ $guide_country_create }}_submit" type="submit" class="btn btn-primary">
                    {{ __( 'template.save_changes' ) }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $guide_country_create }}',
                fileID = '';

        $( '#add-state' ).on( 'click', function () {
            $( '#states-wrapper' ).append(`
                <div class="state-input row mb-2">
                    <div class="col-10">
                        <input type="text" class="form-control" name="states[]" placeholder="State name">
                    </div>
                    <div class="col-2">
                        <button type="button" class="btn btn-danger remove-state">&times;</button>
                    </div>
                </div>
            `);
        });

        $( document ).on( 'click', '.remove-state', function () {
            $( this ).closest( '.state-input' ).remove();
        });

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.guide_country.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'name', $( fc + '_name' ).val() );
            formData.append( 'calling_code', $( fc + '_calling_code' ).val() );

            const stateInputs = document.querySelectorAll('input[name="states[]"]');
            const states = [];

            stateInputs.forEach(input => {
                const stateValue = input.value.trim();
                if (stateValue) {
                    states.push(stateValue);
                }
            });
            formData.append( 'states', states );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.guide_country.createGuideCountry' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.guide_country.index' ) }}';
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

    } );
</script>
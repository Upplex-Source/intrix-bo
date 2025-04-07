<?php
$guide_country_edit = 'guide_country_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.guide_countries' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>

                <div class="mb-3 row">
                    <label for="{{ $guide_country_edit }}_name" class="col-sm-5 col-form-label">{{ __( 'guide_country.name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $guide_country_edit }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none">
                    <label for="{{ $guide_country_edit }}_calling_code" class="col-sm-5 col-form-label">
                        {{ __( 'guide_country.calling_code' ) }}
                    </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $guide_country_edit }}_calling_code" name="calling_code">
                        <div class="invalid-feedback"></div>
                    </div>
                </div> 

                {{-- States Info --}}
                <div class="col-md-12 col-lg-6 mt-3">
                    <h5 class="card-title mb-4">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.guide_states' ) ) ] ) }}</h5>

                    <div id="states-wrapper">
                        
                    </div>

                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-state">
                        {{ __( 'template.add_state' ) }}
                    </button>
                </div>
            </div>

                <div class="text-end">
                    <button id="{{ $guide_country_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $guide_country_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $guide_country_edit }}',
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

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.guide_country.index' ) }}';
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
            formData.append( 'name', $( fe + '_name' ).val() );
            formData.append( 'calling_code', $( fe + '_calling_code' ).val() );

            const stateInputs = document.querySelectorAll('input[name="states[]"]');
            const states = [];

            stateInputs.forEach(input => {
                const stateValue = input.value.trim();
                const stateId = input.getAttribute('data-id'); // or input.dataset.id

                if (stateValue) {
                    states.push({
                        id: stateId,
                        name: stateValue
                    });
                }
            });

            formData.append( 'states', JSON.stringify( states ) );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.guide_country.updateGuideCountry' ) }}',
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
                url: '{{ route( 'admin.guide_country.oneGuideCountry' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    
                    $( fe + '_name' ).val( response.name );
                    $( fe + '_calling_code' ).val( response.calling_code );

                    if( response.states ){
                        $.each(response.states, function (index, state) {
                            $('#states-wrapper').append(`
                                <div class="row mb-2 state-input">
                                    <div class="col-10">
                                        <input type="text" class="form-control" name="states[]" value="${state.name}" data-id="${state.id}">
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-danger remove-state">&times;</button>
                                    </div>
                                </div>
                            `);
                        });
                    }else {
                        $('#states-wrapper').append(`
                            <div class="row mb-2 state-input">
                                <div class="col-10">
                                    <input type="text" class="form-control" name="states[]" data-id="">
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-danger remove-state">&times;</button>
                                </div>
                            </div>
                        `);
                    }

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

    } );
</script>
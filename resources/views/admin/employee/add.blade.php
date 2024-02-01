<?php
$worker_create = 'worker_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.workers' ) ) ] ) }}</h3>
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
                    <div class="dropzone mb-3" id="{{ $worker_create }}_photo" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $worker_create }}_name" class="col-sm-5 col-form-label">{{ __( 'worker.name' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $worker_create }}_name">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $worker_create }}_email" class="col-sm-5 col-form-label">{{ __( 'worker.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $worker_create }}_email" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $worker_create }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'worker.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $worker_create }}_phone_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $worker_create }}_identification_number" class="col-sm-5 col-form-label">{{ __( 'worker.identification_number' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $worker_create }}_identification_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $worker_create }}_date_of_birth" class="col-sm-5 col-form-label">{{ __( 'worker.date_of_birth' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $worker_create }}_date_of_birth" placeholder="{{ __( 'template.optional' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $worker_create }}_remarks" class="col-sm-5 col-form-label">{{ __( 'worker.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $worker_create }}_remarks" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="text-end">
                    <button id="{{ $worker_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $worker_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let wc = '#{{ $worker_create }}',
            fileID = '';

        $( wc + '_date_of_birth' ).flatpickr( {
            disableMobile: true,
            maxDate: '{{ date( 'Y-m-d' ) }}',
        } );

        $( wc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.worker.index' ) }}';
        } );

        $( wc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'photo', fileID );
            formData.append( 'name', $( wc + '_name' ).val() );
            formData.append( 'email', $( wc + '_email' ).val() );
            formData.append( 'phone_number', $( wc + '_phone_number' ).val() );
            formData.append( 'identification_number', $( wc + '_identification_number' ).val() );
            formData.append( 'remarks', $( wc + '_remarks' ).val() );
            formData.append( 'date_of_birth', $( wc + '_date_of_birth' ).val() );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.worker.createWorker' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.worker.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( wc + '_' + key ).addClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( wc + '_photo', { 
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            removedfile: function( file ) {
                fileID = null;
                file.previewElement.remove();
            },
            success: function( file, response ) {
                console.log( file );
                console.log( response );
                if ( response.status == 200 )  {
                    fileID = response.data.id;
                }
            }
        } );

    } );
</script>
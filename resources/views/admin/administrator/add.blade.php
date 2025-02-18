<?php
$administrator_create = 'administrator_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.administrators' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $administrator_create }}_username" class="col-sm-5 col-form-label">{{ __( 'administrator.username' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $administrator_create }}_username">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $administrator_create }}_email" class="col-sm-5 col-form-label">{{ __( 'administrator.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $administrator_create }}_email">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $administrator_create }}_fullname" class="col-sm-5 col-form-label">{{ __( 'administrator.fullname' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $administrator_create }}_fullname">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $administrator_create }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'administrator.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <div class="input-group">
                            <button class="flex-shrink-0 inline-flex items-center input-group-text" type="button">
                                +60
                            </button>
                            <input type="text" class="form-control" id="{{ $administrator_create }}_phone_number">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>                    
                </div>
                <div class="mb-3 row">
                    <label for="{{ $administrator_create }}_password" class="col-sm-5 col-form-label">{{ __( 'administrator.password' ) }}</label>
                    <div class="col-sm-7">
                        <input type="password" class="form-control" id="{{ $administrator_create }}_password" autocomplete="new-password">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $administrator_create }}_role" class="col-sm-5 col-form-label">{{ __( 'administrator.role' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $administrator_create }}_role">
                            <option value="">{{ __( 'datatables.select_x', [ 'title' => __( 'administrator.role' ) ] ) }}</option>
                            @foreach( $data['roles'] as $role )
                            <option value="{{ $role['value'] }}">{{ $role['title'] }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'administrator.profile_pic' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $administrator_create }}_profile_pic" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="text-end">
                    <button id="{{ $administrator_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $administrator_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let ac = '#{{ $administrator_create }}'
            fileID = '';

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( ac + '_profile_pic', { 
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 1,
            maxFilesize: 50,
            acceptedFiles: 'image/jpg,image/jpeg,image/png',
            addRemoveLinks: true,
            removedfile: function( file ) {

                var idToRemove = file.previewElement.id;

                var idArrays = fileID.split(/\s*,\s*/);

                var indexToRemove = idArrays.indexOf( idToRemove.toString() );
                if (indexToRemove !== -1) {
                    idArrays.splice( indexToRemove, 1 );
                }

                fileID = idArrays.join( ', ' );

                file.previewElement.remove();
            },
            success: function( file, response ) {

                if ( response.status == 200 )  {
                    if ( fileID !== '' ) {
                        fileID += ','; // Add a comma if fileID is not empty
                    }
                    fileID += response.data.id;

                    file.previewElement.id = response.data.id;
                }
            }
        } );


        $( ac + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.administrator.index' ) }}';
        } );

        $( ac + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'username', $( ac + '_username' ).val() );
            formData.append( 'email', $( ac + '_email' ).val() );
            formData.append( 'fullname', $( ac + '_fullname' ).val() );
            formData.append( 'phone_number', $( ac + '_phone_number' ).val() );
            formData.append( 'password', $( ac + '_password' ).val() );
            formData.append( 'role', $( ac + '_role' ).val() );
            formData.append( 'profile_pic', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.administrator.createAdministrator' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.administrator.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( ac + '_' + key ).addClass( 'is-invalid' ).next().text( value );
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
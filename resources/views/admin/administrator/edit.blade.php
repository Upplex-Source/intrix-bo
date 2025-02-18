<?php
$administrator_edit = 'administrator_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.administrators' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $administrator_edit }}_username" class="col-sm-5 col-form-label">{{ __( 'administrator.username' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $administrator_edit }}_username">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $administrator_edit }}_email" class="col-sm-5 col-form-label">{{ __( 'administrator.email' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $administrator_edit }}_email">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $administrator_edit }}_fullname" class="col-sm-5 col-form-label">{{ __( 'administrator.fullname' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $administrator_edit }}_fullname">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $administrator_edit }}_phone_number" class="col-sm-5 col-form-label">{{ __( 'administrator.phone_number' ) }}</label>
                    <div class="col-sm-7">
                        <div class="input-group">
                            <button class="flex-shrink-0 inline-flex items-center input-group-text" type="button">
                                +60
                            </button>
                            <input type="text" class="form-control" id="{{ $administrator_edit }}_phone_number">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>                    
                </div>
                <div class="mb-3 row">
                    <label for="{{ $administrator_edit }}_password" class="col-sm-5 col-form-label">{{ __( 'administrator.password' ) }}</label>
                    <div class="col-sm-7">
                        <input type="password" class="form-control" id="{{ $administrator_edit }}_password" autocomplete="new-password" placeholder="{{ __( 'template.leave_blank' ) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $administrator_edit }}_role" class="col-sm-5 col-form-label">{{ __( 'administrator.role' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $administrator_edit }}_role">
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
                    <div class="dropzone mb-3" id="{{ $administrator_edit }}_profile_pic" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="text-end">
                    <button id="{{ $administrator_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $administrator_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let ae = '#{{ $administrator_edit }}',
            fileID = '';
        
        $( ae + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.administrator.index' ) }}';
        } );

        $( ae + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'username', $( ae + '_username' ).val() );
            formData.append( 'email', $( ae + '_email' ).val() );
            formData.append( 'fullname', $( ae + '_fullname' ).val() );
            formData.append( 'phone_number', $( ae + '_phone_number' ).val() );
            formData.append( 'password', $( ae + '_password' ).val() );
            formData.append( 'role', $( ae + '_role' ).val() );
            formData.append( 'profile_pic', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.administrator.updateAdministrator' ) }}',
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
                            $( ae + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();
                    }
                }
            } );
        } );

        getAdministrator();
        Dropzone.autoDiscover = false;

        function getAdministrator() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.administrator.oneAdministrator' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( ae + '_username' ).val( response.name );
                    $( ae + '_email' ).val( response.email );
                    $( ae + '_fullname' ).val( response.fullname );
                    $( ae + '_phone_number' ).val( response.phone_number );
                    $( ae + '_role' ).val( response.role );

                    const dropzone = new Dropzone( ae + '_profile_pic', {
                        url: '{{ route( 'admin.file.upload' ) }}',
                        maxFiles: 1,
                        maxFileSize: 50,
                        acceptedFiles: 'image/jpg,image/jpeg,image/png',
                        addRemoveLinks: true,
                        init: function() {

                            let that = this;
                            console.log(response)
                            if ( response.profile_pic_path != 0 && response.profile_pic ) {
                                let myDropzone = that
                                    cat_id = '{{ request('id') }}',
                                    mockFile = { name: 'Default', size: 1024, accepted: true, id: cat_id };

                                myDropzone.files.push( mockFile );
                                myDropzone.displayExistingFile( mockFile, response.profile_pic_path );
                                $( myDropzone.files[myDropzone.files.length - 1].previewElement ).data( 'id', cat_id );
                            }
                        },
                        removedfile: function (file) {
                            Swal.fire({
                                title: "Are you sure?",
                                text: "This will permanently remove the profile picture.",
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#d33",
                                cancelButtonColor: "#3085d6",
                                confirmButtonText: "Yes, delete it!",
                                cancelButtonText: "Cancel"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    var idToRemove = file.id;
                                    var idArrays = fileID.split(/\s*,\s*/);
                                    var indexToRemove = idArrays.indexOf(idToRemove.toString());

                                    if (indexToRemove !== -1) {
                                        idArrays.splice(indexToRemove, 1);
                                    }

                                    fileID = idArrays.join(", ");
                                    file.previewElement.remove();

                                    // Call the function to remove the image from the server
                                    removeGallery(idToRemove);

                                    Swal.fire("Deleted!", "The profile picture has been removed.", "success");
                                }
                            });
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

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }

        function removeGallery( gallery ) {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', gallery );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.administrator.removeProfilePic' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    // $( '#modal_success .caption-text' ).html( response.message );
                    // modalSuccess.toggle();
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
        }
    } );
</script>
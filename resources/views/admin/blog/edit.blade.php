<?php
$blog_edit = 'blog_edit';
?>

<style>
    .bootstrap-tagsinput .tag {
        border: 1px solid black;
        background-color: #5d5d5d;
        border-radius: 10px;
        padding: 2px 5px;
    }
    .bootstrap-tagsinput {
        width: 100% !important;
    }
    .dz-video {
        width: 120px;
        height: 120px;
    }
</style>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="mb-3 row">
                <label for="{{ $blog_edit }}_main_title" class="col-sm-5 col-form-label">{{ __( 'blog.title' ) }}</label>
                <div class="col-sm-7">
                    <input type="text" class="form-control form-control-sm" id="{{ $blog_edit }}_main_title">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="mb-3 row">
                <label for="{{ $blog_edit }}_subtitle" class="col-sm-5 col-form-label">{{ __( 'blog.subtitle' ) }}</label>
                <div class="col-sm-7">
                    <input type="text" class="form-control form-control-sm" id="{{ $blog_edit }}_subtitle">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            
            <div class="mb-3 row">
                <label for="{{ $blog_edit }}_type" class="col-sm-5 col-form-label">{{ __( 'blog.type' ) }}</label>
                <div class="col-sm-7">
                    <select class="form-select form-select-sm" id="{{ $blog_edit }}_type">
                        @foreach( $data['types'] as $key => $value )
                        <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $blog_edit }}_publish_date" class="col-sm-5 col-form-label">{{ __( 'blog.publish_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $blog_edit }}_publish_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $blog_edit }}_tags" class="col-sm-5 col-form-label">{{ __( 'blog.tags' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" id="{{ $blog_edit }}_tags" class="form-control form-control-sm" data-role="tagsinput">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $blog_edit }}_slug" class="col-sm-5 col-form-label">{{ __( 'blog.slug' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" id="{{ $blog_edit }}_slug" class="form-control form-control-sm">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3 row">
                    <label for="{{ $blog_edit }}_meta_title" class="col-sm-5 col-form-label">{{ __( 'blog.meta_title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control form-control-sm" id="{{ $blog_edit }}_meta_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $blog_edit }}_meta_desc" class="col-sm-5 col-form-label">{{ __( 'blog.meta_desc' ) }}</label>
                    <div class="col-sm-7">
                        <textarea type="text" class="form-control form-control-sm" id="{{ $blog_edit }}_meta_desc" rows="4"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="mb-1">{{ __( 'blog.thumbnail' ) }}</label>
                <div class="dropzone" id="{{ $blog_edit }}_image" style="min-height: 0px;">
                    <div class="dz-message needsclick">
                        <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                    </div>
                </div>
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label class="mb-1">{{ __( 'blog.gallery' ) }}</label>
                <div class="dropzone" id="{{ $blog_edit }}_gallery" style="min-height: 0px;">
                    <div class="dz-message needsclick">
                        <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                    </div>
                </div>
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label for="{{ $blog_edit }}_text" class="col-sm-5 col-form-label">{{ __( 'blog.content' ) }}</label>
                <div class="">
                    <textarea class="form-control form-control-sm" id="{{ $blog_edit }}_text" rows="10"></textarea>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="text-end">
    <button id="{{ $blog_edit }}_cancel" type="button" class="btn btn-sm btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
    &nbsp;
    <button id="{{ $blog_edit }}_submit" type="button" class="btn btn-sm btn-primary">{{ __( 'template.save_changes' ) }}</button>
</div>
{{-- content --}}
<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) . Helper::assetVersion() }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) . Helper::assetVersion() }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) . Helper::assetVersion() }}"></script>
<script>
window.ckeupload_path = '{{ route( 'admin.file.blogUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
window.cke_element = 'blog_edit_text';
</script>

<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init.js' ). Helper::assetVersion() }}"></script>


<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let be = '#{{ $blog_edit }}'
            fileID = '',
            file2ID = [];

        getBlog();

        $( be + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.blog.index' ) }}';
        } );

        $( be + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ Request( 'id' ) }}' );
            formData.append( 'main_title', $( be + '_main_title' ).val() );
            formData.append( 'subtitle', $( be + '_subtitle' ).val() );
            formData.append( 'type', $( be + '_type' ).val() );
            formData.append( 'publish_date', $( be + '_publish_date' ).val() );
            formData.append( 'meta_title', $( be + '_meta_title' ).val() );
            formData.append( 'meta_desc', $( be + '_meta_desc' ).val() );
            formData.append( 'tag', $( be + '_tags' ).val() );
            formData.append( 'slug', $( be + '_slug' ).val() );
            formData.append( 'text', editor.getData() );
            formData.append( 'image', fileID );
            formData.append( 'gallery', JSON.stringify(file2ID) );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.blog.updateBlog' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.blog.index' ) }}';
                    } );
                },
                error: function( error ) {
                    $( 'body' ).loading( 'stop' );

                    if ( error.status === 422 ) {
                        let errors = error.responseJSON.errors;
                        $.each( errors, function( key, value ) {
                            $( be + '_' + key ).addClass( 'is-invalid' ).next().text( value );
                        } );
                    } else {
                        $( '#modal_danger .caption-text' ).html( error.responseJSON.message );
                        modalDanger.toggle();       
                    }
                }
            } );
        } );
        
        $( be + '_tags').tagsinput();
                
        Dropzone.autoDiscover = false;

        $( be + '_publish_date' ).flatpickr( {
            dateFormat: "Y-m-d",
            disableMobile: true,
            onClose: function( selected, dateStr, instance ) {
                window[$( instance.element ).data('id')] = $( instance.element ).val();
                dt_table.draw();
            }
        } );

        function getBlog() {
            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.blog.oneBlog' ) }}',
                type: 'POST',
                data: {
                    id: '{{ request( 'id' ) }}',
                    _token: '{{ csrf_token() }}',
                },
                success: function( response ) {

                    $( be + '_main_title' ).val( response.main_title );
                    $( be + '_subtitle' ).val( response.subtitle );
                    $( be + '_type' ).val( response.type );
                    $( be + '_slug' ).val( response.slug );
                    $( be + '_publish_date' ).val( response.display_publish_date );
                    $( be + '_meta_title' ).val( response.meta_title );
                    $( be + '_meta_desc' ).val( response.meta_desc );
                    
                    $.each( response.tag, function( i, v ) {
                        $( be + '_tags').tagsinput( 'add', v.tag );
                    } );

                    editor.setData( response.text );

                    fileID = response.image;
                    let imagePath = response.image;

                    const dropzone = new Dropzone( be + '_image', { 
                        url: '{{ route( 'admin.file.blogUpload' ) }}',
                        maxFiles: 1,
                        acceptedFiles: 'image/jpg,image/jpeg,image/png',
                        addRemoveLinks: true,
                        init: function() {
                            this.on("addedfile", function (file) {
                                if (this.files.length > 1) {
                                    this.removeFile(this.files[0]);
                                }
                            });
                            if ( imagePath ) {
                                let myDropzone = this,
                                    mockFile = { name: 'Default', size: 1024, accepted: true };

                                myDropzone.files.push( mockFile );
                                myDropzone.displayExistingFile( mockFile, imagePath );
                            }
                        },
                        removedfile: function( file ) {
                            fileID = null;
                            file.previewElement.remove();
                        },
                        success: function( file, response ) {
                            if ( response.status == 200 )  {
                                fileID = response.url;
                            }
                        }
                    } );

                    const dropzone2 = new Dropzone(be + '_gallery', {
                        url: '{{ route("admin.file.blogUpload") }}',
                        acceptedFiles: 'image/jpg,image/jpeg,image/png,video/mp4,video/webm,video/ogg,video/avi,video/mov,video/quicktime',
                        addRemoveLinks: true,
                        previewTemplate: `
                            <div class="dz-preview dz-file-preview">
                                <div class="dz-image">
                                    <img data-dz-thumbnail />
                                    <video class="dz-video" controls style="display: none;">
                                        <source data-dz-video-source />
                                    </video>
                                </div>
                                <div class="dz-error-message"><span data-dz-errormessage></span></div>
                                <div class="dz-success-mark"><span>✔</span></div>
                                <div class="dz-error-mark"><span>✘</span></div>
                            </div>
                        `,
                        init: function() {
                            let myDropzone2 = this;
                            
                            response.images.forEach(function(image) {
                                let mockFile = { 
                                    name: 'default', 
                                    size: 1024, 
                                    accepted: true, 
                                    fileID: image.path 
                                };
                                myDropzone2.displayExistingFile(mockFile, image.path);
                                file2ID.push(image.path);
                                
                                if (image.path.match(/\.(mp4|webm|ogg)$/i)) {
                                    let previewElement = mockFile.previewElement;
                                    if (previewElement) {
                                        let imgPreview = previewElement.querySelector('img[data-dz-thumbnail]');
                                        let videoPreview = previewElement.querySelector('.dz-video');
                                        let videoSource = videoPreview.querySelector('[data-dz-video-source]');
                                        
                                        if (imgPreview) imgPreview.style.display = 'none';
                                        if (videoPreview) {
                                            videoPreview.style.display = 'block';
                                            videoSource.src = image.path;
                                            videoPreview.load();
                                        }
                                    }
                                }
                            });

                            this.on("addedfile", function(file) {
                                if (file.type.match(/video/)) {
                                    let previewElement = file.previewElement;
                                    let imgPreview = previewElement.querySelector('img[data-dz-thumbnail]');
                                    let videoPreview = previewElement.querySelector('.dz-video');
                                    let videoSource = videoPreview.querySelector('[data-dz-video-source]');
                                    
                                    imgPreview.style.display = 'none';
                                    videoPreview.style.display = 'block';
                                    
                                    let videoUrl = URL.createObjectURL(file);
                                    videoSource.src = videoUrl;
                                    videoPreview.load();
                                }
                            });

                            this.on("success", function(file, response) {
                                if (response.status == 200) {
                                    file.fileID = response.url;
                                    file2ID.push(response.url);
                                }
                            });

                            this.on("removedfile", function(file) {
                                if (file.fileID) {
                                    file2ID = file2ID.filter(url => url !== file.fileID);
                                }
                                // Clean up video URL if it exists
                                if (file.type.match(/video/)) {
                                    let videoPreview = file.previewElement.querySelector('.dz-video source');
                                    if (videoPreview && videoPreview.src) {
                                        URL.revokeObjectURL(videoPreview.src);
                                    }
                                }
                            });
                        }
                    });

                    $( 'body' ).loading( 'stop' );
                },
            } );
        }
    } );
</script>
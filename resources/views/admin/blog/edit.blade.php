
<?php
$blog_edit = 'blog_edit';
?>

<style>
    .modal {
        z-index: 1057 !important; /* Ensure modal is above Select2 */
    }

    .modal-backdrop {
        z-index: 1050 !important; /* Make sure the backdrop covers the page */
    }

    .dz-thumbnail {
        text-align: center;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

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
    .ck-editor__editable_inline {
        min-height: 400px;
    }
</style>

<!-- Create New Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCategoryModalLabel">Create New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createCategoryForm">
                    <div class="mb-3">
                        <label for="categoryTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="categoryTitle" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-body">
        <div class="row">
            
            @can ( 'edit administrators' )
            <div class="mb-3 row">
                <label for="{{ $blog_edit }}_author" class="col-sm-5 col-form-label">{{ __( 'blog.author' ) }}</label>
                <div class="col-sm-7">
                    <select class="form-select form-select-sm" id="{{ $blog_edit }}_author" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'user_checkin.user' ) ] ) }}">
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            @endcan
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
            <div class="col-md-6 mb-3 ">
            
                <div class="mb-3 row">
                    <label for="{{ $blog_edit }}_category" class="col-sm-5 col-form-label">{{ __( 'blog.category' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select form-select-sm" id="{{ $blog_edit }}_category" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'blog.category' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3 ">

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
            </div>

            <div class="col-md-6 mb-3 ">
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
                
                <div class="mb-3 row">
                    <label for="{{ $blog_edit }}_min_of_read" class="col-sm-5 col-form-label">{{ __( 'blog.min_of_read' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" id="{{ $blog_edit }}_min_of_read" class="form-control form-control-sm">
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
            if ($(be + '_author').length && $(be + '_author').val() !== '') {
                formData.append('author', $(be + '_author').val());
            }
            formData.append( 'main_title', $( be + '_main_title' ).val() );
            formData.append( 'subtitle', $( be + '_subtitle' ).val() );
            formData.append( 'type', $( be + '_type' ).val() );
            formData.append( 'category', $( be + '_category' ).val() );
            formData.append( 'publish_date', $( be + '_publish_date' ).val() );
            formData.append( 'meta_title', $( be + '_meta_title' ).val() );
            formData.append( 'meta_desc', $( be + '_meta_desc' ).val() );
            formData.append( 'tag', $( be + '_tags' ).val() );
            formData.append( 'slug', $( be + '_slug' ).val() );
            formData.append( 'min_of_read', $( be + '_min_of_read' ).val() );
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
                    $( be + '_min_of_read' ).val( response.min_of_read );
                    $( be + '_publish_date' ).val( response.display_publish_date );
                    $( be + '_meta_title' ).val( response.meta_title );
                    $( be + '_meta_desc' ).val( response.meta_desc );
                    
                    $.each( response.tag, function( i, v ) {
                        $( be + '_tags').tagsinput( 'add', v.tag );
                    } );

                    @can ( 'edit administrators' )
                    if( response.author ){
                        let option = new Option( response.author.email, response.author.id, true, true );
                        authorSelect2.append( option )
                    }
                    @endcan

                    response.categories_metas.forEach((category, index) => {
                        let option1 = new Option( category.title, category.id, true, true );
                        categorySelect2.append( option1 );
                        categorySelect2.trigger( 'change' );
                    });

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

        @can ( 'edit administrators' )
        let authorSelect2 = $( be + '_author' ).select2( {
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: true,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.administrator.allAdministrators' ) }}',
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

                    data.administrators.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.email,
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
        @endcan

        let categorySelect2 = $(be + '_category').select2({
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            closeOnSelect: false, // Keeps the dropdown open after a selection

            ajax: {
                method: 'POST',
                url: '{{ route('admin.blog.allBlogCategories') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        title: params.term,
                        status: 10,
                        start: ((params.page ? params.page : 1) - 1) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;

                    // Always add the "Create New Category" option
                    let processedResult = [{
                        id: 'new_category',
                        text: '➕ Create New Category',
                        newOption: true
                    }];

                    // Add categories if they exist
                    if (data.blog_categories.length > 0) {
                        processedResult = processedResult.concat(data.blog_categories.map(category => ({
                            id: category.id,
                            text: category.title,
                        })));
                    }

                    return {
                        results: processedResult,
                        pagination: {
                            more: (params.page * 10) < data.recordsFiltered
                        }
                    };
                }
            }
        }).on('select2:select', function(e) {
            var selectedData = e.params.data;

            if (selectedData.id === 'new_category') {
                // Deselect "Create New Category" after it's selected
                var select = $(be + '_category');
                
                // Show the modal for category creation
                $('#createCategoryModal').modal('show');
            }
        });

        $('#createCategoryForm').on('submit', function(e) {
            e.preventDefault();

            let categoryTitle = $('#categoryTitle').val();

            if (categoryTitle) {
                $.ajax({
                    method: 'POST',
                    url: '{{ route('admin.blog.createBlogCategoryQuick') }}', // Update with your actual API route
                    data: {
                        categoryTitle: categoryTitle,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            var newUser = {
                                id: response.category.id,
                                text: response.category.title
                            };

                            // Add the new category to the Select2 options and select it
                            var select = $(be + '_category');
                            var newOption = new Option(newUser.text, newUser.id, true, true);
                            select.append(newOption).trigger('change');

                            // Close the modal
                            $('#createCategoryModal').modal('hide');

                            // Reset the form
                            $('#createCategoryForm')[0].reset();
                        }
                    },
                    error: function(error) {
                        if (error.status === 422) {
                            let errors = error.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#' + key).addClass('is-invalid').nextAll('div.invalid-feedback').text(value);
                            });
                        } else {
                            $('#modal_danger .caption-text').html(error.responseJSON.message);
                            modalDanger.toggle();
                        }
                    }
                });
            }
        });

    } );
</script>
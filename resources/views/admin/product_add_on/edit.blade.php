<style>
    .ck-content ul {
      list-style-type: disc;
      margin-left: 20px;
    }
    
    /* Style for numbered lists inside CKEditor */
    .ck-content ol {
      list-style-type: decimal;
      margin-left: 20px;
    }
    
    /* Ensure list items have correct display inside CKEditor */
    .ck-content ul li, 
    .ck-content ol li {
      display: list-item;
    }
    
    /* Apply a minimum height to the CKEditor editable area */
    .ck-editor__editable_inline {
      min-height: 400px;
    }
</style>
<?php
$product_add_on_edit = 'product_add_on_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.product_add_ons' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->


<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
            
                <div class="mb-3">
                    <label class="form-label">{{ __( 'product_add_on.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $product_add_on_edit }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_add_on_edit }}_title" class="col-sm-5 form-label">{{ __( 'product_add_on.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $product_add_on_edit }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_add_on_edit }}_code" class="col-sm-5 form-label">{{ __( 'product_add_on.code' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $product_add_on_edit }}_code">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $product_add_on_edit }}_sku" class="col-sm-5 form-label">{{ __( 'product_add_on.sku' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $product_add_on_edit }}_sku">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_add_on_edit }}_description" class="col-sm-5 form-label">{{ __( 'product_add_on.description' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" name="{{ $product_add_on_edit }}_description" id="{{ $product_add_on_edit }}_description" rows="5"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_add_on_edit }}_price" class="col-sm-5 form-label">{{ __( 'product_add_on.price' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $product_add_on_edit }}_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_add_on_edit }}_discount_price" class="col-sm-5 form-label">{{ __( 'product_add_on.discount_price' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $product_add_on_edit }}_discount_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_add_on_edit }}_specification" class="col-sm-5 form-label">{{ __( 'product_add_on.specification' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" name="{{ $product_add_on_edit }}_specification" id="{{ $product_add_on_edit }}_specification" rows="5"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_add_on_edit }}_features" class="col-sm-5 form-label">{{ __( 'product_add_on.features' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" name="{{ $product_add_on_edit }}_features" id="{{ $product_add_on_edit }}_features" rows="5"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_add_on_edit }}_whats_included" class="col-sm-5 form-label">{{ __( 'product_add_on.whats_included' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" name="{{ $product_add_on_edit }}_whats_included" id="{{ $product_add_on_edit }}_whats_included" rows="5"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $product_add_on_edit }}_product" class="col-sm-5 form-label">{{ __( 'product_add_on.product' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $product_add_on_edit }}_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product_add_on.product' ) ] ) }}" multiple="multiple">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $product_add_on_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $product_add_on_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $product_add_on_edit }}',
                fileID = '';

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.product_add_on.index' ) }}';
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'products', $( fe + '_product' ).val() );
            formData.append( 'sku', $( fe + '_sku' ).val() );
            formData.append( 'title', $( fe + '_title' ).val() );
            formData.append( 'code', $( fe + '_code' ).val() );
            formData.append( 'description', $( fe + '_description' ).val() );
            formData.append( 'price', $( fe + '_price' ).val() );
            formData.append( 'discount_price', $( fe + '_discount_price' ).val() );
            formData.append( 'specification', $( fe + '_specification' ).val()  );
            formData.append( 'features', $( fe + '_features' ).val()  );
            formData.append( 'whats_included', $( fe + '_whats_included' ).val()  );
            
            formData.append( 'image', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.product_add_on.updateProductAddOn' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.product_add_on.index' ) }}';
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

        let productSelect2 = $( fe + '_product' ).select2( {
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.product.allProducts' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        title: params.term, // search term
                        start: params.page ? params.page : 0,
                        status: 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.products.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.title,
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
        
        getProductAddOn();
        Dropzone.autoDiscover = false;

        function getProductAddOn() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {

                url: '{{ route( 'admin.product_add_on.oneProductAddOn' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    
                    $( fe + '_sku' ).val( response.sku );
                    $( fe + '_title' ).val( response.title );
                    $( fe + '_code' ).val( response.code );
                    $( fe + '_description' ).val( response.description );
                    $( fe + '_price' ).val( response.price );
                    $( fe + '_discount_price' ).val( response.discount_price );
                    $( fe + '_specification' ).val( response.specification );
                    $( fe + '_features' ).val( response.features );
                    $( fe + '_whats_included' ).val( response.whats_included );

                    response.add_on_products.forEach((product, index) => {
                        let option1 = new Option( product.title, product.id, true, true );
                        productSelect2.append( option1 );
                        productSelect2.trigger( 'change' );
                    });

                    const dropzone = new Dropzone( fe + '_image', {
                        url: '{{ route( 'admin.file.upload' ) }}',
                        maxFiles: 10,
                        acceptedFiles: 'image/jpg,image/jpeg,image/png',
                        addRemoveLinks: true,
                        init: function() {

                            let that = this;
                            if ( response.image_path != 0 ) {
                                let myDropzone = that
                                    cat_id = '{{ request('id') }}',
                                    mockFile = { name: 'Default', size: 1024, accepted: true, id: cat_id };

                                myDropzone.files.push( mockFile );
                                myDropzone.displayExistingFile( mockFile, response.image_path );
                                $( myDropzone.files[myDropzone.files.length - 1].previewElement ).data( 'id', cat_id );
                            }
                        },
                        removedfile: function( file ) {
                            var idToRemove = file.id;

                            var idArrays = fileID.split(/\s*,\s*/);

                            var indexToRemove = idArrays.indexOf( idToRemove.toString() );
                            if (indexToRemove !== -1) {
                                idArrays.splice( indexToRemove, 1 );
                            }

                            fileID = idArrays.join( ', ' );

                            file.previewElement.remove();

                            removeGallery( idToRemove );

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

    } );
</script>
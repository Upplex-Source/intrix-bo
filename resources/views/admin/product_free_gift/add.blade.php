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
$product_free_gift_create = 'product_free_gift_create';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.product_free_gifts' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
            
                <div class="mb-3">
                    <label class="form-label">{{ __( 'product_free_gift.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $product_free_gift_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_free_gift_create }}_title" class="col-sm-5 form-label">{{ __( 'product_free_gift.title' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $product_free_gift_create }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_free_gift_create }}_code" class="col-sm-5 form-label">{{ __( 'product_free_gift.code' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $product_free_gift_create }}_code">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $product_free_gift_create }}_sku" class="col-sm-5 form-label">{{ __( 'product_free_gift.sku' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $product_free_gift_create }}_sku">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_free_gift_create }}_description" class="col-sm-5 form-label">{{ __( 'product_free_gift.description' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" name="{{ $product_free_gift_create }}_description" id="{{ $product_free_gift_create }}_description" rows="5"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_free_gift_create }}_price" class="col-sm-5 form-label">{{ __( 'product_free_gift.price' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $product_free_gift_create }}_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_free_gift_create }}_discount_price" class="col-sm-5 form-label">{{ __( 'product_free_gift.discount_price' ) }}</label>
                    <div class="col-sm-7">
                        <input type="number" class="form-control" id="{{ $product_free_gift_create }}_discount_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_free_gift_create }}_specification" class="col-sm-5 form-label">{{ __( 'product_free_gift.specification' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" name="{{ $product_free_gift_create }}_specification" id="{{ $product_free_gift_create }}_specification" rows="5"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_free_gift_create }}_features" class="col-sm-5 form-label">{{ __( 'product_free_gift.features' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" name="{{ $product_free_gift_create }}_features" id="{{ $product_free_gift_create }}_features" rows="5"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_free_gift_create }}_whats_included" class="col-sm-5 form-label">{{ __( 'product_free_gift.whats_included' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" name="{{ $product_free_gift_create }}_whats_included" id="{{ $product_free_gift_create }}_whats_included" rows="5"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $product_free_gift_create }}_product" class="col-sm-5 form-label">{{ __( 'product_free_gift.product' ) }}</label>
                    <div class="col-sm-7">
                        <select class="form-select" id="{{ $product_free_gift_create }}_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product_free_gift.product' ) ] ) }}" multiple="multiple">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="text-end">
                    <button id="{{ $product_free_gift_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $product_free_gift_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fc = '#{{ $product_free_gift_create }}',
                fileID = '';

        $( fc + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.product_free_gift.index' ) }}';
        } );

        $( fc + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'products', $( fc + '_product' ).val() );
            formData.append( 'sku', $( fc + '_sku' ).val() );
            formData.append( 'title', $( fc + '_title' ).val() );
            formData.append( '_code', $( fc + '_code' ).val() );
            formData.append( 'description', $( fc + '_description' ).val() );
            formData.append( 'price', $( fc + '_price' ).val() );
            formData.append( 'discount_price', $( fc + '_discount_price' ).val() );
            formData.append( 'specification', $( fc + '_specification' ).val()  );
            formData.append( 'features', $( fc + '_features' ).val()  );
            formData.append( 'whats_included', $( fc + '_whats_included' ).val()  );
            
            formData.append( 'image', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.product_free_gift.createProductFreeGift' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.product_free_gift.index' ) }}';
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

        $( fc + '_product' ).select2( {
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

        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone( fc + '_image', { 
            url: '{{ route( 'admin.file.upload' ) }}',
            maxFiles: 10,
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
    } );
</script>
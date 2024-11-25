<?php
$adjustment_edit = 'adjustment_edit';
?>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.edit_x', [ 'title' => Str::singular( __( 'template.adjustments' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
                <div class="mb-3 row">
                    <label for="{{ $adjustment_edit }}_adjustment_date" class="col-sm-5 col-form-label">{{ __( 'adjustment.adjustment_date' ) }}</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="{{ $adjustment_edit }}_adjustment_date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $adjustment_edit }}_warehouse" class="col-sm-5 form-label">{{ __( 'template.warehouses' ) }}</label>
                        <div class="col-sm-7">
                            <select class="form-select" id="{{ $adjustment_edit }}_warehouse" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.warehouses' ) ] ) }}">
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $adjustment_edit }}_product" class="col-sm-5 form-label">{{ __( 'template.products' ) }}</label>
                    <div class="col-sm-7">

                        <select class="form-select" id="{{ $adjustment_edit }}_product" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'template.products' ) ] ) }}" multiple="multiple">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="{{ $adjustment_edit }}_remarks" class="col-sm-5 col-form-label">{{ __( 'adjustment.remarks' ) }}</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" id="{{ $adjustment_edit }}_remarks"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label>{{ __( 'adjustment.attachment' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $adjustment_edit }}_attachment" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
            
                <label class="mb-3" >{{ __( 'template.products' ) }}</label>
                <table class="table table-bordered" id="product-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic rows will be added here -->
                    </tbody>
                </table>

                <div class="text-end">
                    <button id="{{ $adjustment_edit }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $adjustment_edit }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $adjustment_edit }}',
                fileID = '';

        $( fe + '_adjustment_date' ).flatpickr( {
            disableMobile: true,
        } );

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.adjustment.index' ) }}';
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request( 'id' ) }}' );
            formData.append( 'adjustment_date', $( fe + '_adjustment_date' ).val() );
            formData.append( 'remarks', $( fe + '_remarks' ).val() );
            formData.append( 'attachment', fileID );
            formData.append( 'products', $(fe + '_product').val() );
            formData.append( 'warehouse', $(fe + '_warehouse').val() );
            formData.append( '_token', '{{ csrf_token() }}' );
            let selectedProducts = $(fe + '_product').val();

            selectedProducts.forEach(function(productId,index) {
                let quantityInput = $(`#product-${productId} .product-quantity`).val();

                formData.append(`products[${index}][id]`, productId);
                formData.append(`products[${index}][quantity]`, quantityInput);
            });

            $.ajax( {
                url: '{{ route( 'admin.adjustment.updateAdjustment' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.adjustment.index' ) }}';
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

        getAdjustment();
        Dropzone.autoDiscover = false;

        let adjustmentDate = $( fe + '_adjustment_date' ).flatpickr( {
            disableMobile: true,
        } );

        let wareHouseSelect2 =$( fe + '_warehouse' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.warehouse.allWarehouses' ) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        custom_search: params.term, // search term
                        status: 10,
                        start: ( ( params.page ? params.page : 1 ) - 1 ) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.warehouses.map( function( v, i ) {
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
            },
        } );
        
        let productSelect2 = $(fe + '_product').select2({
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            closeOnSelect: true, // Auto close after selection
            ajax: {
                method: 'POST',
                url: '{{ route('admin.product.allProducts') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        name: params.term, // search term
                        status: 10,
                        start: ((params.page ? params.page : 1) - 1) * 10,
                        length: 10,
                        _token: '{{ csrf_token() }}',
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;

                    let processedResult = [];

                    data.products.map(function(v, i) {
                        processedResult.push({
                            id: v.id,
                            text: v.title,
                        });
                    });

                    return {
                        results: processedResult,
                        pagination: {
                            more: (params.page * 10) < data.recordsFiltered
                        }
                    };
                }
            }
        });

        function getAdjustment() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.adjustment.oneAdjustment' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {
                    
                    $( fe + '_remarks' ).val( response.remarks );
                    adjustmentDate.setDate( response.adjustment_date );

                    if ( response.warehouse ) {
                        let option1 = new Option( response.warehouse.title, response.warehouse.id, true, true );
                        wareHouseSelect2.append( option1 );
                        wareHouseSelect2.trigger( 'change' );
                    }

                    response.adjustment_metas.forEach(product => {
                    
                        let option = new Option(product.title, product.id, true, true); 
                        productSelect2.append(option);
                        console.log(product)
                        
                        // Check if an input field already exists for this product
                        if ($('#product-' + product.id).length === 0) {
                            // Append an input field for the selected product
                            console.log(response.menu_type)
                            quantityInputContainer.append(
                                `<div class="mb-2" id="product-${product.id}">
                                    <label>${product.name} Category dishes maximum quantity:</label>
                                    <input type="number" class="form-control product-quantity"
                                    style="width: 100px; display: inline;" min="1"
                                    data-product-id="${product.id}">
                                    <button type="button" class="btn btn-danger btn-sm remove-product" 
                                    data-product-id="${product.id}">Remove</button>
                                </div>`
                            );

                        }

                    });

                    const dropzone = new Dropzone( fe + '_attachment', {
                        url: '{{ route( 'admin.file.upload' ) }}',
                        maxFiles: 10,
                        acceptedFiles: 'attachment/jpg,attachment/jpeg,attachment/png',
                        addRemoveLinks: true,
                        init: function() {

                            let that = this;
                            console.log(response)
                            if ( response.attachment_path != 0 ) {
                                let myDropzone = that
                                    cat_id = '{{ request('id') }}',
                                    mockFile = { name: 'Default', size: 1024, accepted: true, id: cat_id };

                                myDropzone.files.push( mockFile );
                                myDropzone.displayExistingFile( mockFile, response.attachment_path );
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

        function removeGallery( gallery ) {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', gallery );
            formData.append( '_token', '{{ csrf_token() }}' );

            $.ajax( {
                url: '{{ route( 'admin.adjustment.removeAdjustmentAttachment' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();
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
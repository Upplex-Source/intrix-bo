<?php
$product_create = 'product_create';
$barcodes = $data['barcodes'];
$productTypes = $data['product_types'];
$salesUnits = $data['unit_types']['sale_unit'];
$purchaseUnits = $data['unit_types']['purchase_unit'];
$taxMethods = $data['tax_methods'];
$warehouses = $data['warehouses'];
?>

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

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.add_x', [ 'title' => Str::singular( __( 'template.products' ) ) ] ) }}</h3>
        </div><!-- .nk-block-head-content -->
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->

<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12  ">
                <h5 class="card-title mb-4">{{ __( 'template.general_info' ) }}</h5>
            
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_product_type" class="form-label">{{ __( 'product.product_type' ) }}</label>
                        <select class="form-select" id="{{ $product_create }}_product_type" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product.product_type' ) ] ) }}">
                            <option value="">{{ __('Select Product Type') }}</option>
                            @foreach ($productTypes as $key => $productType)
                                <option value="{{ $key }}">{{ $productType }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_title" class="form-label">{{ __( 'product.title' ) }}</label>
                        <input type="text" class="form-control" id="{{ $product_create }}_title">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_product_code" class="form-label">{{ __( 'product.product_code' ) }}</label>
                        <div class="d-flex">
                            <input type="text" class="form-control" id="{{ $product_create }}_product_code">
                            <div class="invalid-feedback"></div>
                            <button type="button" class="btn btn-outline-secondary ms-1" id="generate_product_code">
                                <i class="fas fa-sync-alt"></i> {{ __('Generate') }}
                            </button>
                        </div>
                    </div>
                </div>
            
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_barcode" class="form-label">{{ __( 'product.barcode' ) }}</label>
                        <select class="form-select" id="{{ $product_create }}_barcode" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product.barcode' ) ] ) }}">
                            <option value="">{{ __('Select Barcode Type') }}</option>
                            @foreach ($barcodes as $barcode)
                                <option value="{{ $barcode }}">{{ $barcode }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_brand" class="form-label">{{ __( 'product.brand' ) }}</label>
                        <select class="form-select" id="{{ $product_create }}_brand" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product.brand' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_category" class="form-label">{{ __( 'product.category' ) }}</label>
                        <select class="form-select" id="{{ $product_create }}_category" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product.category' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_supplier" class="form-label">{{ __( 'product.supplier' ) }}</label>
                        <select class="form-select" id="{{ $product_create }}_supplier" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product.supplier' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_unit" class="form-label">{{ __( 'product.unit' ) }}</label>
                        <select class="form-select" id="{{ $product_create }}_unit" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product.unit' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_workmanship" class="form-label">{{ __( 'product.workmanship' ) }}</label>
                        <select class="form-select" id="{{ $product_create }}_workmanship" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product.workmanship' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            
                <h5 class="card-title mt-4 mb-4">{{ __( 'template.stock_info' ) }}</h5>

                <div class="row mb-3">
                    
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_address_1" class="form-label">{{ __( 'product.address_1' ) }}</label>
                        <input type="text" class="form-control" id="{{ $product_create }}_address_1">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_address_2" class="form-label">{{ __( 'product.address_2' ) }}</label>
                        <input type="text" class="form-control" id="{{ $product_create }}_address_2">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_city" class="form-label">{{ __( 'product.city' ) }}</label>
                        <input type="text" class="form-control" id="{{ $product_create }}_city">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            
                <div class="row mb-3">
                    
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_state" class="form-label">{{ __( 'product.state' ) }}</label>
                        <input type="text" class="form-control" id="{{ $product_create }}_state">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_postcode" class="form-label">{{ __( 'product.postcode' ) }}</label>
                        <input type="text" class="form-control" id="{{ $product_create }}_postcode">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_sale_unit" class="form-label">{{ __( 'product.sale_unit' ) }}</label>
                        <select class="form-select" id="{{ $product_create }}_sale_unit" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product.sale_unit' ) ] ) }}">
                            <option value="">{{ __('Select Sales Units Type') }}</option>
                            @foreach ($salesUnits as $salesUnit)
                                <option value="{{ $salesUnit }}">{{ $salesUnit }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            
                <div class="row mb-3">
                    
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_purchase_unit" class="form-label">{{ __( 'product.purchase_unit' ) }}</label>
                        <select class="form-select" id="{{ $product_create }}_purchase_unit" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product.purchase_unit' ) ] ) }}">
                            <option value="">{{ __('Select Purchase Units Type') }}</option>
                            @foreach ($purchaseUnits as $purchaseUnit)
                                <option value="{{ $purchaseUnit }}">{{ $purchaseUnit }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_cost" class="form-label">{{ __( 'product.cost' ) }}</label>
                        <input type="number" class="form-control" id="{{ $product_create }}_cost">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_price" class="form-label">{{ __( 'product.price' ) }}</label>
                        <input type="number" class="form-control" id="{{ $product_create }}_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            
                <div class="row mb-3">
                    
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_alert_quantity" class="form-label">{{ __( 'product.alert_quantity' ) }}</label>
                        <input type="number" class="form-control" id="{{ $product_create }}_alert_quantity">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_quantity" class="form-label">{{ __( 'product.quantity' ) }}</label>
                        <input type="number" class="form-control" id="{{ $product_create }}_quantity">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_tax_method" class="form-label">{{ __( 'product.tax_method' ) }}</label>
                        <select class="form-select" id="{{ $product_create }}_tax_method" data-placeholder="{{ __( 'datatables.select_x', [ 'title' => __( 'product.tax_method' ) ] ) }}">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            
                <div class="mb-3">
                    <label>{{ __( 'product.image' ) }}</label>
                    <div class="dropzone mb-3" id="{{ $product_create }}_image" style="min-height: 0px;">
                        <div class="dz-message needsclick">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __( 'template.drop_file_or_click_to_upload' ) }}</h3>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $product_create }}_description" class="col-sm-3 col-form-label">{{ __( 'product.description' ) }}</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="{{ $product_create }}_description" style="min-height: 80px;" placeholder="{{ __( 'template.optional' ) }}"></textarea>
                        <div class="i\nvalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_create }}_featured" class="col-sm-5 col-form-label">{{ __( 'product.featured' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $product_create }}_featured" name="{{ $product_create }}_featured" value="1" onchange="this.nextElementSibling.textContent = this.checked ? '{{ __('Enabled') }}' : '{{ __('Disabled') }}';">
                            <label class="form-check-label" for="{{ $product_create }}_featured">{{ __('Disabled') }}</label>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $product_create }}_has_warehouse" class="col-sm-5 col-form-label">{{ __( 'product.has_warehouse' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $product_create }}_has_warehouse" name="{{ $product_create }}_has_warehouse" value="1" onchange="this.nextElementSibling.textContent = this.checked ? '{{ __('Enabled') }}' : '{{ __('Disabled') }}';">
                            <label class="form-check-label" for="{{ $product_create }}_has_warehouse">{{ __('Disabled') }}</label>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row d-none" id="{{ $product_create }}_has_warehouse_input">
                    @foreach ($warehouses as $warehouse)
                        <div class="col-sm-4 warehouse-group">
                            <label for="{{ $product_create }}_warehouse_title_{{ $warehouse->id }}" class="form-label">{{ $warehouse->title }} Price:</label>
                            <input type="text" class="form-control" id="{{ $product_create }}_warehouse_title_{{ $warehouse->id }}" name="{{ $product_create }}_warehouse_price[]">
                            <div class="invalid-feedback"></div>
                        </div>
                    @endforeach
                </div>

                <div class="mb-3 row">
                    <label for="{{ $product_create }}_has_imei" class="col-sm-5 col-form-label">{{ __( 'product.has_imei' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $product_create }}_has_imei" name="{{ $product_create }}_has_imei" value="1" onchange="this.nextElementSibling.textContent = this.checked ? '{{ __('Enabled') }}' : '{{ __('Disabled') }}';">
                            <label class="form-check-label" for="{{ $product_create }}_has_imei">{{ __('Disabled') }}</label>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none"id="{{ $product_create }}_has_imei_input">
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_imei" class="form-label">{{ __( 'product.imei' ) }}</label>
                        <input type="text" class="form-control" id="{{ $product_create }}_imei">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="{{ $product_create }}_serial_number" class="form-label">{{ __('product.serial_number') }}</label>
                        <input type="text" class="form-control" id="{{ $product_create }}_serial_number">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $product_create }}_has_variant" class="col-sm-5 col-form-label">{{ __( 'product.has_variant' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $product_create }}_has_variant" name="{{ $product_create }}_has_variant" value="1" onchange="this.nextElementSibling.textContent = this.checked ? '{{ __('Enabled') }}' : '{{ __('Disabled') }}';">
                            <label class="form-check-label" for="{{ $product_create }}_has_variant">{{ __('Disabled') }}</label>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Variant Input Section -->
                <div class="mb-3 row d-none" id="{{ $product_create }}_has_variant_input">
                    <div class="variant-container">
                        <div class="variant-input-group row mb-2">
                            <div class="col-sm-3">
                                <label class="form-label">{{ __('product.variant_name') }}</label>
                                <input type="text" class="form-control" name="{{ $product_create }}_variant_name[]">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label">{{ __('product.variant_price') }}</label>
                                <input type="number" class="form-control" name="{{ $product_create }}_variant_price[]">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label">{{ __('product.variant_quantity') }}</label>
                                <input type="number" class="form-control" name="{{ $product_create }}_variant_quantity[]">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-sm-3 d-flex align-items-end">
                                <button type="button" class="variant_add btn btn-success me-2">+</button>
                                <button type="button" class="variant_remove btn btn-danger">-</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="{{ $product_create }}_has_promotion" class="col-sm-5 col-form-label">{{ __( 'product.has_promotion' ) }}</label>
                    <div class="col-sm-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $product_create }}_has_promotion" name="{{ $product_create }}_has_promotion" value="1" onchange="this.nextElementSibling.textContent = this.checked ? '{{ __('Enabled') }}' : '{{ __('Disabled') }}';">
                            <label class="form-check-label" for="{{ $product_create }}_has_promotion">{{ __('Disabled') }}</label>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="mb-3 row d-none" id="{{ $product_create }}_has_promotion_input">
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.promotion_start') }}</label>
                        <input type="text" class="form-control" name="{{ $product_create }}_promotion_start" id="{{ $product_create }}_promotion_start">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.promotion_end') }}</label>
                        <input type="text" class="form-control" name="{{ $product_create }}_promotion_end" id="{{ $product_create }}_promotion_end">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.promotion_price') }}</label>
                        <input type="number" class="form-control" name="{{ $product_create }}_promotion_price" id="{{ $product_create }}_promotion_price">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                
                <div class="text-end">
                    <button id="{{ $product_create }}_cancel" type="button" class="btn btn-outline-secondary">{{ __( 'template.cancel' ) }}</button>
                    &nbsp;
                    <button id="{{ $product_create }}_submit" type="button" class="btn btn-primary">{{ __( 'template.save_changes' ) }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset( 'admin/css/ckeditor/styles.css' ) }}">
<script src="{{ asset( 'admin/js/ckeditor/ckeditor.js' ) }}"></script>
<script src="{{ asset( 'admin/js/ckeditor/upload-adapter.js' ) }}"></script>

<script>
window.ckeupload_path = '{{ route( 'admin.product.ckeUpload' ) }}';
window.csrf_token = '{{ csrf_token() }}';
window.cke_element1 = 'product_create_description';
</script>

<script src="{{ asset( 'admin/js/ckeditor/ckeditor-init.js' ) }}"></script>


<script>
    document.addEventListener( 'DOMContentLoaded', function() {

        let fe = '#{{ $product_create }}',
                fileID = '';

        $( fe + '_cancel' ).click( function() {
            window.location.href = '{{ route( 'admin.module_parent.product.index' ) }}';
        } );

        $( '#generate_product_code' ).click( function() {

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            $.ajax( {
                url: '{{ route( 'admin.product.generateProductCode' ) }}',
                type: 'POST',
                data: {
                    'id': '{{ request( 'id' ) }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function( response ) {

                    $( fe + '_product_code' ).val( response.product_code );

                    $( 'body' ).loading( 'stop' );
                },
            } );
        } );

        $( fe + '_submit' ).click( function() {

            resetInputValidation();

            $( 'body' ).loading( {
                message: '{{ __( 'template.loading' ) }}'
            } );

            let formData = new FormData();
            formData.append( 'id', '{{ request('id') }}' );
            formData.append( 'product_type', $( fe + '_product_type' ).val() );
            formData.append( 'product_code', $( fe + '_product_code' ).val() );
            formData.append( 'title', $( fe + '_title' ).val() );
            formData.append( 'unit', $( fe + '_unit' ).val() );
            formData.append( 'barcode', $( fe + '_barcode' ).val() );
            formData.append( 'brand', $( fe + '_brand' ).val() );
            formData.append( 'supplier', $( fe + '_supplier' ).val() );
            formData.append( 'category', $( fe + '_category' ).val() );
            formData.append( 'workmanship', $( fe + '_workmanship' ).val() );
            formData.append( 'address_1', $( fe + '_address_1' ).val() );
            formData.append( 'address_2', $( fe + '_address_2' ).val() );
            formData.append( 'city', $( fe + '_city' ).val() );
            formData.append( 'state', $( fe + '_state' ).val() );
            formData.append( 'city', $( fe + '_city' ).val() );
            formData.append( 'postcode', $( fe + '_postcode' ).val() );
            formData.append( 'sale_unit', $( fe + '_sale_unit' ).val() );
            formData.append( 'purchase_unit', $( fe + '_purchase_unit' ).val() );
            formData.append( 'cost', $( fe + '_cost' ).val() );
            formData.append( 'price', $( fe + '_price' ).val() );
            formData.append( 'alert_quantity', $( fe + '_alert_quantity' ).val() );
            formData.append( 'quantity', $( fe + '_quantity' ).val() );
            formData.append( 'tax_method', $( fe + '_tax_method' ).val() );
            formData.append( 'description', JSON.stringify(editor.getData()) );
            console.log(JSON.stringify(editor.getData()))
            console.log(editor.getData())
            formData.append( 'featured', $(fe + '_featured').is(':checked') ? 1 : 0);

            if ($(fe + '_has_warehouse').is(':checked')) {
                $('.warehouse-group').each(function(index, element) {
                    // Get the warehouse ID from the input's ID attribute
                    var warehouseId = $(element).find('input').attr('id').split('_').pop();
                    
                    // Get the value from the current warehouse input
                    var warehousePrice = $(element).find('input').val();

                    // Append the warehouse ID and price to FormData with structured keys
                    formData.append(`warehouse[${warehouseId}][price]`, warehousePrice);
                });
            }

            if ($(fe + '_has_imei').is(':checked')) {
                formData.append( 'imei', $( fe + '_imei' ).val() );
                formData.append( 'serial_number', $( fe + '_serial_number' ).val() );
            }

            formData.append( 'image', fileID );
            formData.append( '_token', '{{ csrf_token() }}' );

            if ($(fe + '_has_variant').is(':checked')) {

                $('.variant-input-group').each(function(index, element) {
                    // Get values from the current variant input group

                    var variantName = $(element).find('input[name="{{ $product_create }}_variant_name[]"]').val();
                    var variantPrice = $(element).find('input[name="{{ $product_create }}_variant_price[]"]').val();
                    var variantQuantity = $(element).find('input[name="{{ $product_create }}_variant_quantity[]"]').val();
                    var variantId = $(element).find('input[name="{{ $product_create }}_variant_id[]"]').val() ?? 0;
                    
                    // Append them to formData with a structured key
                    formData.append(`variants[${index}][name]`, variantName);
                    formData.append(`variants[${index}][price]`, variantPrice);
                    formData.append(`variants[${index}][quantity]`, variantQuantity);
                    formData.append(`variants[${index}][id]`, variantId);
                });
            }

            formData.append( 'has_promotion', $(fe + '_has_promotion').is(':checked') ? 1 : 0);
            formData.append( 'promotion_start', $( fe + '_promotion_start' ).val() );
            formData.append( 'promotion_end', $( fe + '_promotion_end' ).val() );
            formData.append( 'promotion_price', $( fe + '_promotion_price' ).val() );

            $.ajax( {
                url: '{{ route( 'admin.product.updateProduct' ) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType:   false,
                success: function( response ) {
                    $( 'body' ).loading( 'stop' );
                    $( '#modal_success .caption-text' ).html( response.message );
                    modalSuccess.toggle();

                    document.getElementById( 'modal_success' ).addEventListener( 'hidden.bs.modal', function (event) {
                        window.location.href = '{{ route( 'admin.module_parent.product.index' ) }}';
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

        let brandSelect2 = $( fe + '_brand' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.brand.allBrands' ) }}',
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

                    data.brands.map( function( v, i ) {
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
        
        let categorySelect2 = $( fe + '_category' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.category.allCategories' ) }}',
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

                    data.categories.map( function( v, i ) {
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
        
        let supplierSelect2 = $( fe + '_supplier' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.supplier.allSuppliers' ) }}',
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

                    data.suppliers.map( function( v, i ) {
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
        
        let unitSelect2 = $( fe + '_unit' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.unit.allUnits' ) }}',
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

                    data.units.map( function( v, i ) {
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
        
        let workmanShipSelect2 = $( fe + '_workmanship' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.workmanship.allWorkmanships' ) }}',
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

                    data.workmanships.map( function( v, i ) {
                        processedResult.push( {
                            id: v.id,
                            text: v.fullname,
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
        
        let taxMethodSelect2 = $( fe + '_tax_method' ).select2( {
            language: '{{ App::getLocale() }}',
            theme: 'bootstrap-5',
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
            ajax: {
                method: 'POST',
                url: '{{ route( 'admin.tax_method.allTaxMethods' ) }}',
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

                    data.tax_methods.map( function( v, i ) {
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

        const toggles = document.querySelectorAll('.form-check-input');

        toggles.forEach(function(toggle) {
            // Attach change event listener
            toggle.addEventListener('change', function() {
                const inputField = document.getElementById(`${toggle.id}_input`);
                if (toggle.checked) {
                    inputField.classList.remove('d-none'); // Remove the 'hidden' class to show the input field
                } else {
                    inputField.classList.add('d-none'); // Add the 'hidden' class to hide the input field
                }
            });
        });

        document.querySelectorAll('.variant_add').forEach(function(button) {
            button.onclick = function() {
                const container = button.closest('.variant-container');
                const newGroup = document.createElement('div');
                newGroup.className = 'variant-input-group row mb-2';
                newGroup.innerHTML = `
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.variant_name') }}</label>
                        <input type="text" class="form-control" name="{{ $product_create }}_variant_name[]">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.variant_price') }}</label>
                        <input type="number" class="form-control" name="{{ $product_create }}_variant_price[]">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.variant_quantity') }}</label>
                        <input type="number" class="form-control" name="{{ $product_create }}_variant_quantity[]">
                    </div>
                    <div class="col-sm-3 d-flex align-items-end">
                        <button type="button" class="btn btn-success me-2 variant_add">+</button>
                        <button type="button" class="btn btn-danger variant_remove">-</button>
                    </div>`;
                container.appendChild(newGroup);

                // Re-attach event listeners to new buttons
                attachVariantEventListeners();
            };
        });

        document.querySelectorAll('.variant_remove').forEach(function(button) {
            button.onclick = function() {
                const group = button.closest('.variant-input-group');
                if (group && group.parentNode.childElementCount > 1) {
                    group.remove();
                }
            };
        });

    function attachVariantEventListeners() {
        document.querySelectorAll('.variant_add').forEach(function(button) {
            button.onclick = function() {
                const container = button.closest('.variant-container');
                const newGroup = document.createElement('div');
                newGroup.className = 'variant-input-group row mb-2';
                newGroup.innerHTML = `
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.variant_name') }}</label>
                        <input type="text" class="form-control" name="{{ $product_create }}_variant_name[]">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.variant_price') }}</label>
                        <input type="number" class="form-control" name="{{ $product_create }}_variant_price[]">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.variant_quantity') }}</label>
                        <input type="number" class="form-control" name="{{ $product_create }}_variant_quantity[]">
                    </div>
                    <div class="col-sm-3 d-flex align-items-end">
                        <button type="button" class="btn btn-success me-2 variant_add">+</button>
                        <button type="button" class="btn btn-danger variant_remove">-</button>
                    </div>`;
                container.appendChild(newGroup);

                // Re-attach event listeners to new buttons
                attachVariantEventListeners();
            };
        });

        document.querySelectorAll('.variant_remove').forEach(function(button) {
            button.onclick = function() {
                const group = button.closest('.variant-input-group');
                if (group && group.parentNode.childElementCount > 1) {
                    group.remove();
                }
            };
        });
    }

    let promotionStart = $( fe + '_promotion_start' ).flatpickr( {
        disableMobile: true,
    } );

    let promotionEnd = $( fe + '_promotion_end' ).flatpickr( {
        disableMobile: true,
    } );

    Dropzone.autoDiscover = false;

    getProduct();

    function getProduct() {

        $( 'body' ).loading( {
            message: '{{ __( 'template.loading' ) }}'
        } );

        $.ajax( {
            url: '{{ route( 'admin.product.oneProduct' ) }}',
            type: 'POST',
            data: {
                'id': '{{ request( 'id' ) }}',
                '_token': '{{ csrf_token() }}'
            },
            success: function( response ) {

                if ( response.brand.title ) {
                    let option1 = new Option( response.brand.title, response.brand.id, true, true );
                    brandSelect2.append( option1 );
                    brandSelect2.trigger( 'change' );
                }

                if ( response.categories[0].title ) {
                    let option1 = new Option( response.categories[0].title, response.categories[0].id, true, true );
                    categorySelect2.append( option1 );
                    categorySelect2.trigger( 'change' );
                }

                if ( response.supplier.title ) {
                    let option1 = new Option( response.supplier.title, response.supplier.id, true, true );
                    supplierSelect2.append( option1 );
                    supplierSelect2.trigger( 'change' );
                }

                if ( response.unit.title ) {
                    let option1 = new Option( response.unit.title, response.unit.id, true, true );
                    unitSelect2.append( option1 );
                    unitSelect2.trigger( 'change' );
                }

                if ( response.workmanship.fullname ) {
                    let option1 = new Option( response.workmanship.fullname, response.workmanship.id, true, true );
                    workmanShipSelect2.append( option1 );
                    workmanShipSelect2.trigger( 'change' );
                }

                if ( response.tax_method.title ) {
                    let option1 = new Option( response.tax_method.title, response.tax_method.id, true, true );
                    taxMethodSelect2.append( option1 );
                    taxMethodSelect2.trigger( 'change' );
                }

                $( fe + '_product_type' ).val( response.type );
                $( fe + '_product_code' ).val( response.product_code );
                $( fe + '_title' ).val( response.title );
                $( fe + '_barcode' ).val( response.barcode_symbology );
                $( fe + '_address_2' ).val( response.address_2 );
                $( fe + '_address_1' ).val( response.address_1 );
                $( fe + '_postcode' ).val( response.postcode );
                $( fe + '_state' ).val( response.state );
                $( fe + '_city' ).val( response.city );
                $( fe + '_sale_unit' ).val( response.sale_unit );
                $( fe + '_purchase_unit' ).val( response.purchase_unit );
                $( fe + '_cost' ).val( response.cost );
                $( fe + '_price' ).val( response.price );
                $( fe + '_quantity' ).val( response.quantity );
                $( fe + '_alert_quantity' ).val( response.alert_quantity );
                $( fe + '_imei' ).val( response.imei );
                $( fe + '_serial_number' ).val( response.serial_number );
                $( fe + '_promotion_price' ).val( response.promotional_price );

                promotionStart.setDate( response.promotion_start );
                promotionEnd.setDate( response.promotion_end );

                if (response.featured == 1) {
                    $(fe + '_featured').prop('checked', true);
                    $(fe + '_featured').next('label').text('{{ __('Enabled') }}');
                } 
                if (response.warehouses.length > 0) {

                    response.warehouses.forEach(warehouse => {
                        // Find the corresponding input field for the warehouse
                        const warehouseInput = document.querySelector(`#product_create_warehouse_title_${warehouse.id}`);

                        if (warehouseInput) {
                            // Set the price from the response into the input field
                            warehouseInput.value = warehouse.pivot.price;
                        }
                    });
                    $(fe + '_has_warehouse_input').removeClass('d-none')
                    $(fe + '_has_warehouse').prop('checked', true);
                    $(fe + '_has_warehouse').next('label').text('{{ __('Enabled') }}');
                } 
                if (response.imei || response.serial_number) {
                    $(fe + '_has_imei_input').removeClass('d-none')
                    $(fe + '_has_imei').prop('checked', true);
                    $(fe + '_has_imei').next('label').text('{{ __('Enabled') }}');
                }

                if (response.variants.length > 0) {
                    $(fe + '_has_variant').prop('checked', true);
                    $(fe + '_has_variant').next('label').text('{{ __('Enabled') }}');
                    $(fe + '_has_variant_input').removeClass('d-none')
                    populateVariants(response.variants);
                }

                if (response.promotional_price) {
                    $(fe + '_has_promotion_input').removeClass('d-none')
                    $(fe + '_has_promotion').prop('checked', true);
                    $(fe + '_has_promotion').next('label').text('{{ __('Enabled') }}');
                } 

                if (response.description) {
                    editor.setData( response.description );
                }

                const dropzone = new Dropzone( fe + '_image', {
                    url: '{{ route( 'admin.file.upload' ) }}',
                    maxFiles: 10,
                    acceptedFiles: 'image/jpg,image/jpeg,image/png',
                    addRemoveLinks: true,
                    init: function() {

                        let that = this;

                        if ( response.galleries.length != 0 ) {

                            $.each( response.galleries, function( i, v ) {

                                let myDropzone = that,
                                    mockFile = { name: 'Default', size: 1024, accepted: true, id: v.id, isOld: true };

                                myDropzone.files.push( mockFile );
                                myDropzone.displayExistingFile( mockFile, v.image_path );
                                $( myDropzone.files[myDropzone.files.length - 1].previewElement ).data( 'id', v.id );
                                    
                            } );
                        }
                    },
                    removedfile: function( file ) {

                        var idToRemove = file.previewElement.id;

                        if (typeof file.isOld !== 'undefined' && file.isOld === true) {
                            var idToRemove = file.id;
                        }

                        var idArrays = fileID.split(/\s*,\s*/);

                        var indexToRemove = idArrays.indexOf( idToRemove.toString() );
                        if (indexToRemove !== -1) {
                            idArrays.splice( indexToRemove, 1 );
                        }

                        fileID = idArrays.join( ', ' );

                        if (typeof file.isOld !== 'undefined' && file.isOld === true) {
                            removeGallery(idToRemove);
                        }

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

                $( 'body' ).loading( 'stop' );
            },
        } );
    }

    // Function to populate variants from the response
    function populateVariants(variants) {
        const container = document.querySelector('.variant-container'); // The container where variants should be added
        if (container) {
            // Clear any existing variant groups (optional if you want to reset the list)
            container.innerHTML = ''; 

            variants.forEach(variant => {
                const newGroup = document.createElement('div');
                newGroup.className = 'variant-input-group row mb-2';
                newGroup.innerHTML = `
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.variant_name') }}</label>
                        <input type="text" class="form-control" name="{{ $product_create }}_variant_name[]" value="${variant.title}">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.variant_price') }}</label>
                        <input type="number" class="form-control" name="{{ $product_create }}_variant_price[]" value="${variant.price}">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.variant_quantity') }}</label>
                        <input type="number" class="form-control" name="{{ $product_create }}_variant_quantity[]" value="${variant.quantity}">
                    </div>
                        <input type="hidden" class="form-control" name="{{ $product_create }}_variant_id[]" value="${variant.id}">
                    <div class="col-sm-3 d-flex align-items-end">
                        <button type="button" class="btn btn-success me-2 variant_add">+</button>
                        <button type="button" class="btn btn-danger variant_remove">-</button>
                    </div>`;

                container.appendChild(newGroup);
            });

            // Re-attach event listeners to new buttons
            attachVariantEventListeners();
        }
    }

    // Re-attach event listeners after new variants are added
    function attachVariantEventListeners() {
        document.querySelectorAll('.variant_add').forEach(function (button) {
            button.onclick = function () {
                const container = button.closest('.variant-container');
                const newGroup = document.createElement('div');
                newGroup.className = 'variant-input-group row mb-2';
                newGroup.innerHTML = `
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.variant_name') }}</label>
                        <input type="text" class="form-control" name="{{ $product_create }}_variant_name[]">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.variant_price') }}</label>
                        <input type="number" class="form-control" name="{{ $product_create }}_variant_price[]">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">{{ __('product.variant_quantity') }}</label>
                        <input type="number" class="form-control" name="{{ $product_create }}_variant_quantity[]">
                    </div>
                    <div class="col-sm-3 d-flex align-items-end">
                        <button type="button" class="btn btn-success me-2 variant_add">+</button>
                        <button type="button" class="btn btn-danger variant_remove">-</button>
                    </div>`;

                container.appendChild(newGroup);

                // Re-attach event listeners to new buttons
                attachVariantEventListeners();
            };
        });

        document.querySelectorAll('.variant_remove').forEach(function (button) {
            button.onclick = function () {
                const group = button.closest('.variant-input-group');
                if (group && group.parentNode.childElementCount > 1) {
                    group.remove();
                }
            };
        });
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
            url: '{{ route( 'admin.product.removeProductGalleryImage' ) }}',
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
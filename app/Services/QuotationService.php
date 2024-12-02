<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
    Mail,
};

use App\Mail\QuotationMail;

use Helper;

use App\Models\{
    Company,
    Customer,
    Administrator,
    Quotation,
    QuotationMeta,
    Booking,
    FileManager,
    Product,
    SalesOrder,
    SalesOrderMeta,
    ProductVariant,
    TaxMethod,
    Bundle,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class QuotationService
{

    public static function createQuotation( $request ) {

        $validator = Validator::make( $request->all(), [
            'remarks' => [ 'nullable' ],
            'attachment' => [ 'nullable' ],
            'products' => [ 'nullable' ],
            'warehouse' => [ 'nullable', 'exists:warehouses,id' ],
            'products.*.id' => [ 'nullable',  function ($attribute, $value, $fail) {
                    if (!preg_match('/^(product|bundle|variant)-(\d+)$/', $value, $matches)) {
                        return $fail("The {$attribute} format is invalid.");
                    }
        
                    $type = $matches[1];
                    $identifier = $matches[2];
        
                    // Check if the identifier exists in the corresponding table
                    if ($type === 'product' && !\DB::table('products')->where('id', $identifier)->exists()) {
                        return $fail("The {$attribute} does not exist in products.");
                    } elseif ($type === 'bundle' && !\DB::table('bundles')->where('id', $identifier)->exists()) {
                        return $fail("The {$attribute} does not exist in bundles.");
                    } elseif ($type === 'variant' && !\DB::table('product_variants')->where('id', $identifier)->exists()) {
                        return $fail("The {$attribute} does not exist in bundles.");
                    }
                },
            ],
            'products.*.quantity' => [
                'nullable',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1]; // Extract the product index
                    $productId = $request->input("products.{$index}.id");

                    if (!preg_match('/^(product|bundle|variant)-(\d+)$/', $productId, $matches)) {
                        return $fail("The ID format is invalid for {$attribute}.");
                    }

                    $type = $matches[1];
                    $identifier = $matches[2];

                    // Validate the quantity against the stock in the respective warehouse table
                    $availableStock = 0;

                    if ($type === 'product') {
                        $availableStock = \DB::table('warehouses_products')
                            ->where('warehouse_id', $request->warehouse)
                            ->where('product_id', $identifier)
                            ->sum('quantity');

                        $name = 'Product: ' . Product::find($identifier)->value('title');

                    } elseif ($type === 'bundle') {
                        $availableStock = \DB::table('warehouses_bundles')
                            ->where('warehouse_id', $request->warehouse)
                            ->where('bundle_id', $identifier)
                            ->sum('quantity');

                        $name = 'Bundle: ' . Bundle::find($identifier)->value('title');

                    } elseif ($type === 'variant') {
                        $availableStock = \DB::table('warehouses_variants')
                            ->where('warehouse_id', $request->warehouse)
                            ->where('variant_id', $identifier)
                            ->sum('quantity');

                        $name = 'Variant: ' . ProductVariant::find($identifier)->value('title');

                    }

                    if ($value > $availableStock) {
                        return $fail(" The requested quantity for {$name} exceeds available stock ({$availableStock} stocks available).");
                    }
                },
            ],
            'supplier' => [ 'nullable', 'exists:suppliers,id' ],
            'salesman' => [ 'nullable', 'exists:administrators,id' ],
            'customer' => [ 'nullable', 'exists:users,id' ],
            'discount' => [ 'nullable', 'numeric' ,'min:0' ],
            'shipping_cost' => [ 'nullable', 'numeric' ,'min:0' ],
            'tax_method' => [ 'nullable', 'exists:tax_methods,id' ],

        ] );

        $attributeName = [
            'title' => __( 'quotation.title' ),
            'description' => __( 'quotation.description' ),
            'image' => __( 'quotation.image' ),
            'thumbnail' => __( 'quotation.thumbnail' ),
            'url_slug' => __( 'quotation.url_slug' ),
            'structure' => __( 'quotation.structure' ),
            'size' => __( 'quotation.size' ),
            'phone_number' => __( 'quotation.phone_number' ),
            'sort' => __( 'quotation.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $amount = 0;
            $originalAmount = 0;
            $paidAmount = 0;
            $finalAmount = 0;
            $taxAmount = 0;

            if( $request->products ) {

                $products = $request->products;

                foreach( $products as $product ){

                    preg_match('/^(product|bundle|variant)-(\d+)$/', $product['id'], $matches);

                    $type = $matches[1];
                    $identifier = $matches[2];

                    switch ($type) {
                        case 'product':
                            $productData = Product::find( $identifier );
                            // turnoff warehouse price
                            if( count( $productData->warehouses ) > 0 ){
                                $warehouseProduct = $productData->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                                $amount += $warehouseProduct->pivot->price > 0 ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->price * $product['quantity'];
                            } else {
                                $amount += $productData->price * $product['quantity'];
                            }
                            break;

                        case 'variant':
                            $productData = ProductVariant::find( $identifier );
                            // turnoff warehouse price
                            if( count( $productData->product->warehouses ) > 0 ){
                                $warehouseProduct = $productData->product->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                                $amount += $warehouseProduct->pivot->price > 0 ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->product->price * $product['quantity'];
                            } else {
                                $amount += $productData->product->price * $product['quantity'];
                            }
                            break;

                        case 'bundle':
                            $productData = Bundle::find( $identifier );
                            // turnoff warehouse price
                            $amount += $productData->price * $product['quantity'];
                            break;
                        
                        default:
                            $productData = Product::find( $identifier );
                            // turnoff warehouse price
                            if( count( $productData->warehouses ) > 0 ){
                                $warehouseProduct = $productData->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                                $amount += $warehouseProduct->pivot->price > 0 ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->price * $product['quantity'];
                            } else {
                                $amount += $productData->price * $product['quantity'];
                            }
                            break;
                    }

                }
            }

            // $taxAmount = $amount * Helper::taxTypes()[$request->tax_type ?? 1]['percentage'];\
            $taxAmount = $amount * TaxMethod::find( $request->tax_method )->formatted_percentage;
            $finalAmount = $amount - $request->discount + $taxAmount;

            $quotationCreate = Quotation::create([
                'supplier_id' => $request->supplier,
                'warehouse_id' => $request->warehouse,
                'salesman_id' => $request->salesman,
                'customer_id' => $request->customer,
                'remarks' => $request->remarks,
                'reference' => Helper::generateQuotationNumber(),
                'tax_type' => 1,
                // 'tax_method_id' => $request->tax_method,
                'amount' => $amount,
                'original_amount' => $amount,
                'paid_amount' => $paidAmount,
                'final_amount' => $amount,
                'order_tax' => $taxAmount,
                'order_discount' => $request->discount,
                'shipping_cost' => $request->shipping_cost,
                'tax_method_id' => $request->tax_method,
                'status' => 10,
            ]);

            $attachment = explode( ',', $request->attachment );
            $attachmentFiles = FileManager::whereIn( 'id', $attachment )->get();

            if ( $attachmentFiles ) {
                foreach ( $attachmentFiles as $attachmentFile ) {

                    $fileName = explode( '/', $attachmentFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'quotation/' . $quotationCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $quotationCreate->attachment = $target;
                   $quotationCreate->save();

                    $attachmentFile->status = 10;
                    $attachmentFile->save();

                }
            }

            $products = $request->products;

            if( $products ){
                foreach( $products as $product ){

                    preg_match('/^(product|bundle|variant)-(\d+)$/', $product['id'], $matches);

                    $type = $matches[1];
                    $identifier = $matches[2];

                    switch ($type) {
                        case 'product':
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, $product['id'], $product['quantity'], true  );
                            break;

                        case 'variant':
                            $productVariant = ProductVariant::find( $identifier );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $productVariant->product_id, $identifier, $product['quantity'], true  );
                            break;

                        case 'bundle':
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, $product['id'], $product['quantity'], true  );
                            break;
                        
                        default:
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, $product['id'], $product['quantity'], true  );
                            break;
                    }

                    $quotationMetaCreate = QuotationMeta::create([
                        'quotation_id' => $quotationCreate->id,
                        'product_id' => $type == 'product' ? $identifier : null,
                        'variant_id' => $type == 'variant' ? $identifier :null,
                        'bundle_id' => $type == 'bundle' ? $identifier :null,
                        'quantity' => $product['quantity'],
                        'tax_method_id' => $request->tax_method,
                        'status' => 10,
                    ]);

                }
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.quotations' ) ) ] ),
        ] );
    }
    
    public static function updateQuotation( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'remarks' => [ 'nullable' ],
            'attachment' => [ 'nullable' ],
            'warehouse' => [ 'nullable', 'exists:warehouses,id' ],
            'products' => [ 'nullable' ],
            'products.*.id' => [ 'nullable',  function ($attribute, $value, $fail) {
                    if (!preg_match('/^(product|bundle|variant)-(\d+)$/', $value, $matches)) {
                        return $fail("The {$attribute} format is invalid.");
                    }
        
                    $type = $matches[1];
                    $identifier = $matches[2];
        
                    // Check if the identifier exists in the corresponding table
                    if ($type === 'product' && !\DB::table('products')->where('id', $identifier)->exists()) {
                        return $fail("The {$attribute} does not exist in products.");
                    } elseif ($type === 'bundle' && !\DB::table('bundles')->where('id', $identifier)->exists()) {
                        return $fail("The {$attribute} does not exist in bundles.");
                    } elseif ($type === 'variant' && !\DB::table('product_variants')->where('id', $identifier)->exists()) {
                        return $fail("The {$attribute} does not exist in bundles.");
                    }
                },
            ],
            'products.*.quantity' => [
                'nullable',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1]; // Extract the product index
                    $productId = $request->input("products.{$index}.id");

                    if (!preg_match('/^(product|bundle|variant)-(\d+)$/', $productId, $matches)) {
                        return $fail("The ID format is invalid for {$attribute}.");
                    }

                    $type = $matches[1];
                    $identifier = $matches[2];

                    // Validate the quantity against the stock in the respective warehouse table
                    $availableStock = 0;

                    if ($type === 'product') {
                        $availableStock = \DB::table('warehouses_products')
                            ->where('warehouse_id', $request->warehouse)
                            ->where('product_id', $identifier)
                            ->sum('quantity');

                        $name = 'Product: ' . Product::find($identifier)->value('title');

                    } elseif ($type === 'bundle') {
                        $availableStock = \DB::table('warehouses_bundles')
                            ->where('warehouse_id', $request->warehouse)
                            ->where('bundle_id', $identifier)
                            ->sum('quantity');

                        $name = 'Bundle: ' . Bundle::find($identifier)->value('title');

                    } elseif ($type === 'variant') {
                        $availableStock = \DB::table('warehouses_variants')
                            ->where('warehouse_id', $request->warehouse)
                            ->where('variant_id', $identifier)
                            ->sum('quantity');

                        $name = 'Variant: ' . ProductVariant::find($identifier)->value('title');

                    }

                    if ($value > $availableStock) {
                        return $fail(" The requested quantity for {$name} exceeds available stock ({$availableStock} stocks available).");
                    }
                },
            ],
            'supplier' => [ 'nullable', 'exists:suppliers,id' ],
            'salesman' => [ 'nullable', 'exists:administrators,id' ],
            'customer' => [ 'nullable', 'exists:users,id' ],
            'discount' => [ 'nullable', 'numeric' ,'min:0' ],
            'shipping_cost' => [ 'nullable', 'numeric' ,'min:0' ],
            'tax_method' => [ 'nullable', 'exists:tax_methods,id' ],
        ] );

        $attributeName = [
            'title' => __( 'quotation.title' ),
            'description' => __( 'quotation.description' ),
            'image' => __( 'quotation.image' ),
            'thumbnail' => __( 'quotation.thumbnail' ),
            'url_slug' => __( 'quotation.url_slug' ),
            'structure' => __( 'quotation.structure' ),
            'size' => __( 'quotation.size' ),
            'phone_number' => __( 'quotation.phone_number' ),
            'sort' => __( 'quotation.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $amount = 0;
            $originalAmount = 0;
            $paidAmount = 0;
            $finalAmount = 0;
            $taxAmount = 0;

            if( $request->products ) {

                $products = $request->products;

                foreach( $products as $product ){
                    preg_match('/^(product|bundle|variant)-(\d+)$/', $product['id'], $matches);

                    $type = $matches[1];
                    $identifier = $matches[2];

                    switch ($type) {
                        case 'product':
                            $productData = Product::find( $identifier );
                            // turnoff warehouse price
                            if( count( $productData->warehouses ) > 0 ){
                                $warehouseProduct = $productData->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                                $amount += $warehouseProduct->pivot->price > 0 ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->price * $product['quantity'];
                            } else {
                                $amount += $productData->price * $product['quantity'];
                            }
                            break;

                        case 'variant':
                            $productData = ProductVariant::find( $identifier );
                            // turnoff warehouse price
                            if( count( $productData->product->warehouses ) > 0 ){
                                $warehouseProduct = $productData->product->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                                $amount += $warehouseProduct->pivot->price > 0 ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->product->price * $product['quantity'];
                            } else {
                                $amount += $productData->product->price * $product['quantity'];
                            }
                            break;

                        case 'bundle':
                            $productData = Bundle::find( $identifier );
                            // turnoff warehouse price
                            $amount += $productData->price * $product['quantity'];
                            break;
                        
                        default:
                            $productData = Product::find( $identifier );
                            // turnoff warehouse price
                            if( count( $productData->warehouses ) > 0 ){
                                $warehouseProduct = $productData->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                                $amount += $warehouseProduct->pivot->price > 0 ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->price * $product['quantity'];
                            } else {
                                $amount += $productData->price * $product['quantity'];
                            }
                            break;
                    }

                }
            }
            
            $taxAmount = $amount * Helper::taxTypes()[$request->tax_type ?? 1]['percentage'];
            $finalAmount = $amount - $request->discount + $taxAmount;

            $updateQuotation = Quotation::find( $request->id );

            $updateQuotation->remarks = $request->remarks ?? $updateQuotation->remarks;
            $updateQuotation->warehouse_id = $request->warehouse ?? $updateQuotation->warehouse_id;
            $updateQuotation->salesman_id = $request->salesman ?? $updateQuotation->salesman_id;
            $updateQuotation->customer_id = $request->customer ?? $updateQuotation->customer_id;
            $updateQuotation->tax_type = $request->tax_type ?? 1 ?? $updateQuotation->tax_type;
            $updateQuotation->amount = $amount;
            $updateQuotation->original_amount = $amount;
            $updateQuotation->paid_amount = $paidAmount;
            $updateQuotation->final_amount = $amount;
            $updateQuotation->order_tax = $taxAmount;
            $updateQuotation->order_discount = $request->discount;
            $updateQuotation->shipping_cost = $request->shipping_cost;
            $updateQuotation->tax_method_id = $request->tax_method;

            $attachment = explode( ',', $request->attachment );

            $attachmentFiles = FileManager::whereIn( 'id', $attachment )->get();

            if ( $attachmentFiles ) {
                foreach ( $attachmentFiles as $attachmentFile ) {

                    $fileName = explode( '/', $attachmentFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'quotation/' . $updateQuotation->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $updateQuotation->attachment = $target;
                   $updateQuotation->save();

                    $attachmentFile->status = 10;
                    $attachmentFile->save();

                }
            }

            $oldQuotationMetas = $updateQuotation->quotationMetas;
            $oldQuotationMetasArray = $oldQuotationMetas->pluck('id')->toArray();
            $products = $request->products;

            if( $products ) {

                $incomingProductIds = array_column($products, 'metaId');
    
                $incomingProductIds = array_filter($incomingProductIds, function ($id) {
                    return $id !== null && $id !== 'null';
                });

                $idsToDelete = array_diff($oldQuotationMetasArray, $incomingProductIds);

                foreach( $idsToDelete as $idToDelete ){

                    $quotation = QuotationMeta::find( $idToDelete );

                    if( $quotation->variant_id ){
                        $prevWarehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $quotation->product_id, $quotation->variant_id, -$quotation->amount, true );
                    }

                    else if( $quotation->bundle_id ){
                        $prevWarehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'bundle-' . $quotation->product_id, -$quotation->amount, true );
                    }

                    else{
                        $prevWarehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'product-' . $quotation->product_id, -$quotation->amount, true );
                    }

                }

                QuotationMeta::whereIn('id', $idsToDelete)->delete();
                
                foreach( $products as $product ){

                    if( in_array( $product['metaId'], $oldQuotationMetasArray ) ){

                        $removeQuotationMeta = QuotationMeta::find( $product['metaId'] );

                        // Remove previous
                        if( $removeQuotationMeta->product_id ) {
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'product-'.$removeQuotationMeta->product_id, -$removeQuotationMeta->amount, true  );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'product-'.$removeQuotationMeta->product_id, $product['quantity'], false );
                        }elseif( $removeQuotationMeta->variant_id ) {
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $removeQuotationMeta->product_id, $removeQuotationMeta->variant_id, -$removeQuotationMeta->amount, true  );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $removeQuotationMeta->product_id, $removeQuotationMeta->variant_id, $product['quantity'], false  );
                        }else{
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'bundle'.$removeQuotationMeta->bundle_id, -$removeQuotationMeta->amount, true  );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'bundle'.$removeQuotationMeta->bundle_id, $product['quantity'], false );
                        }
                        
                        $removeQuotationMeta->quotation_id = $updateQuotation->id;
                        $removeQuotationMeta->amount = $product['quantity'];
                    } else {

                        if( $product['metaId'] == 'null' ){

                            preg_match('/^(product|bundle|variant)-(\d+)$/', $product['id'], $matches);

                            $type = $matches[1];
                            $identifier = $matches[2];

                            switch ($type) {
                                case 'product':
                                    $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, $product['id'], $product['quantity'], true  );
                                    break;
        
                                case 'variant':
                                    $productVariant = ProductVariant::find( $identifier );
                                    $warehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $productVariant->product_id, $identifier, $product['quantity'], true  );
                                    break;
        
                                case 'bundle':
                                    $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, $product['id'], $product['quantity'], true  );
                                    break;
                                
                                default:
                                    $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, $product['id'], $product['quantity'], true  );
                                    break;
                            }

                            $quotationMetaCreate = QuotationMeta::create([
                                'quotation_id' => $updateQuotation->id,
                                'product_id' => $product['id'],
                                'quantity' => $product['quantity'],
                                'product_id' => $type == 'product' ? $identifier : null,
                                'variant_id' => $type == 'variant' ? $identifier :null,
                                'bundle_id' => $type == 'bundle' ? $identifier :null,
                                'tax_method_id' => $request->tax_method,
                                'status' => 10,
                            ]);
                        } else{
                            $removeQuotationMeta = QuotationMeta::find( $product['metaId'] );
                            $removeQuotationMeta->delete();
                        }
                    }
    
                }
            } else {
                foreach ($oldQuotationMetas as $meta) {
                    $meta->delete();
                }
            }

            $updateQuotation->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.quotations' ) ) ] ),
        ] );
    }

     public static function allQuotations( $request ) {

        $quotations = Quotation::with( [ 'salesman', 'customer','warehouse', 'supplier'] )->select( 'quotations.*');

        $filterObject = self::filter( $request, $quotations );
        $quotation = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $quotation->orderBy( 'quotations.created_at', $dir );
                    break;
                case 2:
                    $quotation->orderBy( 'quotations.title', $dir );
                    break;
                case 3:
                    $quotation->orderBy( 'quotations.description', $dir );
                    break;
            }
        }

            $quotationCount = $quotation->count();

            $limit = $request->length;
            $offset = $request->start;

            $quotations = $quotation->skip( $offset )->take( $limit )->get();

            if ( $quotations ) {
                $quotations->append( [
                    'encrypted_id',
                    'attachment_path',
                ] );
            }

            $totalRecord = Quotation::count();

            $data = [
                'quotations' => $quotations,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $quotationCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'quotations.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->reference ) ) {
            $model->where( 'quotations.reference', 'LIKE', '%' . $request->reference . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'quotations.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        if ( !empty( $request->id ) ) {
            $model->where( 'quotations.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->product)) {
            $model->whereHas('quotationMetas', function ($query) use ($request) {
                $query->whereHas('product', function ($query) use ($request) {
                    $query->where('title', 'LIKE', '%' . $request->product . '%');
                });
            });
            $filter = true;
        }

        if (!empty($request->warehouse)) {
            $model->whereHas('warehouses', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->warehouse . '%');
            });
            $filter = true;
        }

        if (!empty($request->supplier)) {
            $model->whereHas('supplier', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->supplier . '%');
            });
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneQuotation( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $quotation = Quotation::with( [ 'quotationMetas.product.warehouses','quotationMetas.bundle','quotationMetas.variant.product.warehouses', 'taxMethod', 'salesman', 'customer','warehouse', 'supplier'] )->find( $request->id );

        $quotation->append( [
            'encrypted_id',
            'attachment_path',
        ] );

        $quotation->taxMethod?->append( [
            'formatted_tax'
        ] );
        
        return response()->json( $quotation );
    }

    public static function deleteQuotation( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'quotation.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Quotation::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.quotations' ) ) ] ),
        ] );
    }

    public static function updateQuotationStatus( $request ) {
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateQuotation = Quotation::find( $request->id );
            $updateQuotation->status = $updateQuotation->status == 10 ? 20 : 10;

            $updateQuotation->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'quotation' => $updateQuotation,
                    'message_key' => 'update_quotation_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_quotation_failed',
            ], 500 );
        }
    }

    public static function removeQuotationAttachment( $request ) {

        $updateFarm = Quotation::find( Helper::decode($request->id) );
        $updateFarm->attachment = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'quotation.attachment' ) ) ] ),
        ] );
    }

    public static function oneQuotationTransaction( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $quotation = QuotationTransaction::with( [ 'quotation', 'account'] )->find( $request->id );

        $quotation->append( [
            'encrypted_id',
        ] );
        
        return response()->json( $quotation );
    }

    public static function createQuotationTransaction( $request ) {

        $validator = Validator::make( $request->all(), [
            'quotation' => [ 'nullable', 'exists:quotations,id' ],
            'account' => [ 'nullable', 'exists:expenses_accounts,id' ],
            'paid_amount' => [ 'nullable', 'numeric' ,'min:0' ],
            'paid_by' => [ 'nullable' ],

        ] );

        $attributeName = [
            'title' => __( 'quotation.title' ),
            'description' => __( 'quotation.description' ),
            'image' => __( 'quotation.image' ),
            'thumbnail' => __( 'quotation.thumbnail' ),
            'url_slug' => __( 'quotation.url_slug' ),
            'structure' => __( 'quotation.structure' ),
            'size' => __( 'quotation.size' ),
            'phone_number' => __( 'quotation.phone_number' ),
            'sort' => __( 'quotation.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $quotationCreate = QuotationTransaction::create([
                'quotation_id' => $request->quotation,
                'account_id' => $request->account,
                'reference' => Helper::generateQuotationTransactionNumber(),
                'remarks' => $request->remarks,
                'paid_amount' => $request->paid_amount,
                'paid_by' => $request->paid_by,
                'status' => 10,
            ]);

            $quotation = Quotation::find($request->quotation);
            $quotation->paid_amount += $request->paid_amount;
            $quotation->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.quotation_transactions' ) ) ] ),
        ] );
    }

    public static function updateQuotationTransactionStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateQuotationTransaction = QuotationTransaction::find( $request->id );
            $updateQuotationTransaction->status = $updateQuotation->status == 10 ? 20 : 10;
            $updateQuotationTransaction->save();

            $quotation = Quotation::find($updateQuotation->quotation_id);
            if( $updateQuotationTransaction->status == 10 ) {
                $quotation->paid_amount += $updateQuotationTransaction->paid_amount;
            }else{
                $quotation->paid_amount -= $updateQuotationTransaction->paid_amount;
            }
            $quotation->save();

            DB::commit();

            return response()->json( [
                'data' => [
                    'quotation' => $updateQuotation,
                    'message_key' => 'update_quotation_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_quotation_success',
            ], 500 );
        }
    }

    public static function convertSalesOrder($request) {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);
    
        DB::beginTransaction();
    
        try {
            $quotation = Quotation::find($request->id);
    
            if (!$quotation) {
                throw new \Exception('Quotation not found.');
            }

            if ($quotation->status != 10) {
                throw new \Exception('Quotation not available.');
            }
    
            $salesOrder = new SalesOrder();
            $salesOrder->quotation_id = $quotation->id;
            $salesOrder->customer_id = $quotation->customer_id;
            $salesOrder->salesman_id = $quotation->salesman_id;
            $salesOrder->warehouse_id = $quotation->warehouse_id;
            $salesOrder->supplier_id = $quotation->supplier_id;
            $salesOrder->tax_method_id = $quotation->tax_method_id;
            $salesOrder->order_tax = $quotation->order_tax;
            $salesOrder->order_discount = $quotation->order_discount;
            $salesOrder->shipping_cost = $quotation->shipping_cost;
            $salesOrder->remarks = $quotation->remarks;
            $salesOrder->attachment = $quotation->attachment;
            $salesOrder->status = 10;
            $salesOrder->amount = $quotation->amount;
            $salesOrder->original_amount = $quotation->original_amount;
            $salesOrder->final_amount = $quotation->final_amount;
            $salesOrder->paid_amount = $quotation->paid_amount;
            $salesOrder->reference = Helper::generateSalesOrderNumber();
            $salesOrder->save();
    
            $quotationMetas = $quotation->quotationMetas;
            foreach ($quotationMetas as $quotationMeta) {
                $salesOrderMeta = new SalesOrderMeta();
                $salesOrderMeta->sales_order_id = $salesOrder->id;
                $salesOrderMeta->product_id = $quotationMeta->product_id;
                $salesOrderMeta->custom_discount = $quotationMeta->custom_discount;
                $salesOrderMeta->custom_tax = $quotationMeta->custom_tax;
                $salesOrderMeta->custom_shipping_cost = $quotationMeta->custom_shipping_cost;
                $salesOrderMeta->quantity = $quotationMeta->quantity;
                $salesOrderMeta->product_id = $quotationMeta->product_id;
                $salesOrderMeta->variant_id = $quotationMeta->variant_id;
                $salesOrderMeta->bundle_id = $quotationMeta->bundle_id;
                $salesOrderMeta->tax_method_id = $quotationMeta->tax_method_id;
                $salesOrderMeta->status = 10;
                $salesOrderMeta->save();
            }
    
            $quotation->status = 12;
            $quotation->save();
    
            DB::commit();
    
            return response()->json([
                'data' => [
                    'sales_order' => $salesOrder,
                    'message_key' => 'convert_quotation_success',
                ]
            ]);
    
        } catch (\Throwable $th) {
            DB::rollBack();
    
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'convert_quotation_failure',
            ], 500);
        }
    }

    public static function sendEmail( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $quotation = Quotation::with( [ 'quotationMetas.product.warehouses','quotationMetas.bundle','quotationMetas.variant.product.warehouses', 'taxMethod', 'salesman', 'customer','warehouse', 'supplier'] )->find( $request->id );

            Mail::to( $quotation->customer->email )->send(new QuotationMail( $updateQuotation ));

            DB::commit();

            return response()->json( [
                'data' => [
                    'quotation' => $quotation,
                    'message_key' => 'mail_sent',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'mail_send_failed',
            ], 500 );
        }
    }
    
}
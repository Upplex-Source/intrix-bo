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
    SalesOrder,
    SalesOrderMeta,
    Booking,
    FileManager,
    Product,
    Invoice,
    InvoiceMeta,
    ProductVariant,
    TaxMethod,
    Bundle,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SalesOrderService
{

    public static function createSalesOrder( $request ) {

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
            'title' => __( 'sales_order.title' ),
            'description' => __( 'sales_order.description' ),
            'image' => __( 'sales_order.image' ),
            'thumbnail' => __( 'sales_order.thumbnail' ),
            'url_slug' => __( 'sales_order.url_slug' ),
            'structure' => __( 'sales_order.structure' ),
            'size' => __( 'sales_order.size' ),
            'phone_number' => __( 'sales_order.phone_number' ),
            'sort' => __( 'sales_order.sort' ),
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

            $salesOrderCreate = SalesOrder::create([
                'supplier_id' => $request->supplier,
                'warehouse_id' => $request->warehouse,
                'salesman_id' => $request->salesman,
                'customer_id' => $request->customer,
                'remarks' => $request->remarks,
                'reference' => Helper::generateSalesOrderNumber(),
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

                    $target = 'sales_order/' . $salesOrderCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $salesOrderCreate->attachment = $target;
                   $salesOrderCreate->save();

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

                    $salesOrderMetaCreate = SalesOrderMeta::create([
                        'sales_order_id' => $salesOrderCreate->id,
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.sales_orders' ) ) ] ),
        ] );
    }
    
    public static function updateSalesOrder( $request ) {

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
            'title' => __( 'sales_order.title' ),
            'description' => __( 'sales_order.description' ),
            'image' => __( 'sales_order.image' ),
            'thumbnail' => __( 'sales_order.thumbnail' ),
            'url_slug' => __( 'sales_order.url_slug' ),
            'structure' => __( 'sales_order.structure' ),
            'size' => __( 'sales_order.size' ),
            'phone_number' => __( 'sales_order.phone_number' ),
            'sort' => __( 'sales_order.sort' ),
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

            $updateSalesOrder = SalesOrder::find( $request->id );

            $updateSalesOrder->remarks = $request->remarks ?? $updateSalesOrder->remarks;
            $updateSalesOrder->warehouse_id = $request->warehouse ?? $updateSalesOrder->warehouse_id;
            $updateSalesOrder->salesman_id = $request->salesman ?? $updateSalesOrder->salesman_id;
            $updateSalesOrder->customer_id = $request->customer ?? $updateSalesOrder->customer_id;
            $updateSalesOrder->tax_type = $request->tax_type ?? 1 ?? $updateSalesOrder->tax_type;
            $updateSalesOrder->amount = $amount;
            $updateSalesOrder->original_amount = $amount;
            $updateSalesOrder->paid_amount = $paidAmount;
            $updateSalesOrder->final_amount = $amount;
            $updateSalesOrder->order_tax = $taxAmount;
            $updateSalesOrder->order_discount = $request->discount;
            $updateSalesOrder->shipping_cost = $request->shipping_cost;
            $updateSalesOrder->tax_method_id = $request->tax_method;

            $attachment = explode( ',', $request->attachment );

            $attachmentFiles = FileManager::whereIn( 'id', $attachment )->get();

            if ( $attachmentFiles ) {
                foreach ( $attachmentFiles as $attachmentFile ) {

                    $fileName = explode( '/', $attachmentFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'sales_order/' . $updateSalesOrder->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $updateSalesOrder->attachment = $target;
                   $updateSalesOrder->save();

                    $attachmentFile->status = 10;
                    $attachmentFile->save();

                }
            }

            $oldSalesOrderMetas = $updateSalesOrder->salesOrderMetas;
            $oldSalesOrderMetasArray = $oldSalesOrderMetas->pluck('id')->toArray();
            $products = $request->products;

            if( $products ) {

                $incomingProductIds = array_column($products, 'metaId');
    
                $incomingProductIds = array_filter($incomingProductIds, function ($id) {
                    return $id !== null && $id !== 'null';
                });

                $idsToDelete = array_diff($oldSalesOrderMetasArray, $incomingProductIds);

                foreach( $idsToDelete as $idToDelete ){

                    $salesorder = SalesOrderMeta::find( $idToDelete );

                    if( $salesorder->variant_id ){
                        $prevWarehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $salesorder->product_id, $salesorder->variant_id, -$salesorder->quantity, true );
                    }

                    else if( $salesorder->bundle_id ){
                        $prevWarehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'bundle-' . $salesorder->product_id, -$salesorder->quantity, true );
                    }

                    else{
                        $prevWarehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'product-' . $salesorder->product_id, -$salesorder->quantity, true );
                    }

                }

                SalesOrderMeta::whereIn('id', $idsToDelete)->delete();
                
                foreach( $products as $product ){

                    if( in_array( $product['metaId'], $oldSalesOrderMetasArray ) ){

                        $removeSalesOrderMeta = SalesOrderMeta::find( $product['metaId'] );

                        // Remove previous
                        if( $removeQuotationMeta->product_id ) {
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'product-'.$removeQuotationMeta->product_id, -$removeQuotationMeta->quantity, true  );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'product-'.$removeQuotationMeta->product_id, $product['quantity'], false );
                        }elseif( $removeQuotationMeta->variant_id ) {
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $removeQuotationMeta->product_id, $removeQuotationMeta->variant_id, -$removeQuotationMeta->quantity, true  );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $removeQuotationMeta->product_id, $removeQuotationMeta->variant_id, $product['quantity'], false  );
                        }else{
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'bundle'.$removeQuotationMeta->bundle_id, -$removeQuotationMeta->quantity, true  );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'bundle'.$removeQuotationMeta->bundle_id, $product['quantity'], false );
                        }

                        preg_match('/^(product|bundle|variant)-(\d+)$/', $product['id'], $matches);

                        $type = $matches[1];
                        $identifier = $matches[2];
                        
                        $removeSalesOrderMeta->sales_order_id = $updateSalesOrder->id;
                        $removeSalesOrderMeta->quantity= $product['quantity'];
                        $removeSalesOrderMeta->save();

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

                            $salesOrderMetaCreate = SalesOrderMeta::create([
                                'sales_order_id' => $updateSalesOrder->id,
                                'product_id' => $product['id'],
                                'quantity' => $product['quantity'],
                                'product_id' => $type == 'product' ? $identifier : null,
                                'variant_id' => $type == 'variant' ? $identifier :null,
                                'bundle_id' => $type == 'bundle' ? $identifier :null,
                                'tax_method_id' => $request->tax_method,
                                'status' => 10,
                            ]);
                        } else{
                            $removeSalesOrderMeta = SalesOrderMeta::find( $product['metaId'] );
                            $removeSalesOrderMeta->delete();
                        }
                    }
    
                }
            } else {
                foreach ($oldSalesOrderMetas as $meta) {
                    $meta->delete();
                }
            }

            $updateSalesOrder->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.sales_orders' ) ) ] ),
        ] );
    }

     public static function allSalesOrders( $request ) {

        $salesorders = SalesOrder::with( [ 'quotation','salesman', 'customer','warehouse', 'supplier'] )->select( 'sales_orders.*');

        $filterObject = self::filter( $request, $salesorders );
        $salesorder = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $salesorder->orderBy( 'sales_orders.created_at', $dir );
                    break;
                case 2:
                    $salesorder->orderBy( 'sales_orders.title', $dir );
                    break;
                case 3:
                    $salesorder->orderBy( 'sales_orders.description', $dir );
                    break;
            }
        }

            $salesorderCount = $salesorder->count();

            $limit = $request->length;
            $offset = $request->start;

            $salesorders = $salesorder->skip( $offset )->take( $limit )->get();

            if ( $salesorders ) {
                $salesorders->append( [
                    'encrypted_id',
                    'attachment_path',
                ] );
            }

            $totalRecord = SalesOrder::count();

            $data = [
                'sales_orders' => $salesorders,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $salesorderCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'sales_orders.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->reference ) ) {
            $model->where( 'sales_orders.reference', 'LIKE', '%' . $request->reference . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'sales_orders.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        if ( !empty( $request->id ) ) {
            $model->where( 'sales_orders.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->product)) {
            $model->whereHas('salesOrderMetas', function ($query) use ($request) {
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

    public static function oneSalesOrder( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $salesorder = SalesOrder::with( [ 'salesOrderMetas.product.warehouses','salesOrderMetas.bundle','salesOrderMetas.variant.product.warehouses', 'taxMethod', 'salesman', 'customer','warehouse', 'supplier'] )->find( $request->id );

        $salesorder->append( [
            'encrypted_id',
            'attachment_path',
        ] );

        $salesorder->taxMethod?->append( [
            'formatted_tax'
        ] );
        
        return response()->json( $salesorder );
    }

    public static function deleteSalesOrder( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'sales_order.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            SalesOrder::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.sales_orders' ) ) ] ),
        ] );
    }

    public static function updateSalesOrderStatus( $request ) {
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateSalesOrder = SalesOrder::find( $request->id );
            $updateSalesOrder->status = $updateSalesOrder->status == 10 ? 20 : 10;

            $updateSalesOrder->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'sales_order' => $updateSalesOrder,
                    'message_key' => 'update_sales_order_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_sales_order_failed',
            ], 500 );
        }
    }

    public static function removeSalesOrderAttachment( $request ) {

        $updateFarm = SalesOrder::find( Helper::decode($request->id) );
        $updateFarm->attachment = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'sales_order.attachment' ) ) ] ),
        ] );
    }

    public static function oneSalesOrderTransaction( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $salesorder = SalesOrderTransaction::with( [ 'sales_order', 'account'] )->find( $request->id );

        $salesorder->append( [
            'encrypted_id',
        ] );
        
        return response()->json( $salesorder );
    }

    public static function createSalesOrderTransaction( $request ) {

        $validator = Validator::make( $request->all(), [
            'sales_order' => [ 'nullable', 'exists:sales_orders,id' ],
            'account' => [ 'nullable', 'exists:expenses_accounts,id' ],
            'paid_amount' => [ 'nullable', 'numeric' ,'min:0' ],
            'paid_by' => [ 'nullable' ],

        ] );

        $attributeName = [
            'title' => __( 'sales_order.title' ),
            'description' => __( 'sales_order.description' ),
            'image' => __( 'sales_order.image' ),
            'thumbnail' => __( 'sales_order.thumbnail' ),
            'url_slug' => __( 'sales_order.url_slug' ),
            'structure' => __( 'sales_order.structure' ),
            'size' => __( 'sales_order.size' ),
            'phone_number' => __( 'sales_order.phone_number' ),
            'sort' => __( 'sales_order.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $salesOrderCreate = SalesOrderTransaction::create([
                'sales_order_id' => $request->sales_order,
                'account_id' => $request->account,
                'reference' => Helper::generateSalesOrderTransactionNumber(),
                'remarks' => $request->remarks,
                'paid_amount' => $request->paid_amount,
                'paid_by' => $request->paid_by,
                'status' => 10,
            ]);

            $salesorder = SalesOrder::find($request->sales_order);
            $salesorder->paid_amount += $request->paid_amount;
            $salesorder->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.sales_order_transactions' ) ) ] ),
        ] );
    }

    public static function updateSalesOrderTransactionStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateSalesOrderTransaction = SalesOrderTransaction::find( $request->id );
            $updateSalesOrderTransaction->status = $updateSalesOrder->status == 10 ? 20 : 10;
            $updateSalesOrderTransaction->save();

            $salesorder = SalesOrder::find($updateSalesOrder->sales_order_id);
            if( $updateSalesOrderTransaction->status == 10 ) {
                $salesorder->paid_amount += $updateSalesOrderTransaction->paid_amount;
            }else{
                $salesorder->paid_amount -= $updateSalesOrderTransaction->paid_amount;
            }
            $salesorder->save();

            DB::commit();

            return response()->json( [
                'data' => [
                    'sales_order' => $updateSalesOrder,
                    'message_key' => 'update_sales_order_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_sales_order_success',
            ], 500 );
        }
    }

    public static function convertInvoice($request) {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);
    
        DB::beginTransaction();
    
        try {
            $salesorder = SalesOrder::find($request->id);
    
            if (!$salesorder) {
                throw new \Exception('SalesOrder not found.');
            }

            if ($salesorder->status != 10) {
                throw new \Exception('SalesOrder not available.');
            }
    
            $invoice = new Invoice();
            $invoice->sales_order_id = $salesorder->id;
            $invoice->customer_id = $salesorder->customer_id;
            $invoice->salesman_id = $salesorder->salesman_id;
            $invoice->warehouse_id = $salesorder->warehouse_id;
            $invoice->supplier_id = $salesorder->supplier_id;
            $invoice->tax_method_id = $salesorder->tax_method_id;
            $invoice->order_tax = $salesorder->order_tax;
            $invoice->order_discount = $salesorder->order_discount;
            $invoice->shipping_cost = $salesorder->shipping_cost;
            $invoice->remarks = $salesorder->remarks;
            $invoice->attachment = $salesorder->attachment;
            $invoice->status = 10;
            $invoice->amount = $salesorder->amount;
            $invoice->original_amount = $salesorder->original_amount;
            $invoice->final_amount = $salesorder->final_amount;
            $invoice->paid_amount = $salesorder->paid_amount;
            $invoice->reference = Helper::generateInvoiceNumber();
            $invoice->save();
    
            $salesorderMetas = $salesorder->salesOrderMetas;
            foreach ($salesorderMetas as $salesorderMeta) {
                $invoiceMeta = new InvoiceMeta();
                $invoiceMeta->invoice_id = $invoice->id;
                $invoiceMeta->product_id = $salesorderMeta->product_id;
                $invoiceMeta->custom_discount = $salesorderMeta->custom_discount;
                $invoiceMeta->custom_tax = $salesorderMeta->custom_tax;
                $invoiceMeta->custom_shipping_cost = $salesorderMeta->custom_shipping_cost;
                $invoiceMeta->quantity = $salesorderMeta->quantity;
                $invoiceMeta->product_id = $salesorderMeta->product_id;
                $invoiceMeta->variant_id = $salesorderMeta->variant_id;
                $invoiceMeta->bundle_id = $salesorderMeta->bundle_id;
                $invoiceMeta->tax_method_id = $salesorderMeta->tax_method_id;
                $invoiceMeta->status = 10;
                $invoiceMeta->save();
            }
    
            $salesorder->status = 13;
            $salesorder->save();
    
            DB::commit();
    
            return response()->json([
                'data' => [
                    'sales_order' => $invoice,
                    'message_key' => 'convert_invoice_success',
                ]
            ]);
    
        } catch (\Throwable $th) {
            DB::rollBack();
    
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'convert_invoice_failure',
            ], 500);
        }
    }

    public static function sendEmail( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $salesorder = SalesOrder::with( [ 'salesOrderMetas.product.warehouses','salesOrderMetas.bundle','salesOrderMetas.variant.product.warehouses', 'taxMethod', 'salesman', 'customer','warehouse', 'supplier'] )->find( $request->id );
            $salesorder->action = 'sales_order';
            // Mail::to( $salesorder->customer->email )->send(new QuotationMail( $salesorder ));

            DB::commit();

            return response()->json( [
                'data' => [
                    'sales_order' => $salesorder,
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
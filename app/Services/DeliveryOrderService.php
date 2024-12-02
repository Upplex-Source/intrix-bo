<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
    Mail,
};

use App\Mail\DeliveryOrderMail;

use Helper;

use App\Models\{
    Company,
    Customer,
    Administrator,
    Invoice,
    InvoiceMeta,
    Booking,
    FileManager,
    Product,
    DeliveryOrder,
    DeliveryOrderMeta,
    ProductVariant,
    TaxMethod,
    Bundle,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DeliveryOrderService
{

    public static function createDeliveryOrder( $request ) {

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
                        return $fail("The requested quantity for {$name} exceeds available stock.");
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
            'title' => __( 'deliveryorder.title' ),
            'description' => __( 'deliveryorder.description' ),
            'image' => __( 'deliveryorder.image' ),
            'thumbnail' => __( 'deliveryorder.thumbnail' ),
            'url_slug' => __( 'deliveryorder.url_slug' ),
            'structure' => __( 'deliveryorder.structure' ),
            'size' => __( 'deliveryorder.size' ),
            'phone_number' => __( 'deliveryorder.phone_number' ),
            'sort' => __( 'deliveryorder.sort' ),
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

            $deliveryOrderCreate = DeliveryOrder::create([
                'supplier_id' => $request->supplier,
                'warehouse_id' => $request->warehouse,
                'salesman_id' => $request->salesman,
                'customer_id' => $request->customer,
                'remarks' => $request->remarks,
                'reference' => Helper::generateDeliveryOrderNumber(),
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

                    $target = 'delivery_order/' . $deliveryOrderCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $deliveryOrderCreate->attachment = $target;
                   $deliveryOrderCreate->save();

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

                    $deliveryOrderMetaCreate = DeliveryOrderMeta::create([
                        'delivery_order_id' => $deliveryOrderCreate->id,
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.delivery_orders' ) ) ] ),
        ] );
    }
    
    public static function updateDeliveryOrder( $request ) {

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
                        return $fail("The requested quantity for {$name} exceeds available stock.");
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
            'title' => __( 'deliveryorder.title' ),
            'description' => __( 'deliveryorder.description' ),
            'image' => __( 'deliveryorder.image' ),
            'thumbnail' => __( 'deliveryorder.thumbnail' ),
            'url_slug' => __( 'deliveryorder.url_slug' ),
            'structure' => __( 'deliveryorder.structure' ),
            'size' => __( 'deliveryorder.size' ),
            'phone_number' => __( 'deliveryorder.phone_number' ),
            'sort' => __( 'deliveryorder.sort' ),
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

            $updateDeliveryOrder = DeliveryOrder::find( $request->id );

            $updateDeliveryOrder->remarks = $request->remarks ?? $updateDeliveryOrder->remarks;
            $updateDeliveryOrder->warehouse_id = $request->warehouse ?? $updateDeliveryOrder->warehouse_id;
            $updateDeliveryOrder->salesman_id = $request->salesman ?? $updateDeliveryOrder->salesman_id;
            $updateDeliveryOrder->customer_id = $request->customer ?? $updateDeliveryOrder->customer_id;
            $updateDeliveryOrder->tax_type = $request->tax_type ?? 1 ?? $updateDeliveryOrder->tax_type;
            $updateDeliveryOrder->amount = $amount;
            $updateDeliveryOrder->original_amount = $amount;
            $updateDeliveryOrder->paid_amount = $paidAmount;
            $updateDeliveryOrder->final_amount = $amount;
            $updateDeliveryOrder->order_tax = $taxAmount;
            $updateDeliveryOrder->order_discount = $request->discount;
            $updateDeliveryOrder->shipping_cost = $request->shipping_cost;
            $updateDeliveryOrder->tax_method_id = $request->tax_method;

            $attachment = explode( ',', $request->attachment );

            $attachmentFiles = FileManager::whereIn( 'id', $attachment )->get();

            if ( $attachmentFiles ) {
                foreach ( $attachmentFiles as $attachmentFile ) {

                    $fileName = explode( '/', $attachmentFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'delivery_order/' . $updateDeliveryOrder->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $updateDeliveryOrder->attachment = $target;
                   $updateDeliveryOrder->save();

                    $attachmentFile->status = 10;
                    $attachmentFile->save();

                }
            }

            $oldDeliveryOrderMetas = $updateDeliveryOrder->deliveryorderMetas;
            $oldDeliveryOrderMetasArray = $oldDeliveryOrderMetas->pluck('id')->toArray();
            $products = $request->products;

            if( $products ) {

                $incomingProductIds = array_column($products, 'metaId');
    
                $incomingProductIds = array_filter($incomingProductIds, function ($id) {
                    return $id !== null && $id !== 'null';
                });

                $idsToDelete = array_diff($oldDeliveryOrderMetasArray, $incomingProductIds);

                foreach( $idsToDelete as $idToDelete ){

                    $deliveryOrder = DeliveryOrderMeta::find( $idToDelete );

                    if( $deliveryOrder->variant_id ){
                        $prevWarehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $deliveryOrder->product_id, $deliveryOrder->variant_id, -$deliveryOrder->amount, true );
                    }

                    else if( $deliveryOrder->bundle_id ){
                        $prevWarehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'bundle-' . $deliveryOrder->product_id, -$deliveryOrder->amount, true );
                    }

                    else{
                        $prevWarehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'product-' . $deliveryOrder->product_id, -$deliveryOrder->amount, true );
                    }

                }

                DeliveryOrderMeta::whereIn('id', $idsToDelete)->delete();
                
                foreach( $products as $product ){

                    if( in_array( $product['metaId'], $oldDeliveryOrderMetasArray ) ){

                        $removeDeliveryOrderMeta = DeliveryOrderMeta::find( $product['metaId'] );

                        // Remove previous
                        if( $removeDeliveryOrderMeta->product_id ) {
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'product-'.$removeDeliveryOrderMeta->product_id, -$removeDeliveryOrderMeta->amount, true  );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'product-'.$removeDeliveryOrderMeta->product_id, $product['quantity'], false );
                        }elseif( $removeDeliveryOrderMeta->variant_id ) {
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $removeDeliveryOrderMeta->product_id, $removeDeliveryOrderMeta->variant_id, -$removeDeliveryOrderMeta->amount, true  );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseVariantQuantity( $request->warehouse, $removeDeliveryOrderMeta->product_id, $removeDeliveryOrderMeta->variant_id, $product['quantity'], false  );
                        }else{
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'bundle'.$removeDeliveryOrderMeta->bundle_id, -$removeDeliveryOrderMeta->amount, true  );
                            $warehouseAdjustment = AdjustmentService::adjustWarehouseQuantity( $request->warehouse, 'bundle'.$removeDeliveryOrderMeta->bundle_id, $product['quantity'], false );
                        }
                        
                        $removeDeliveryOrderMeta->delivery_order_id = $updateDeliveryOrder->id;
                        $removeDeliveryOrderMeta->amount = $product['quantity'];
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

                            $deliveryOrderMetaCreate = DeliveryOrderMeta::create([
                                'delivery_order_id' => $updateDeliveryOrder->id,
                                'product_id' => $product['id'],
                                'quantity' => $product['quantity'],
                                'product_id' => $type == 'product' ? $identifier : null,
                                'variant_id' => $type == 'variant' ? $identifier :null,
                                'bundle_id' => $type == 'bundle' ? $identifier :null,
                                'tax_method_id' => $request->tax_method,
                                'status' => 10,
                            ]);
                        } else{
                            $removeDeliveryOrderMeta = DeliveryOrderMeta::find( $product['metaId'] );
                            $removeDeliveryOrderMeta->delete();
                        }
                    }
    
                }
            } else {
                foreach ($oldDeliveryOrderMetas as $meta) {
                    $meta->delete();
                }
            }

            $updateDeliveryOrder->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.delivery_orders' ) ) ] ),
        ] );
    }

     public static function allDeliveryOrders( $request ) {

        $deliveryOrders = DeliveryOrder::with( [ 'invoice', 'salesman', 'customer','warehouse', 'supplier'] )->select( 'delivery_orders.*');

        $filterObject = self::filter( $request, $deliveryOrders );
        $deliveryOrder = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $deliveryOrder->orderBy( 'delivery_orders.created_at', $dir );
                    break;
                case 2:
                    $deliveryOrder->orderBy( 'delivery_orders.title', $dir );
                    break;
                case 3:
                    $deliveryOrder->orderBy( 'delivery_orders.description', $dir );
                    break;
            }
        }

            $deliveryOrderCount = $deliveryOrder->count();

            $limit = $request->length;
            $offset = $request->start;

            $deliveryOrders = $deliveryOrder->skip( $offset )->take( $limit )->get();

            if ( $deliveryOrders ) {
                $deliveryOrders->append( [
                    'encrypted_id',
                    'attachment_path',
                ] );
            }

            $totalRecord = DeliveryOrder::count();

            $data = [
                'delivery_orders' => $deliveryOrders,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $deliveryOrderCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'delivery_orders.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->reference ) ) {
            $model->where( 'delivery_orders.reference', 'LIKE', '%' . $request->reference . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'delivery_orders.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        if ( !empty( $request->id ) ) {
            $model->where( 'delivery_orders.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->product)) {
            $model->whereHas('deliveryorderMetas', function ($query) use ($request) {
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

    public static function oneDeliveryOrder( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $deliveryOrder = DeliveryOrder::with( [ 'deliveryOrderMetas.product.warehouses','deliveryOrderMetas.bundle','deliveryOrderMetas.variant.product.warehouses', 'taxMethod', 'salesman', 'customer','warehouse', 'supplier'] )->find( $request->id );

        $deliveryOrder->append( [
            'encrypted_id',
            'attachment_path',
        ] );

        $deliveryOrder->taxMethod?->append( [
            'formatted_tax'
        ] );
        
        return response()->json( $deliveryOrder );
    }

    public static function deleteDeliveryOrder( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'deliveryorder.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            DeliveryOrder::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.delivery_orders' ) ) ] ),
        ] );
    }

    public static function updateDeliveryOrderStatus( $request ) {
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateDeliveryOrder = DeliveryOrder::find( $request->id );
            $updateDeliveryOrder->status = $updateDeliveryOrder->status == 10 ? 20 : 10;

            $updateDeliveryOrder->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'delivery_order' => $updateDeliveryOrder,
                    'message_key' => 'update_deliveryorder_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_deliveryorder_failed',
            ], 500 );
        }
    }

    public static function removeDeliveryOrderAttachment( $request ) {

        $updateFarm = DeliveryOrder::find( Helper::decode($request->id) );
        $updateFarm->attachment = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'deliveryorder.attachment' ) ) ] ),
        ] );
    }

    public static function oneDeliveryOrderTransaction( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $deliveryOrder = DeliveryOrderTransaction::with( [ 'delivery_order', 'account'] )->find( $request->id );

        $deliveryOrder->append( [
            'encrypted_id',
        ] );
        
        return response()->json( $deliveryOrder );
    }

    public static function createDeliveryOrderTransaction( $request ) {

        $validator = Validator::make( $request->all(), [
            'delivery_order' => [ 'nullable', 'exists:delivery_orders,id' ],
            'account' => [ 'nullable', 'exists:expenses_accounts,id' ],
            'paid_amount' => [ 'nullable', 'numeric' ,'min:0' ],
            'paid_by' => [ 'nullable' ],

        ] );

        $attributeName = [
            'title' => __( 'deliveryorder.title' ),
            'description' => __( 'deliveryorder.description' ),
            'image' => __( 'deliveryorder.image' ),
            'thumbnail' => __( 'deliveryorder.thumbnail' ),
            'url_slug' => __( 'deliveryorder.url_slug' ),
            'structure' => __( 'deliveryorder.structure' ),
            'size' => __( 'deliveryorder.size' ),
            'phone_number' => __( 'deliveryorder.phone_number' ),
            'sort' => __( 'deliveryorder.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $deliveryOrderCreate = DeliveryOrderTransaction::create([
                'delivery_order_id' => $request->delivery_order,
                'account_id' => $request->account,
                'reference' => Helper::generateDeliveryOrderTransactionNumber(),
                'remarks' => $request->remarks,
                'paid_amount' => $request->paid_amount,
                'paid_by' => $request->paid_by,
                'status' => 10,
            ]);

            $deliveryOrder = DeliveryOrder::find($request->delivery_order);
            $deliveryOrder->paid_amount += $request->paid_amount;
            $deliveryOrder->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.deliveryorder_transactions' ) ) ] ),
        ] );
    }

    public static function updateDeliveryOrderTransactionStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateDeliveryOrderTransaction = DeliveryOrderTransaction::find( $request->id );
            $updateDeliveryOrderTransaction->status = $updateDeliveryOrder->status == 10 ? 20 : 10;
            $updateDeliveryOrderTransaction->save();

            $deliveryOrder = DeliveryOrder::find($updateDeliveryOrder->delivery_order_id);
            if( $updateDeliveryOrderTransaction->status == 10 ) {
                $deliveryOrder->paid_amount += $updateDeliveryOrderTransaction->paid_amount;
            }else{
                $deliveryOrder->paid_amount -= $updateDeliveryOrderTransaction->paid_amount;
            }
            $deliveryOrder->save();

            DB::commit();

            return response()->json( [
                'data' => [
                    'delivery_order' => $updateDeliveryOrder,
                    'message_key' => 'update_deliveryorder_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_deliveryorder_success',
            ], 500 );
        }
    }

    public static function convertDeliveryOrder($request) {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);
    
        DB::beginTransaction();
    
        try {
            $deliveryOrder = DeliveryOrder::find($request->id);
    
            if (!$deliveryOrder) {
                throw new \Exception('Sales Order not found.');
            }

            if ($deliveryOrder->status != 10) {
                throw new \Exception('Sales Order not available.');
            }
    
            $deliveryOrder = new DeliveryOrder();
            $deliveryOrder->delivery_order_id = $deliveryOrder->id;
            $deliveryOrder->customer_id = $deliveryOrder->customer_id;
            $deliveryOrder->salesman_id = $deliveryOrder->salesman_id;
            $deliveryOrder->warehouse_id = $deliveryOrder->warehouse_id;
            $deliveryOrder->supplier_id = $deliveryOrder->supplier_id;
            $deliveryOrder->tax_method_id = $deliveryOrder->tax_method_id;
            $deliveryOrder->order_tax = $deliveryOrder->order_tax;
            $deliveryOrder->order_discount = $deliveryOrder->order_discount;
            $deliveryOrder->shipping_cost = $deliveryOrder->shipping_cost;
            $deliveryOrder->remarks = $deliveryOrder->remarks;
            $deliveryOrder->attachment = $deliveryOrder->attachment;
            $deliveryOrder->status = 10;
            $deliveryOrder->amount = $deliveryOrder->amount;
            $deliveryOrder->original_amount = $deliveryOrder->original_amount;
            $deliveryOrder->final_amount = $deliveryOrder->final_amount;
            $deliveryOrder->paid_amount = $deliveryOrder->paid_amount;
            $deliveryOrder->reference = Helper::generateSalesOrderNumber();
            $deliveryOrder->save();
    
            $deliveryOrderMetas = $deliveryOrder->deliveryorderMetas;
            foreach ($deliveryOrderMetas as $deliveryOrderMeta) {
                $deliveryOrderMeta = new DeliveryOrderMeta();
                $deliveryOrderMeta->delivery_order_id = $deliveryOrder->id;
                $deliveryOrderMeta->product_id = $deliveryOrderMeta->product_id;
                $deliveryOrderMeta->custom_discount = $deliveryOrderMeta->custom_discount;
                $deliveryOrderMeta->custom_tax = $deliveryOrderMeta->custom_tax;
                $deliveryOrderMeta->custom_shipping_cost = $deliveryOrderMeta->custom_shipping_cost;
                $deliveryOrderMeta->quantity = $deliveryOrderMeta->quantity;
                $deliveryOrderMeta->product_id = $deliveryOrderMeta->product_id;
                $deliveryOrderMeta->variant_id = $deliveryOrderMeta->variant_id;
                $deliveryOrderMeta->bundle_id = $deliveryOrderMeta->bundle_id;
                $deliveryOrderMeta->tax_method_id = $deliveryOrderMeta->tax_method_id;
                $deliveryOrderMeta->status = 10;
                $deliveryOrderMeta->save();
            }
    
            $deliveryOrder->status = 14;
            $deliveryOrder->save();
    
            DB::commit();
    
            return response()->json([
                'data' => [
                    'delivery_order' => $deliveryOrder,
                    'message_key' => 'convert_delivery_order_success',
                ]
            ]);
    
        } catch (\Throwable $th) {
            DB::rollBack();
    
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'convert_delivery_order_failure',
            ], 500);
        }
    }

    public static function sendEmail( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $deliveryOrder = DeliveryOrder::with( [ 'deliveryorderMetas.product.warehouses','deliveryorderMetas.bundle','deliveryorderMetas.variant.product.warehouses', 'taxMethod', 'salesman', 'customer','warehouse', 'supplier'] )->find( $request->id );

            Mail::to( $deliveryOrder->customer->email )->send(new DeliveryOrderMail( $updateDeliveryOrder ));

            DB::commit();

            return response()->json( [
                'data' => [
                    'delivery_order' => $deliveryOrder,
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
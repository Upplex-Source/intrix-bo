<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
};

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
            'products.*.id' => [ 'nullable', 'exists:products,id' ],
            'products.*.quantity' => [ 'nullable' ],
            'warehouse' => [ 'nullable', 'exists:warehouses,id' ],
            'supplier' => [ 'nullable', 'exists:suppliers,id' ],
            'salesman' => [ 'nullable', 'exists:administrators,id' ],
            'customer' => [ 'nullable', 'exists:users,id' ],
            'discount' => [ 'nullable', 'numeric' ,'min:0' ],
            'shipping_cost' => [ 'nullable', 'numeric' ,'min:0' ],
            'tax_type' => [ 'nullable', 'in:1,2' ],

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
                    $productData = Product::find( $product['id'] );
                    // turnoff warehouse price
                    // if( $request->warehouse ){
                    //     $warehouseProduct = $productData->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                    //     $amount += $warehouseProduct ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->price * $product['quantity'];
                    // } else {
                    //     $amount += $productData->price * $product['quantity'];
                    // }

                    $amount += $productData->price * $product['quantity'];

                }
            }

            $taxAmount = $amount * Helper::taxTypes()[$request->tax_type ?? 1]['percentage'];
            $finalAmount = $amount - $request->discount + $taxAmount;


            $sales_orderCreate = SalesOrder::create([
                'supplier_id' => $request->supplier,
                'warehouse_id' => $request->warehouse,
                'salesman_id' => $request->salesman,
                'customer_id' => $request->customer,
                // 'remarks' => $request->remarks,
                'reference' => Helper::generateSalesOrderNumber(),
                'tax_type' => $request->tax_type ?? 1,
                'amount' => $amount,
                'original_amount' => $amount,
                'paid_amount' => $paidAmount,
                'final_amount' => $amount,
                'order_tax' => $taxAmount,
                'order_discount' => $request->discount,
                'shipping_cost' => $request->shipping_cost,
                'status' => 10,
            ]);

            $attachment = explode( ',', $request->attachment );

            $attachmentFiles = FileManager::whereIn( 'id', $attachment )->get();

            // if ( $attachmentFiles ) {
            //     foreach ( $attachmentFiles as $attachmentFile ) {

            //         $fileName = explode( '/', $attachmentFile->file );
            //         $fileExtention = pathinfo($fileName[1])['extension'];

            //         $target = 'sales_order/' . $sales_orderCreate->id . '/' . $fileName[1];
            //         Storage::disk( 'public' )->move( $attachmentFile->file, $target );

            //        $sales_orderCreate->attachment = $target;
            //        $sales_orderCreate->save();

            //         $attachmentFile->status = 10;
            //         $attachmentFile->save();

            //     }
            // }

            $products = $request->products;

            if( $products ){
                foreach( $products as $product ){

                    $sales_orderMetaCreate = SalesOrderMeta::create([
                        'sales_order_id' => $sales_orderCreate->id,
                        'product_id' => $product['id'],
                        // 'custom_discount' => $product['custom_discount'],
                        // 'custom_tax' => $product['custom_tax'],
                        // 'custom_shipping_cost' => $product['custom_shipping_cost'],
                        'quantity' => $product['quantity'],
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
            'products' => [ 'nullable' ],
            'products.*.id' => [ 'nullable', 'exists:products,id' ],
            'products.*.quantity' => [ 'nullable' ],
            'warehouse' => [ 'nullable', 'exists:warehouses,id' ],
            'supplier' => [ 'nullable', 'exists:suppliers,id' ],
            'salesman' => [ 'nullable', 'exists:administrators,id' ],
            'customer' => [ 'nullable', 'exists:users,id' ],
            'discount' => [ 'nullable', 'numeric' ,'min:0' ],
            'shipping_cost' => [ 'nullable', 'numeric' ,'min:0' ],
            'tax_type' => [ 'nullable', 'in:1,2' ],
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
                    $productData = Product::find( $product['id'] );
                    // turnoff warehouse price
                    // if( $request->warehouse ){
                    //     $warehouseProduct = $productData->warehouses->where('pivot.warehouse_id', $request->warehouse)->first();
                    //     $amount += $warehouseProduct ? $warehouseProduct->pivot->price * $product['quantity'] : $productData->price * $product['quantity'];
                    // } else {
                    //     $amount += $productData->price * $product['quantity'];
                    // }

                    $amount += $productData->price * $product['quantity'];

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

            $oldSalesOrderMetas = $updateSalesOrder->SalesOrderMetas;
            $oldSalesOrderMetasArray = $oldSalesOrderMetas->pluck('id')->toArray();
            $products = $request->products;

            if( $products ) {

                $incomingProductIds = array_column($products, 'metaId');
    
                $incomingProductIds = array_filter($incomingProductIds, function ($id) {
                    return $id !== null && $id !== 'null';
                });

                $idsToDelete = array_diff($oldSalesOrderMetasArray, $incomingProductIds);

                SalesOrderMeta::whereIn('id', $idsToDelete)->delete();
                
                foreach( $products as $product ){

                    if( in_array( $product['metaId'], $oldSalesOrderMetasArray ) ){
                        $removeSalesOrderMeta = SalesOrderMeta::find( $product['metaId'] );
                        $removeSalesOrderMeta->sales_order_id = $updateSalesOrder->id;
                        $removeSalesOrderMeta->amount = $product['quantity'];
                    } else {

                        if( $product['metaId'] == 'null' ){
                            $sales_orderMetaCreate = SalesOrderMeta::create([
                                'sales_order_id' => $updateSalesOrder->id,
                                'product_id' => $product['id'],
                                'quantity' => $product['quantity'],
                                // 'custom_discount' => $product['custom_discount'],
                                // 'custom_tax' => $product['custom_tax'],
                                // 'custom_shipping_cost' => $product['custom_shipping_cost'],
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

        $sales_orders = SalesOrder::with( [ 'quotation', 'salesman', 'customer','warehouse', 'supplier'] )->select( 'sales_orders.*');

        $filterObject = self::filter( $request, $sales_orders );
        $sales_order = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $sales_order->orderBy( 'sales_orders.created_at', $dir );
                    break;
                case 2:
                    $sales_order->orderBy( 'sales_orders.title', $dir );
                    break;
                case 3:
                    $sales_order->orderBy( 'sales_orders.description', $dir );
                    break;
            }
        }

            $sales_orderCount = $sales_order->count();

            $limit = $request->length;
            $offset = $request->start;

            $sales_orders = $sales_order->skip( $offset )->take( $limit )->get();

            if ( $sales_orders ) {
                $sales_orders->append( [
                    'encrypted_id',
                    'attachment_path',
                ] );
            }

            $totalRecord = SalesOrder::count();

            $data = [
                'sales_orders' => $sales_orders,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $sales_orderCount : $totalRecord,
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
            $model->whereHas('SalesOrderMetas', function ($query) use ($request) {
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

        if (!empty($request->quotation)) {
            $model->whereHas('quotation', function ($query) use ($request) {
                $query->where('reference', 'LIKE', '%' . $request->quotation . '%');
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

        $sales_order = SalesOrder::with( [ 'quotation','SalesOrderMetas.product','salesman', 'customer','warehouse', 'supplier'] )->find( $request->id );

        $sales_order->append( [
            'encrypted_id',
            'attachment_path',
        ] );
        
        return response()->json( $sales_order );
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

        $sales_order = SalesOrderTransaction::with( [ 'sales_order', 'account'] )->find( $request->id );

        $sales_order->append( [
            'encrypted_id',
        ] );
        
        return response()->json( $sales_order );
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

            $sales_orderCreate = SalesOrderTransaction::create([
                'sales_order_id' => $request->sales_order,
                'account_id' => $request->account,
                'reference' => Helper::generateSalesOrderTransactionNumber(),
                'remarks' => $request->remarks,
                'paid_amount' => $request->paid_amount,
                'paid_by' => $request->paid_by,
                'status' => 10,
            ]);

            $sales_order = SalesOrder::find($request->sales_order);
            $sales_order->paid_amount += $request->paid_amount;
            $sales_order->save();

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

            $sales_order = SalesOrder::find($updateSalesOrder->sales_order_id);
            if( $updateSalesOrderTransaction->status == 10 ) {
                $sales_order->paid_amount += $updateSalesOrderTransaction->paid_amount;
            }else{
                $sales_order->paid_amount -= $updateSalesOrderTransaction->paid_amount;
            }
            $sales_order->save();

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

    public static function convertSalesOrder($request) {
        $request->merge([
            'id' => Helper::decode($request->id),
        ]);
    
        DB::beginTransaction();
    
        try {
            $sales_order = SalesOrder::find($request->id);
    
            if (!$sales_order) {
                throw new \Exception('SalesOrder not found.');
            }
    
            $salesOrder = new SalesOrder();
            $salesOrder->sales_order_id = $sales_order->id;
            $salesOrder->customer_id = $sales_order->customer_id;
            $salesOrder->salesman_id = $sales_order->salesman_id;
            $salesOrder->warehouse_id = $sales_order->warehouse_id;
            $salesOrder->supplier_id = $sales_order->supplier_id;
            $salesOrder->order_tax = $sales_order->order_tax;
            $salesOrder->order_discount = $sales_order->order_discount;
            $salesOrder->shipping_cost = $sales_order->shipping_cost;
            $salesOrder->remarks = $sales_order->remarks;
            $salesOrder->status = 10;
            $salesOrder->amount = $sales_order->amount;
            $salesOrder->original_amount = $sales_order->original_amount;
            $salesOrder->final_amount = $sales_order->final_amount;
            $salesOrder->paid_amount = $sales_order->paid_amount;
            $salesOrder->reference = Helper::generateSalesOrderNumber();
            $salesOrder->save();
    
            $SalesOrderMetas = $sales_order->SalesOrderMetas;
            foreach ($SalesOrderMetas as $sales_orderMeta) {
                $salesOrderMeta = new SalesOrderMeta();
                $salesOrderMeta->sales_order_id = $salesOrder->id;
                $salesOrderMeta->product_id = $sales_orderMeta->product_id;
                $salesOrderMeta->custom_discount = $sales_orderMeta->custom_discount;
                $salesOrderMeta->custom_tax = $sales_orderMeta->custom_tax;
                $salesOrderMeta->custom_shipping_cost = $sales_orderMeta->custom_shipping_cost;
                $salesOrderMeta->quantity = $sales_orderMeta->quantity;
                $salesOrderMeta->status = 10;
                $salesOrderMeta->save();
            }
    
            $sales_order->status = 12;
            $sales_order->save();
    
            DB::commit();
    
            return response()->json([
                'data' => [
                    'sales_order' => $salesOrder,
                    'message_key' => 'convert_sales_order_success',
                ]
            ]);
    
        } catch (\Throwable $th) {
            DB::rollBack();
    
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'convert_sales_order_failure',
            ], 500);
        }
    }
    
}
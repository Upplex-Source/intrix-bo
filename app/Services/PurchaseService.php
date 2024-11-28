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
    Purchase,
    PurchaseMeta,
    PurchaseTransaction,
    Booking,
    FileManager,
    Product,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PurchaseService
{

    public static function createPurchase( $request ) {

        $validator = Validator::make( $request->all(), [
            'purchase_date' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
            'attachment' => [ 'nullable' ],
            'products' => [ 'nullable' ],
            'products.*.id' => [ 'nullable', 'exists:products,id' ],
            'products.*.quantity' => [ 'nullable' ],
            'warehouse' => [ 'nullable', 'exists:warehouses,id' ],
            'supplier' => [ 'nullable', 'exists:suppliers,id' ],
            'discount' => [ 'nullable', 'numeric' ,'min:0' ],
            'shipping_cost' => [ 'nullable', 'numeric' ,'min:0' ],
            'tax_type' => [ 'nullable', 'in:1,2' ],

        ] );

        $attributeName = [
            'title' => __( 'purchase.title' ),
            'description' => __( 'purchase.description' ),
            'image' => __( 'purchase.image' ),
            'thumbnail' => __( 'purchase.thumbnail' ),
            'url_slug' => __( 'purchase.url_slug' ),
            'structure' => __( 'purchase.structure' ),
            'size' => __( 'purchase.size' ),
            'phone_number' => __( 'purchase.phone_number' ),
            'sort' => __( 'purchase.sort' ),
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

            $purchaseCreate = Purchase::create([
                'causer_id' => auth()->user()->id,
                'supplier_id' => $request->supplier,
                'warehouse_id' => $request->warehouse,
                'remarks' => $request->remarks,
                'reference' => Helper::generatePurchaseNumber(),
                'purchase_date' => $request->purchase_date,
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

            if ( $attachmentFiles ) {
                foreach ( $attachmentFiles as $attachmentFile ) {

                    $fileName = explode( '/', $attachmentFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'purchase/' . $purchaseCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $purchaseCreate->attachment = $target;
                   $purchaseCreate->save();

                    $attachmentFile->status = 10;
                    $attachmentFile->save();

                }
            }

            $products = $request->products;

            if( $products ){
                foreach( $products as $product ){

                    $purchaseMetaCreate = PurchaseMeta::create([
                        'purchase_id' => $purchaseCreate->id,
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.purchases' ) ) ] ),
        ] );
    }
    
    public static function updatePurchase( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'purchase_date' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
            'attachment' => [ 'nullable' ],
            'products' => [ 'nullable' ],
            'products.*.id' => [ 'nullable', 'exists:products,id' ],
            'products.*.quantity' => [ 'nullable' ],
            'warehouse' => [ 'nullable', 'exists:warehouses,id' ],
            'supplier' => [ 'nullable', 'exists:suppliers,id' ],
            'discount' => [ 'nullable', 'numeric' ,'min:0' ],
            'shipping_cost' => [ 'nullable', 'numeric' ,'min:0' ],
            'tax_type' => [ 'nullable', 'in:1,2' ],
        ] );

        $attributeName = [
            'title' => __( 'purchase.title' ),
            'description' => __( 'purchase.description' ),
            'image' => __( 'purchase.image' ),
            'thumbnail' => __( 'purchase.thumbnail' ),
            'url_slug' => __( 'purchase.url_slug' ),
            'structure' => __( 'purchase.structure' ),
            'size' => __( 'purchase.size' ),
            'phone_number' => __( 'purchase.phone_number' ),
            'sort' => __( 'purchase.sort' ),
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

            $updatePurchase = Purchase::find( $request->id );

            $updatePurchase->remarks = $request->remarks ?? $updatePurchase->remarks;
            $updatePurchase->causer_id = auth()->user()->id;
            $updatePurchase->warehouse_id = $request->warehouse ?? $updatePurchase->warehouse_id;
            $updatePurchase->purchase_date = $request->purchase_date ?? $updatePurchase->purchase_date;
            $updatePurchase->tax_type = $request->tax_type ?? 1 ?? $updatePurchase->tax_type;
            $updatePurchase->amount = $amount;
            $updatePurchase->original_amount = $amount;
            $updatePurchase->paid_amount = $paidAmount;
            $updatePurchase->final_amount = $amount;
            $updatePurchase->order_tax = $taxAmount;
            $updatePurchase->order_discount = $request->discount;
            $updatePurchase->shipping_cost = $request->shipping_cost;

            $attachment = explode( ',', $request->attachment );

            $attachmentFiles = FileManager::whereIn( 'id', $attachment )->get();

            if ( $attachmentFiles ) {
                foreach ( $attachmentFiles as $attachmentFile ) {

                    $fileName = explode( '/', $attachmentFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'purchase/' . $updatePurchase->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $updatePurchase->attachment = $target;
                   $updatePurchase->save();

                    $attachmentFile->status = 10;
                    $attachmentFile->save();

                }
            }

            $oldPurchaseMetas = $updatePurchase->purchaseMetas;
            $oldPurchaseMetasArray = $oldPurchaseMetas->pluck('id')->toArray();
            $products = $request->products;

            if( $products ) {

                $incomingProductIds = array_column($products, 'metaId');
    
                $incomingProductIds = array_filter($incomingProductIds, function ($id) {
                    return $id !== null && $id !== 'null';
                });

                $idsToDelete = array_diff($oldPurchaseMetasArray, $incomingProductIds);

                PurchaseMeta::whereIn('id', $idsToDelete)->delete();
                
                foreach( $products as $product ){

                    if( in_array( $product['metaId'], $oldPurchaseMetasArray ) ){
                        $removePurchaseMeta = PurchaseMeta::find( $product['metaId'] );
                        $removePurchaseMeta->purchase_id = $updatePurchase->id;
                        $removePurchaseMeta->amount = $product['quantity'];
                    } else {

                        if( $product['metaId'] == 'null' ){
                            $purchaseMetaCreate = PurchaseMeta::create([
                                'purchase_id' => $updatePurchase->id,
                                'product_id' => $product['id'],
                                'quantity' => $product['quantity'],
                                // 'custom_discount' => $product['custom_discount'],
                                // 'custom_tax' => $product['custom_tax'],
                                // 'custom_shipping_cost' => $product['custom_shipping_cost'],
                                'status' => 10,
                            ]);
                        } else{
                            $removePurchaseMeta = PurchaseMeta::find( $product['metaId'] );
                            $removePurchaseMeta->delete();
                        }
                    }
    
                }
            } else {
                foreach ($oldPurchaseMetas as $meta) {
                    $meta->delete();
                }
            }

            $updatePurchase->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.purchases' ) ) ] ),
        ] );
    }

     public static function allPurchases( $request ) {

        $purchases = Purchase::with( [ 'purchaseTransactions', 'purchaseMetas.product','warehouse', 'supplier'] )->select( 'purchases.*');

        $filterObject = self::filter( $request, $purchases );
        $purchase = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $purchase->orderBy( 'purchases.created_at', $dir );
                    break;
                case 2:
                    $purchase->orderBy( 'purchases.title', $dir );
                    break;
                case 3:
                    $purchase->orderBy( 'purchases.description', $dir );
                    break;
            }
        }

            $purchaseCount = $purchase->count();

            $limit = $request->length;
            $offset = $request->start;

            $purchases = $purchase->skip( $offset )->take( $limit )->get();

            if ( $purchases ) {
                $purchases->append( [
                    'encrypted_id',
                    'attachment_path',
                    'due_amount',
                    'payment_status',
                ] );
            }

            $totalRecord = Purchase::count();

            $data = [
                'purchases' => $purchases,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $purchaseCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'purchases.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->reference ) ) {
            $model->where( 'purchases.reference', 'LIKE', '%' . $request->reference . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'purchases.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        if ( !empty( $request->id ) ) {
            $model->where( 'purchases.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->product)) {
            $model->whereHas('purchaseMetas', function ($query) use ($request) {
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

    public static function onePurchase( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $purchase = Purchase::with( [ 'purchaseTransactions', 'purchaseMetas.product','warehouse', 'supplier'] )->find( $request->id );

        $purchase->append( [
            'encrypted_id',
            'attachment_path',
            'due_amount',
            'payment_status',
        ] );
        
        return response()->json( $purchase );
    }

    public static function deletePurchase( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'purchase.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Purchase::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.purchases' ) ) ] ),
        ] );
    }

    public static function updatePurchaseStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updatePurchase = Purchase::find( $request->id );
            $updatePurchase->status = $updatePurchase->status == 10 ? 20 : 10;

            $updatePurchase->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'purchase' => $updatePurchase,
                    'message_key' => 'update_purchase_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_purchase_failed',
            ], 500 );
        }
    }

    public static function removePurchaseAttachment( $request ) {

        $updateFarm = Purchase::find( Helper::decode($request->id) );
        $updateFarm->attachment = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'purchase.attachment' ) ) ] ),
        ] );
    }

    public static function onePurchaseTransaction( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $purchase = PurchaseTransaction::with( [ 'purchase', 'account'] )->find( $request->id );

        $purchase->append( [
            'encrypted_id',
        ] );
        
        return response()->json( $purchase );
    }

    public static function createPurchaseTransaction( $request ) {

        $validator = Validator::make( $request->all(), [
            'purchase' => [ 'nullable', 'exists:purchases,id' ],
            'account' => [ 'nullable', 'exists:expenses_accounts,id' ],
            'paid_amount' => [ 'nullable', 'numeric' ,'min:0' ],
            'paid_by' => [ 'nullable' ],

        ] );

        $attributeName = [
            'title' => __( 'purchase.title' ),
            'description' => __( 'purchase.description' ),
            'image' => __( 'purchase.image' ),
            'thumbnail' => __( 'purchase.thumbnail' ),
            'url_slug' => __( 'purchase.url_slug' ),
            'structure' => __( 'purchase.structure' ),
            'size' => __( 'purchase.size' ),
            'phone_number' => __( 'purchase.phone_number' ),
            'sort' => __( 'purchase.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $purchaseCreate = PurchaseTransaction::create([
                'purchase_id' => $request->purchase,
                'account_id' => $request->account,
                'reference' => Helper::generatePurchaseTransactionNumber(),
                'remarks' => $request->remarks,
                'paid_amount' => $request->paid_amount,
                'paid_by' => $request->paid_by,
                'status' => 10,
            ]);

            $purchase = Purchase::find($request->purchase);
            $purchase->paid_amount += $request->paid_amount;
            $purchase->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.purchase_transactions' ) ) ] ),
        ] );
    }

    public static function updatePurchaseTransactionStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updatePurchaseTransaction = PurchaseTransaction::find( $request->id );
            $updatePurchaseTransaction->status = $updatePurchase->status == 10 ? 20 : 10;
            $updatePurchaseTransaction->save();

            $purchase = Purchase::find($updatePurchase->purchase_id);
            if( $updatePurchaseTransaction->status == 10 ) {
                $purchase->paid_amount += $updatePurchaseTransaction->paid_amount;
            }else{
                $purchase->paid_amount -= $updatePurchaseTransaction->paid_amount;
            }
            $purchase->save();

            DB::commit();

            return response()->json( [
                'data' => [
                    'purchase' => $updatePurchase,
                    'message_key' => 'update_purchase_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_purchase_success',
            ], 500 );
        }
    }
}
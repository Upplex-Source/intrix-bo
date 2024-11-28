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

            $quotationCreate = Quotation::create([
                'supplier_id' => $request->supplier,
                'warehouse_id' => $request->warehouse,
                'salesman_id' => $request->salesman,
                'customer_id' => $request->customer,
                'remarks' => $request->remarks,
                'reference' => Helper::generateQuotationNumber(),
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

                    $quotationMetaCreate = QuotationMeta::create([
                        'quotation_id' => $quotationCreate->id,
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

                QuotationMeta::whereIn('id', $idsToDelete)->delete();
                
                foreach( $products as $product ){

                    if( in_array( $product['metaId'], $oldQuotationMetasArray ) ){
                        $removeQuotationMeta = QuotationMeta::find( $product['metaId'] );
                        $removeQuotationMeta->quotation_id = $updateQuotation->id;
                        $removeQuotationMeta->amount = $product['quantity'];
                    } else {

                        if( $product['metaId'] == 'null' ){
                            $quotationMetaCreate = QuotationMeta::create([
                                'quotation_id' => $updateQuotation->id,
                                'product_id' => $product['id'],
                                'quantity' => $product['quantity'],
                                // 'custom_discount' => $product['custom_discount'],
                                // 'custom_tax' => $product['custom_tax'],
                                // 'custom_shipping_cost' => $product['custom_shipping_cost'],
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

        $quotation = Quotation::with( [ 'quotationMetas.product','salesman', 'customer','warehouse', 'supplier'] )->find( $request->id );

        $quotation->append( [
            'encrypted_id',
            'attachment_path',
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

            $updateQuotation = Quotation::find( $request->id );

            Mail::to( $updateQuotation->customer->email )->send(new QuotationMail( $updateQuotation ));

            DB::commit();

            return response()->json( [
                'data' => [
                    'quotation' => $updateQuotation,
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
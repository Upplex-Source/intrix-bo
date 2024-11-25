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
    Adjustment,
    AdjustmentMeta,
    Booking,
    FileManager,
    Warehouse,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AdjustmentService
{

    public static function createAdjustment( $request ) {

        $validator = Validator::make( $request->all(), [
            'adjustment_date' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
            'attachment' => [ 'nullable' ],
            'products' => [ 'nullable' ],
            'products.*.id' => [ 'nullable', 'exists:products,id' ],
            'products.*.quantity' => [ 'nullable' ],
            'warehouse' => [ 'nullable', 'exists:warehouses,id' ],
        ] );

        $attributeName = [
            'title' => __( 'adjustment.title' ),
            'description' => __( 'adjustment.description' ),
            'image' => __( 'adjustment.image' ),
            'thumbnail' => __( 'adjustment.thumbnail' ),
            'url_slug' => __( 'adjustment.url_slug' ),
            'structure' => __( 'adjustment.structure' ),
            'size' => __( 'adjustment.size' ),
            'phone_number' => __( 'adjustment.phone_number' ),
            'sort' => __( 'adjustment.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $adjustmentCreate = Adjustment::create([
                'causer_id' => auth()->user()->id,
                'warehouse_id' => $request->warehouse,
                'remarks' => $request->remarks,
                'reference' => Helper::generateAdjustmentNumber(),
                'adjustment_date' => $request->adjustment_date,
            ]);

            $attachment = explode( ',', $request->attachment );
            $attachmentFiles = FileManager::whereIn( 'id', $attachment )->get();

            if ( $attachmentFiles ) {
                foreach ( $attachmentFiles as $attachmentFile ) {

                    $fileName = explode( '/', $attachmentFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'adjustment/' . $adjustmentCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $adjustmentCreate->attachment = $target;
                   $adjustmentCreate->save();

                    $attachmentFile->status = 10;
                    $attachmentFile->save();

                }
            }

            $products = $request->products;

            if( $products ){
                foreach( $products as $product ){

                    $warehouseAdjustment = self::adjustWarehouseQuantity( $request->warehouse, $product['id'], $product['quantity'] );

                    $adjustmentMetaCreate = AdjustmentMeta::create([
                        'adjustment_id' => $adjustmentCreate->id,
                        'product_id' => $product['id'],
                        // 'variant_id' => $product['variant_id'],
                        'amount' => $product['quantity'],
                        // 'variant_id' => $product['variant_id'],
                        'original_amount' => $warehouseAdjustment['original_quantity'],
                        'final_amount' => $warehouseAdjustment['updated_quantity'],
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.adjustments' ) ) ] ),
        ] );
    }
    
    public static function updateAdjustment( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'adjustment_date' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
            'attachment' => [ 'nullable' ],
            'products' => [ 'nullable' ],
            'products.*.id' => [ 'nullable', 'exists:products,id' ],
            'products.*.quantity' => [ 'nullable' ],
            'warehouse' => [ 'nullable', 'exists:warehouses,id' ],
        ] );

        $attributeName = [
            'title' => __( 'adjustment.title' ),
            'description' => __( 'adjustment.description' ),
            'image' => __( 'adjustment.image' ),
            'thumbnail' => __( 'adjustment.thumbnail' ),
            'url_slug' => __( 'adjustment.url_slug' ),
            'structure' => __( 'adjustment.structure' ),
            'size' => __( 'adjustment.size' ),
            'phone_number' => __( 'adjustment.phone_number' ),
            'sort' => __( 'adjustment.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateAdjustment = Adjustment::find( $request->id );

            $updateAdjustment->remarks = $request->remarks ?? $updateAdjustment->remarks;
            $updateAdjustment->causer_id = auth()->user()->id;
            $updateAdjustment->warehouse_id = $request->warehouse ?? $updateAdjustment->warehouse_id;
            $updateAdjustment->adjustment_date = $request->adjustment_date ?? $updateAdjustment->adjustment_date;

            $attachment = explode( ',', $request->attachment );

            $attachmentFiles = FileManager::whereIn( 'id', $attachment )->get();

            if ( $attachmentFiles ) {
                foreach ( $attachmentFiles as $attachmentFile ) {

                    $fileName = explode( '/', $attachmentFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'adjustment/' . $updateAdjustment->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $updateAdjustment->attachment = $target;
                   $updateAdjustment->save();

                    $attachmentFile->status = 10;
                    $attachmentFile->save();

                }
            }

            $oldAdjustmentMetas = $updateAdjustment->adjustmentMetas;
            $oldAdjustmentMetasArray = $oldAdjustmentMetas->pluck('id')->toArray();
            $products = $request->products;

            if( $products ) {

                $incomingProductIds = array_column($products, 'metaId');
    
                $incomingProductIds = array_filter($incomingProductIds, function ($id) {
                    return $id !== null && $id !== 'null';
                });

                $idsToDelete = array_diff($oldAdjustmentMetasArray, $incomingProductIds);

                AdjustmentMeta::whereIn('id', $idsToDelete)->delete();
                
                foreach( $products as $product ){

                    if( in_array( $product['metaId'], $oldAdjustmentMetasArray ) ){
                        $removeAdjustmentMeta = AdjustmentMeta::find( $product['metaId'] );
                        $removeAdjustmentMeta->adjustment_id = $updateAdjustment->id;
                        $removeAdjustmentMeta->amount = $product['quantity'];
                    } else {

                        if( $product['metaId'] == 'null' ){
                            $adjustmentMetaCreate = AdjustmentMeta::create([
                                'adjustment_id' => $updateAdjustment->id,
                                'product_id' => $product['id'],
                                // 'variant_id' => $product['variant_id'],
                                'amount' => $product['quantity'],
                                // 'variant_id' => $product['variant_id'],
                                // 'original_amount' => $product['quantity'],
                                // 'final_amount' => $product['quantity'],
                                'status' => 10,
                            ]);
                        } else{
                            $removeAdjustmentMeta = AdjustmentMeta::find( $product['metaId'] );
                            $removeAdjustmentMeta->delete();
                        }
                    }
    
                }
            } else {
                foreach ($oldAdjustmentMetas as $meta) {
                    $meta->delete();
                }
            }

            $updateAdjustment->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.adjustments' ) ) ] ),
        ] );
    }

     public static function allAdjustments( $request ) {

        $adjustments = Adjustment::with( ['AdjustmentMetas.product','warehouse'] )->select( 'adjustments.*');

        $filterObject = self::filter( $request, $adjustments );
        $adjustment = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $adjustment->orderBy( 'adjustments.created_at', $dir );
                    break;
                case 2:
                    $adjustment->orderBy( 'adjustments.title', $dir );
                    break;
                case 3:
                    $adjustment->orderBy( 'adjustments.description', $dir );
                    break;
            }
        }

            $adjustmentCount = $adjustment->count();

            $limit = $request->length;
            $offset = $request->start;

            $adjustments = $adjustment->skip( $offset )->take( $limit )->get();

            if ( $adjustments ) {
                $adjustments->append( [
                    'encrypted_id',
                    'attachment_path',
                ] );
            }

            $totalRecord = Adjustment::count();

            $data = [
                'adjustments' => $adjustments,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $adjustmentCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->reference ) ) {
            $model->where( 'adjustments.reference', 'LIKE', '%' . $request->reference . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'adjustments.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'adjustment.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if (!empty($request->warehouse)) {
            $model->whereHas('warehouse', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->warehouse . '%');
            });
            $filter = true;
        }

        if (!empty($request->product)) {
            $model->whereHas('adjustmentMetas', function ($query) use ($request) {
                $query->whereHas('product', function ($query) use ($request) {
                    $query->where('title', 'LIKE', '%' . $request->product . '%');
                });
            });
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneAdjustment( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $adjustment = Adjustment::with( ['AdjustmentMetas.product','warehouse'] )->find( $request->id );

        $adjustment->append( ['encrypted_id','attachment_path'] );
        
        return response()->json( $adjustment );
    }

    public static function deleteAdjustment( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'adjustment.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Adjustment::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.adjustments' ) ) ] ),
        ] );
    }

    public static function updateAdjustmentStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateAdjustment = Adjustment::find( $request->id );
            $updateAdjustment->status = $updateAdjustment->status == 10 ? 20 : 10;

            $updateAdjustment->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'adjustment' => $updateAdjustment,
                    'message_key' => 'update_adjustment_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_adjustment_failed',
            ], 500 );
        }
    }

    public static function removeAdjustmentAttachment( $request ) {

        $updateFarm = Adjustment::find( Helper::decode($request->id) );
        $updateFarm->attachment = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'adjustment.attachment' ) ) ] ),
        ] );
    }

    public static function adjustWarehouseQuantity($warehouseId, $productId, $quantity)
    {
        // Find the warehouse
        $warehouse = Warehouse::find($warehouseId);

        if (!$warehouse) {
            throw new \Exception("Warehouse not found.");
        }

        // Initialize original quantity as 0 (default for new relation)
        $originalQuantity = 0;

        // Check if the product already exists in the warehouse
        $existingProduct = $warehouse->products()->where('product_id', $productId)->first();

        if ($existingProduct) {
            // Get the current quantity
            $originalQuantity = $existingProduct->pivot->quantity;

            // Calculate the new quantity
            $newQuantity = $originalQuantity + $quantity;

            // Ensure quantity doesn't go below zero
            if ($newQuantity < 0) {
                throw new \Exception("Insufficient quantity in warehouse for this product.");
            }

            // Update the pivot table
            $warehouse->products()->updateExistingPivot($productId, [
                'quantity' => $newQuantity,
            ]);
        } else {
            // If no relation exists, set original quantity to 0
            if ($quantity < 0) {
                throw new \Exception("Cannot deduct quantity for a product not in this warehouse.");
            }

            // Create a new pivot relation
            $newQuantity = $quantity;
            $warehouse->products()->attach($productId, [
                'quantity' => $newQuantity,
                'price' => 0, // Default price
                'status' => 1, // Active by default
            ]);
        }

        // Return the original and updated quantities
        return [
            'original_quantity' => $originalQuantity,
            'updated_quantity' => $newQuantity,
        ];
    }

    
}
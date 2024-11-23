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
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AdjustmentService
{

    public static function createAdjustment( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
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
                'title' => $request->title,
                'description' => $request->description,
            ]);

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'adjustment/' . $adjustmentCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $adjustmentCreate->image = $target;
                   $adjustmentCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'adjustment/' . $adjustmentCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $adjustmentCreate->thumbnail = $target;
                   $adjustmentCreate->save();

                    $thumbnailFile->status = 10;
                    $thumbnailFile->save();

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
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
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
    
            $updateAdjustment->title = $request->title;
            $updateAdjustment->description = $request->description;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'adjustment/' . $updateAdjustment->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateAdjustment->image = $target;
                   $updateAdjustment->save();

                    $imageFile->status = 10;
                    $imageFile->save();

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

        $adjustments = Adjustment::select( 'adjustments.*');

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
                    'image_path',
                    'thumbnail_path',
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

        if ( !empty( $request->title ) ) {
            $model->where( 'adjustments.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'adjustments.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_adjustment)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_adjustment . '%');
            });
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
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneAdjustment( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $adjustment = Adjustment::find( $request->id );

        $adjustment->append( ['encrypted_id','image_path'] );
        
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

    public static function removeAdjustmentGalleryImage( $request ) {

        $updateFarm = Adjustment::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}
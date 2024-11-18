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
    Warehouse,
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class WarehouseService
{

    public static function createWarehouse( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'warehouse.title' ),
            'description' => __( 'warehouse.description' ),
            'image' => __( 'warehouse.image' ),
            'thumbnail' => __( 'warehouse.thumbnail' ),
            'url_slug' => __( 'warehouse.url_slug' ),
            'structure' => __( 'warehouse.structure' ),
            'size' => __( 'warehouse.size' ),
            'phone_number' => __( 'warehouse.phone_number' ),
            'sort' => __( 'warehouse.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $warehouseCreate = Warehouse::create([
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

                    $target = 'warehouse/' . $warehouseCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $warehouseCreate->image = $target;
                   $warehouseCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'warehouse/' . $warehouseCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $warehouseCreate->thumbnail = $target;
                   $warehouseCreate->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.warehouses' ) ) ] ),
        ] );
    }
    
    public static function updateWarehouse( $request ) {

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
            'title' => __( 'warehouse.title' ),
            'description' => __( 'warehouse.description' ),
            'image' => __( 'warehouse.image' ),
            'thumbnail' => __( 'warehouse.thumbnail' ),
            'url_slug' => __( 'warehouse.url_slug' ),
            'structure' => __( 'warehouse.structure' ),
            'size' => __( 'warehouse.size' ),
            'phone_number' => __( 'warehouse.phone_number' ),
            'sort' => __( 'warehouse.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateWarehouse = Warehouse::find( $request->id );
    
            $updateWarehouse->title = $request->title;
            $updateWarehouse->description = $request->description;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'warehouse/' . $updateWarehouse->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateWarehouse->image = $target;
                   $updateWarehouse->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateWarehouse->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.warehouses' ) ) ] ),
        ] );
    }

     public static function allWarehouses( $request ) {

        $warehouses = Warehouse::select( 'warehouses.*');

        $filterObject = self::filter( $request, $warehouses );
        $warehouse = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $warehouse->orderBy( 'warehouses.created_at', $dir );
                    break;
                case 2:
                    $warehouse->orderBy( 'warehouses.title', $dir );
                    break;
                case 3:
                    $warehouse->orderBy( 'warehouses.description', $dir );
                    break;
            }
        }

            $warehouseCount = $warehouse->count();

            $limit = $request->length;
            $offset = $request->start;

            $warehouses = $warehouse->skip( $offset )->take( $limit )->get();

            if ( $warehouses ) {
                $warehouses->append( [
                    'encrypted_id',
                    'image_path',
                    'thumbnail_path',
                ] );
            }

            $totalRecord = Warehouse::count();

            $data = [
                'warehouses' => $warehouses,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $warehouseCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'warehouses.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'warehouses.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_warehouse)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_warehouse . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'warehouse.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneWarehouse( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $warehouse = Warehouse::find( $request->id );

        $warehouse->append( ['encrypted_id','image_path'] );
        
        return response()->json( $warehouse );
    }

    public static function deleteWarehouse( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'warehouse.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Warehouse::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.warehouses' ) ) ] ),
        ] );
    }

    public static function updateWarehouseStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateWarehouse = Warehouse::find( $request->id );
            $updateWarehouse->status = $updateWarehouse->status == 10 ? 20 : 10;

            $updateWarehouse->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'warehouse' => $updateWarehouse,
                    'message_key' => 'update_warehouse_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_warehouse_failed',
            ], 500 );
        }
    }

    public static function removeWarehouseGalleryImage( $request ) {

        $updateFarm = Warehouse::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }

    public static function getWareHouses( $request ) {

        $warehouses = Warehouse::select( 'warehouses.*')->where( 'status', 10 )->get();

        return $warehouses;
              
    }
}
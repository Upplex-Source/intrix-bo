<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use App\Models\{
    Farm,
    FarmGallery,
    FileManager,
};

use Helper;

class FarmService
{
    public static function allFarms( $request ) {

        $farm = Farm::with( [ 'owner' ] )->select( 'farms.*' );

        $filterObject = self::filter( $request, $farm );
        $farm = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $farm->orderBy( 'created_at', $dir );
                    break;
            }
        }

        $farmCount = $farm->count();

        $limit = $request->length;
        $offset = $request->start;

        $farms = $farm->skip( $offset )->take( $limit )->get();

        if ( $farms ) {
            $farms->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = Farm::count();

        $data = [
            'farms' => $farms,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $farmCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {
     
        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'farms.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'farms.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->title ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->remarks ) ) {
            $model->where( 'remarks', 'LIKE', '%' . $request->remarks . '%' );
            $filter = true;
        }

        if ( !empty( $request->size ) ) {
            $model->where( 'size', 'LIKE', '%' . $request->size . '%' );
            $filter = true;
        }

        if ( !empty( $request->owner ) ) {
            $model->whereHas( 'owner', function ( $query ) use ( $request ) {
                $query->where( 'name', 'LIKE', '%' . $request->owner . '%' );
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( function( $query ) use ( $request ) {
                $query->where( 'name', 'LIKE', '%' . $request->custom_search . '%' );
            } );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneFarm( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $farm = Farm::with( [ 'owner', 'galleries' ] )->find( $request->id );

        if( $farm ) {
            $farm->append( [
                'encrypted_id',
            ] );

            if( $farm->galleries ) {
                $farm->galleries->append( [
                    'path',
                ] );
            }
        }

        return response()->json( $farm );
    }

    public static function createFarm( $request ) {

        $validator = Validator::make( $request->all(), [
            'owner' => [ 'required', 'exists:users,id' ],
            'title' => [ 'required' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
            'size' => [ 'nullable', 'numeric' ],
            'phone_number' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
            'galleries' => [ 'nullable' ],
        ] );

        $attributeName = [
            'owner' => __( 'farm.owner' ),
            'title' => __( 'farm.name' ),
            'address_1' => __( 'buyer.address_1' ),
            'address_2' => __( 'buyer.address_2' ),
            'city' => __( 'buyer.city' ),
            'state' => __( 'buyer.state' ),
            'postcode' => __( 'buyer.postcode' ),
            'size' => __( 'farm.size' ),
            'phone_number' => __( 'farm.phone_number' ),
            'remarks' => __( 'farm.remarks' ),
            'galleries' => __( 'farm.galleries' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createFarm = Farm::create( [
                'owner_id' => $request->owner,
                'title' => $request->title,
                'size' => $request->size,
                'remarks' => $request->remarks,
                'phone_number' => $request->phone_number,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'state' => $request->state,
                'postcode' => $request->postcode,
            ] );

            $farmGalleries = explode( ',', $request->galleries );
            
            $files = FileManager::whereIn( 'id', $farmGalleries )->get();

            if ( $files ) {
                foreach ( $files as $file ) {

                    $fileName = explode( '/', $file->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'farm-galleries/' . $createFarm->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $file->file, $target );

                    $createFarmGallery = FarmGallery::create( [
                        'farm_id' => $createFarm->id,
                        'title' => '',
                        'file' => $target,
                        'type' => 1,
                        'file_type' => $fileExtention,
                    ] );

                    $file->status = 10;
                    $file->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.farms' ) ) ] ),
        ] );
    }

    public static function updateFarm( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'owner' => [ 'required', 'exists:users,id' ],
            'title' => [ 'required' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
            'size' => [ 'nullable', 'numeric' ],
            'phone_number' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
            'galleries' => [ 'nullable' ],
        ] );

        $attributeName = [
            'owner' => __( 'farm.owner' ),
            'title' => __( 'farm.name' ),
            'address_1' => __( 'buyer.address_1' ),
            'address_2' => __( 'buyer.address_2' ),
            'city' => __( 'buyer.city' ),
            'state' => __( 'buyer.state' ),
            'postcode' => __( 'buyer.postcode' ),
            'size' => __( 'farm.size' ),
            'phone_number' => __( 'farm.phone_number' ),
            'remarks' => __( 'farm.remarks' ),
            'galleries' => __( 'farm.galleries' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateFarm = Farm::find( $request->id );
            $updateFarm->owner_id = $request->owner;
            $updateFarm->title = $request->title;
            $updateFarm->size = $request->size;
            $updateFarm->remarks = $request->remarks;
            $updateFarm->phone_number = $request->phone_number;
            $updateFarm->address_1 = $request->address_1;
            $updateFarm->address_2 = $request->address_2;
            $updateFarm->city = $request->city;
            $updateFarm->state = $request->state;
            $updateFarm->postcode = $request->postcode;
            $updateFarm->save();

            $farmGalleries = explode( ',', $request->galleries );
            
            $files = FileManager::whereIn( 'id', $farmGalleries )->get();

            if ( $files ) {
                foreach ( $files as $file ) {

                    $fileName = explode( '/', $file->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'farm-galleries/' . $updateFarm->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $file->file, $target );

                    $createFarmGallery = FarmGallery::create( [
                        'farm_id' => $updateFarm->id,
                        'title' => '',
                        'file' => $target,
                        'type' => 1,
                        'file_type' => $fileExtention,
                    ] );

                    $file->status = 10;
                    $file->save();

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
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.farms' ) ) ] ),
        ] );
    }

    public static function updateFarmStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateFarm = Farm::find( $request->id );
        $updateFarm->status = $request->status;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.farms' ) ) ] ),
        ] );
    }

    public static function get() {

        $farms = Farm::where( 'status', 10 )->get()->toArray();

        return $farms;
    }

    public static function removeFarmGalleryImage( $request ) {

        $updateFarm = FarmGallery::find( $request->id );
        $updateFarm->delete();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }

}
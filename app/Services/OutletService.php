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
    Outlet,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class OutletService
{

    public static function createOutlet( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'outlet.title' ),
            'description' => __( 'outlet.description' ),
            'image' => __( 'outlet.image' ),
            'thumbnail' => __( 'outlet.thumbnail' ),
            'url_slug' => __( 'outlet.url_slug' ),
            'structure' => __( 'outlet.structure' ),
            'size' => __( 'outlet.size' ),
            'phone_number' => __( 'outlet.phone_number' ),
            'sort' => __( 'outlet.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $outletCreate = Outlet::create([
                'title' => $request->title,
                'description' => $request->description,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'state' => $request->state,
                'postcode' => $request->postcode,
            ]);

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'outlet/' . $outletCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $outletCreate->image = $target;
                   $outletCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'outlet/' . $outletCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $outletCreate->thumbnail = $target;
                   $outletCreate->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.outlets' ) ) ] ),
        ] );
    }
    
    public static function updateOutlet( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'outlet.title' ),
            'description' => __( 'outlet.description' ),
            'image' => __( 'outlet.image' ),
            'thumbnail' => __( 'outlet.thumbnail' ),
            'url_slug' => __( 'outlet.url_slug' ),
            'structure' => __( 'outlet.structure' ),
            'size' => __( 'outlet.size' ),
            'phone_number' => __( 'outlet.phone_number' ),
            'sort' => __( 'outlet.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateOutlet = Outlet::find( $request->id );
    
            $updateOutlet->title = $request->title;
            $updateOutlet->description = $request->description;
            $updateOutlet->address_1 = $request->address_1;
            $updateOutlet->address_2 = $request->address_2;
            $updateOutlet->city = $request->city;
            $updateOutlet->state = $request->state;
            $updateOutlet->postcode = $request->postcode;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'outlet/' . $updateOutlet->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateOutlet->image = $target;
                   $updateOutlet->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateOutlet->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.outlets' ) ) ] ),
        ] );
    }

     public static function allOutlets( $request ) {

        $outlets = Outlet::select( 'outlets.*');

        $filterObject = self::filter( $request, $outlets );
        $outlet = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $outlet->orderBy( 'outlets.created_at', $dir );
                    break;
                case 2:
                    $outlet->orderBy( 'outlets.title', $dir );
                    break;
                case 3:
                    $outlet->orderBy( 'outlets.description', $dir );
                    break;
            }
        }

            $outletCount = $outlet->count();

            $limit = $request->length;
            $offset = $request->start;

            $outlets = $outlet->skip( $offset )->take( $limit )->get();

            if ( $outlets ) {
                $outlets->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }

            $totalRecord = Outlet::count();

            $data = [
                'outlets' => $outlets,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $outletCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'outlets.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'outlets.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_outlet)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_outlet . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneOutlet( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $outlet = Outlet::find( $request->id );

        $outlet->append( ['encrypted_id','image_path'] );
        
        return response()->json( $outlet );
    }

    public static function deleteOutlet( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'outlet.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Outlet::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.outlets' ) ) ] ),
        ] );
    }

    public static function updateOutletStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateOutlet = Outlet::find( $request->id );
            $updateOutlet->status = $updateOutlet->status == 10 ? 20 : 10;

            $updateOutlet->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'outlet' => $updateOutlet,
                    'message_key' => 'update_outlet_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_outlet_failed',
            ], 500 );
        }
    }

    public static function removeOutletGalleryImage( $request ) {

        $updateFarm = Outlet::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}
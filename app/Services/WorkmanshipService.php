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
    Workmanship,
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class WorkmanshipService
{

    public static function createWorkmanship( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'fullname' => [ 'required' ],
            'calculation_rate' => [ 'nullable' , 'numeric', 'min:0' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'workmanship.title' ),
            'description' => __( 'workmanship.description' ),
            'image' => __( 'workmanship.image' ),
            'thumbnail' => __( 'workmanship.thumbnail' ),
            'url_slug' => __( 'workmanship.url_slug' ),
            'structure' => __( 'workmanship.structure' ),
            'size' => __( 'workmanship.size' ),
            'phone_number' => __( 'workmanship.phone_number' ),
            'sort' => __( 'workmanship.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $workmanshipCreate = Workmanship::create([
                'fullname' => $request->fullname,
                'calculation_rate' => $request->calculation_rate,
            ]);

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'workmanship/' . $workmanshipCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $workmanshipCreate->image = $target;
                   $workmanshipCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'workmanship/' . $workmanshipCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $workmanshipCreate->thumbnail = $target;
                   $workmanshipCreate->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.workmanships' ) ) ] ),
        ] );
    }
    
    public static function updateWorkmanship( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'fullname' => [ 'required' ],
            'calculation_rate' => [ 'nullable' , 'numeric', 'min:0' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'workmanship.title' ),
            'description' => __( 'workmanship.description' ),
            'image' => __( 'workmanship.image' ),
            'thumbnail' => __( 'workmanship.thumbnail' ),
            'url_slug' => __( 'workmanship.url_slug' ),
            'structure' => __( 'workmanship.structure' ),
            'size' => __( 'workmanship.size' ),
            'phone_number' => __( 'workmanship.phone_number' ),
            'sort' => __( 'workmanship.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateWorkmanship = Workmanship::find( $request->id );
    
            $updateWorkmanship->fullname = $request->fullname;
            $updateWorkmanship->calculation_rate = $request->calculation_rate;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'workmanship/' . $updateWorkmanship->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateWorkmanship->image = $target;
                   $updateWorkmanship->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateWorkmanship->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.workmanships' ) ) ] ),
        ] );
    }

     public static function allWorkmanships( $request ) {

        $workmanships = Workmanship::select( 'workmanships.*');

        $filterObject = self::filter( $request, $workmanships );
        $workmanship = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $workmanship->orderBy( 'workmanships.created_at', $dir );
                    break;
                case 2:
                    $workmanship->orderBy( 'workmanships.title', $dir );
                    break;
                case 3:
                    $workmanship->orderBy( 'workmanships.description', $dir );
                    break;
            }
        }

            $workmanshipCount = $workmanship->count();

            $limit = $request->length;
            $offset = $request->start;

            $workmanships = $workmanship->skip( $offset )->take( $limit )->get();

            if ( $workmanships ) {
                $workmanships->append( [
                    'encrypted_id',
                    'image_path',
                    'thumbnail_path',
                ] );
            }

            $totalRecord = Workmanship::count();

            $data = [
                'workmanships' => $workmanships,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $workmanshipCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'workmanships.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'workmanships.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_workmanship)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_workmanship . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'workmanship.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneWorkmanship( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $workmanship = Workmanship::find( $request->id );

        $workmanship->append( ['encrypted_id','image_path'] );
        
        return response()->json( $workmanship );
    }

    public static function deleteWorkmanship( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'workmanship.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Workmanship::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.workmanships' ) ) ] ),
        ] );
    }

    public static function updateWorkmanshipStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateWorkmanship = Workmanship::find( $request->id );
            $updateWorkmanship->status = $updateWorkmanship->status == 10 ? 20 : 10;

            $updateWorkmanship->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'workmanship' => $updateWorkmanship,
                    'message_key' => 'update_workmanship_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_workmanship_failed',
            ], 500 );
        }
    }

    public static function removeWorkmanshipGalleryImage( $request ) {

        $updateFarm = Workmanship::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}
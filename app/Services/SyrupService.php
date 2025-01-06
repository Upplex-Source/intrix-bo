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
    Syrup,
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SyrupService
{

    public static function createSyrup( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'code' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'ingredients' => [ 'nullable' ],
            'nutritional_values' => [ 'nullable' ],
            'price' => [ 'required', 'min:0' ],
        ] );

        $attributeName = [
            'title' => __( 'syrup.title' ),
            'description' => __( 'syrup.description' ),
            'image' => __( 'syrup.image' ),
            'code' => __( 'syrup.code' ),
            'ingredients' => __( 'syrup.ingredients' ),
            'nutritional_values' => __( 'syrup.nutritional_values' ),
            'price' => __( 'syrup.price' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $syrupCreate = Syrup::create([
                'title' => $request->title,
                'description' => $request->description,
                'code' => $request->code,
                'price' => $request->price,
                'ingredients' => $request->ingredients,
                'nutritional_values' => $request->nutritional_values,
            ]);

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'syrup/' . $syrupCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $syrupCreate->image = $target;
                   $syrupCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.syrups' ) ) ] ),
        ] );
    }
    
    public static function updateSyrup( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'code' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'ingredients' => [ 'nullable' ],
            'nutritional_values' => [ 'nullable' ],
            'price' => [ 'required', 'min:0' ],
        ] );

        $attributeName = [
            'title' => __( 'syrup.title' ),
            'description' => __( 'syrup.description' ),
            'image' => __( 'syrup.image' ),
            'code' => __( 'syrup.code' ),
            'ingredients' => __( 'syrup.ingredients' ),
            'nutritional_values' => __( 'syrup.nutritional_values' ),
            'price' => __( 'syrup.price' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateSyrup = Syrup::find( $request->id );
    
            $updateSyrup->title = $request->title;
            $updateSyrup->description = $request->description;
            $updateSyrup->ingredients = $request->ingredients;
            $updateSyrup->nutritional_values = $request->nutritional_values;
            $updateSyrup->code = $request->code;
            $updateSyrup->price = $request->price;

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'syrup/' . $updateSyrup->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateSyrup->image = $target;
                   $updateSyrup->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateSyrup->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.syrups' ) ) ] ),
        ] );
    }

     public static function allSyrups( $request ) {

        $syrups = Syrup::select( 'syrups.*');

        $filterObject = self::filter( $request, $syrups );
        $syrup = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $syrup->orderBy( 'syrups.created_at', $dir );
                    break;
                case 2:
                    $syrup->orderBy( 'syrups.title', $dir );
                    break;
                case 3:
                    $syrup->orderBy( 'syrups.description', $dir );
                    break;
            }
        }

            $syrupCount = $syrup->count();

            $limit = $request->length;
            $offset = $request->start;

            $syrups = $syrup->skip( $offset )->take( $limit )->get();

            if ( $syrups ) {
                $syrups->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }

            $totalRecord = Syrup::count();

            $data = [
                'syrups' => $syrups,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $syrupCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'syrups.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'syrups.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_syrup)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_syrup . '%');
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

        if ( !empty( $request->code ) ) {
            $model->where( 'code', 'LIKE', '%' . $request->code . '%' );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneSyrup( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $syrup = Syrup::find( $request->id );

        $syrup->append( ['encrypted_id','image_path'] );
        
        return response()->json( $syrup );
    }

    public static function deleteSyrup( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'syrup.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Syrup::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.syrups' ) ) ] ),
        ] );
    }

    public static function updateSyrupStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateSyrup = Syrup::find( $request->id );
            $updateSyrup->status = $updateSyrup->status == 10 ? 20 : 10;

            $updateSyrup->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'syrup' => $updateSyrup,
                    'message_key' => 'update_syrup_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_syrup_failed',
            ], 500 );
        }
    }

    public static function removeSyrupGalleryImage( $request ) {

        $updateFarm = Syrup::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}
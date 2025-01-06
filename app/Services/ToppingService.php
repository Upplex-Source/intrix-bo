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
    Topping,
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ToppingService
{

    public static function createTopping( $request ) {
        
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
            'title' => __( 'topping.title' ),
            'description' => __( 'topping.description' ),
            'image' => __( 'topping.image' ),
            'code' => __( 'topping.code' ),
            'ingredients' => __( 'topping.ingredients' ),
            'nutritional_values' => __( 'topping.nutritional_values' ),
            'price' => __( 'topping.price' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $toppingCreate = Topping::create([
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

                    $target = 'topping/' . $toppingCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $toppingCreate->image = $target;
                   $toppingCreate->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.toppings' ) ) ] ),
        ] );
    }
    
    public static function updateTopping( $request ) {

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
            'price' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'topping.title' ),
            'description' => __( 'topping.description' ),
            'image' => __( 'topping.image' ),
            'code' => __( 'topping.code' ),
            'ingredients' => __( 'topping.ingredients' ),
            'nutritional_values' => __( 'topping.nutritional_values' ),
            'price' => __( 'topping.price' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateTopping = Topping::find( $request->id );
    
            $updateTopping->title = $request->title;
            $updateTopping->description = $request->description;
            $updateTopping->ingredients = $request->ingredients;
            $updateTopping->nutritional_values = $request->nutritional_values;
            $updateTopping->code = $request->code;
            $updateTopping->price = $request->price;

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'topping/' . $updateTopping->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateTopping->image = $target;
                   $updateTopping->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateTopping->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.toppings' ) ) ] ),
        ] );
    }

     public static function allToppings( $request ) {

        $toppings = Topping::select( 'toppings.*');

        $filterObject = self::filter( $request, $toppings );
        $topping = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $topping->orderBy( 'toppings.created_at', $dir );
                    break;
                case 2:
                    $topping->orderBy( 'toppings.title', $dir );
                    break;
                case 3:
                    $topping->orderBy( 'toppings.description', $dir );
                    break;
            }
        }

            $toppingCount = $topping->count();

            $limit = $request->length;
            $offset = $request->start;

            $toppings = $topping->skip( $offset )->take( $limit )->get();

            if ( $toppings ) {
                $toppings->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }

            $totalRecord = Topping::count();

            $data = [
                'toppings' => $toppings,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $toppingCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'toppings.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'toppings.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_topping)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_topping . '%');
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

    public static function oneTopping( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $topping = Topping::find( $request->id );

        $topping->append( ['encrypted_id','image_path'] );
        
        return response()->json( $topping );
    }

    public static function deleteTopping( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'topping.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Topping::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.toppings' ) ) ] ),
        ] );
    }

    public static function updateToppingStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateTopping = Topping::find( $request->id );
            $updateTopping->status = $updateTopping->status == 10 ? 20 : 10;

            $updateTopping->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'topping' => $updateTopping,
                    'message_key' => 'update_topping_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_topping_failed',
            ], 500 );
        }
    }

    public static function removeToppingGalleryImage( $request ) {

        $updateFarm = Topping::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}
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
    Bundle,
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BundleService
{

    public static function createBundle( $request ) {

        $validator = Validator::make( $request->all(), [
            'products' => [ 'nullable',  function ($attribute, $value, $fail) {
                $ids = explode(',', $value);
                $invalidIds = array_filter($ids, function ($id) {
                    return !\DB::table('products')->where('id', $id)->exists();
                });
                
                if (!empty($invalidIds)) {
                    $fail("The $attribute contains invalid IDs: " . implode(', ', $invalidIds));
                }
            }, ],
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            
            'promotion_start' => [ 'nullable' ],
            'promotion_end' => [ 'nullable' ],
            'promotion_enabled' => [ 'nullable' ],
            'promotion_price' => [ 'nullable', 'numeric', 'min:0' ],
            'price' => [ 'nullable', 'numeric', 'min:0' ],
        ] );

        $attributeName = [
            'parent_id' => __( 'bundle.parent_id' ),
            'title' => __( 'bundle.title' ),
            'description' => __( 'bundle.description' ),
            'image' => __( 'bundle.image' ),
            'thumbnail' => __( 'bundle.thumbnail' ),
            'url_slug' => __( 'bundle.url_slug' ),
            'structure' => __( 'bundle.structure' ),
            'size' => __( 'bundle.size' ),
            'phone_number' => __( 'bundle.phone_number' ),
            'sort' => __( 'bundle.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $bundleCreate = Bundle::create([
                'title' => $request->title,
                'description' => $request->description,
                'promotion_start' => $request->promotion_start,
                'promotion_end' => $request->promotion_end,
                'promotion_enabled' => $request->promotion_enabled,
                'promotion_price' => $request->promotion_price,
                'price' => $request->price,
            ]);

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'bundle/' . $bundleCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $bundleCreate->image = $target;
                   $bundleCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $request->products ) {
                $products = explode( ',', $request->products );

                if( $products != null ) {
                    if( is_array( $products ) ) {
                        foreach ( $products as $product ) {
                            if (!$bundleCreate->products()->where('product_id', $product)->exists()) {
                                $bundleCreate->products()->attach($product);
                            }
                        }
                    }else{
                        $bundleCreate->products()->attach($request->product);
                    }
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.bundles' ) ) ] ),
        ] );
    }
    
    public static function updateBundle( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
           'products' => [ 'nullable',  function ($attribute, $value, $fail) {
                $ids = explode(',', $value);
                $invalidIds = array_filter($ids, function ($id) {
                    return !\DB::table('products')->where('id', $id)->exists();
                });
                
                if (!empty($invalidIds)) {
                    $fail("The $attribute contains invalid IDs: " . implode(', ', $invalidIds));
                }
            }, ],
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            
            'promotion_start' => [ 'nullable' ],
            'promotion_end' => [ 'nullable' ],
            'promotion_enabled' => [ 'nullable' ],
            'promotion_price' => [ 'nullable', 'numeric', 'min:0' ],
            'price' => [ 'nullable', 'numeric', 'min:0' ],
        ] );

        $attributeName = [
            'title' => __( 'bundle.title' ),
            'description' => __( 'bundle.description' ),
            'image' => __( 'bundle.image' ),
            'thumbnail' => __( 'bundle.thumbnail' ),
            'url_slug' => __( 'bundle.url_slug' ),
            'structure' => __( 'bundle.structure' ),
            'size' => __( 'bundle.size' ),
            'phone_number' => __( 'bundle.phone_number' ),
            'sort' => __( 'bundle.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateBundle = Bundle::find( $request->id );
    
            $updateBundle->title = $request->title;
            $updateBundle->description = $request->description;
            $updateBundle->promotion_start = $request->promotion_start;
            $updateBundle->promotion_end = $request->promotion_end;
            $updateBundle->promotion_enabled = $request->promotion_enabled;
            $updateBundle->promotion_price = $request->promotion_price;
            $updateBundle->price = $request->price;

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'bundle/' . $updateBundle->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateBundle->image = $target;
                   $updateBundle->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }
            if( $request->products ) {

                $products = explode( ',', $request->products );

                if( $products != null ) {
                    $updateBundle->products()->detach();
                    if( is_array( $products ) ) {
                        foreach ( $products as $product ) {
                            if (!$updateBundle->products()->where('product_id', $product)->exists()) {
                                $updateBundle->products()->attach($product);
                            }
                        }
                    }else{
                        $updateBundle->products()->attach($request->product);
                    }
                }
            }

            $updateBundle->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.bundles' ) ) ] ),
        ] );
    }

     public static function allBundles( $request ) {

        $bundles = Bundle::with(['products']);

        $filterObject = self::filter( $request, $bundles );
        $bundle = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $bundle->orderBy( 'bundles.created_at', $dir );
                    break;
                case 2:
                    $bundle->orderBy( 'bundles.created_at', $dir );
                    break;
                case 3:
                    $bundle->orderBy( 'bundles.title', $dir );
                    break;
                case 4:
                    $bundle->orderBy( 'bundles.description', $dir );
                    break;
            }
        }

            $bundleCount = $bundle->count();

            $limit = $request->length;
            $offset = $request->start;

            $bundles = $bundle->skip( $offset )->take( $limit )->get();

            if ( $bundles ) {
                $bundles->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }

            $totalRecord = Bundle::count();

            $data = [
                'bundles' => $bundles,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $bundleCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'bundles.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'bundles.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        if ( !empty( $request->id ) ) {
            $model->where( 'bundles.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_bundle)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_bundle . '%');
            });
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneBundle( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $bundle = Bundle::with( [
            'products',
        ] )->find( $request->id );

        $bundle->append( ['encrypted_id','image_path'] );
        
        return response()->json( $bundle );
    }

    public static function deleteBundle( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'bundle.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Bundle::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.bundles' ) ) ] ),
        ] );
    }

    public static function updateBundleStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateBundle = Bundle::find( $request->id );
            $updateBundle->status = $updateBundle->status == 10 ? 20 : 10;

            $updateBundle->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'bundle' => $updateBundle,
                    'message_key' => 'update_bundle_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_bundle_failed',
            ], 500 );
        }
    }

    public static function removeBundleGalleryImage( $request ) {

        $updateFarm = Bundle::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}
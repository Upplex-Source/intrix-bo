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
    Product,
    Booking,
    FileManager,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ProductService
{

    public static function createProduct( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'parent_id' => [ 'nullable', 'exists:products,id' ],
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'parent_id' => __( 'product.parent_id' ),
            'title' => __( 'product.title' ),
            'description' => __( 'product.description' ),
            'image' => __( 'product.image' ),
            'thumbnail' => __( 'product.thumbnail' ),
            'url_slug' => __( 'product.url_slug' ),
            'structure' => __( 'product.structure' ),
            'size' => __( 'product.size' ),
            'phone_number' => __( 'product.phone_number' ),
            'sort' => __( 'product.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $productCreate = Product::create([
                'parent_id' => $request->parent_id,
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

                    $target = 'product/' . $productCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $productCreate->image = $target;
                   $productCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'product/' . $productCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $productCreate->thumbnail = $target;
                   $productCreate->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.products' ) ) ] ),
        ] );
    }
    
    public static function updateProduct( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'parent_id' => [ 'nullable', 'exists:products,id' ],
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'parent_id' => __( 'product.parent_id' ),
            'title' => __( 'product.title' ),
            'description' => __( 'product.description' ),
            'image' => __( 'product.image' ),
            'thumbnail' => __( 'product.thumbnail' ),
            'url_slug' => __( 'product.url_slug' ),
            'structure' => __( 'product.structure' ),
            'size' => __( 'product.size' ),
            'phone_number' => __( 'product.phone_number' ),
            'sort' => __( 'product.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateProduct = Product::find( $request->id );
    
            $updateProduct->parent_id = $request->parent_id;
            $updateProduct->title = $request->title;
            $updateProduct->description = $request->description;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'product/' . $updateProduct->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateProduct->image = $target;
                   $updateProduct->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateProduct->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.products' ) ) ] ),
        ] );
    }

     public static function allProducts( $request ) {

        $products = Product::with(['children', 'parent']);

        $filterObject = self::filter( $request, $products );
        $product = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $product->orderBy( 'products.created_at', $dir );
                    break;
                case 2:
                    $product->orderBy( 'products.parent_id', $dir );
                    break;
                case 3:
                    $product->orderBy( 'products.title', $dir );
                    break;
                case 4:
                    $product->orderBy( 'products.description', $dir );
                    break;
            }
        }

            $productCount = $product->count();

            $limit = $request->length;
            $offset = $request->start;

            $products = $product->skip( $offset )->take( $limit )->get();

            if ( $products ) {
                $products->append( [
                    'encrypted_id',
                    'image_path',
                    'thumbnail_path',
                ] );
            }

            $totalRecord = Product::count();

            $data = [
                'products' => $products,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $productCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'products.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'products.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        
        if ( !empty( $request->id ) ) {
            $model->where( 'products.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_product)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_product . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'products.status', $request->status );
            $filter = true;
        }

        

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneProduct( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $product = Product::with( [
            'children', 'parent',
        ] )->find( $request->id );

        $product->append( ['encrypted_id','image_path'] );
        
        return response()->json( $product );
    }

    public static function deleteProduct( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'product.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Product::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.products' ) ) ] ),
        ] );
    }

    public static function updateProductStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateProduct = Product::find( $request->id );
            $updateProduct->status = $updateProduct->status == 10 ? 20 : 10;

            $updateProduct->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'product' => $updateProduct,
                    'message_key' => 'update_product_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_product_failed',
            ], 500 );
        }
    }

    public static function removeProductGalleryImage( $request ) {

        $updateFarm = Product::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}
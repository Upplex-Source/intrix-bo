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
    ProductBundle,
    ProductBundleGallery,
    Booking,
    FileManager,
    ProductBundleVariant,
    Froyo,
    Syrup,
    Topping,
    ProductBundleMeta,
    User,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Carbon\Carbon;

class ProductBundleService
{

    public static function createProductBundle( $request ) {

        $validator = Validator::make( $request->all(), [
            'code' => [ 'nullable' ],
            'title' => [ 'nullable' ],
            'description' => [ 'nullable' ],
            'price' => [ 'required' ],
            'discount_price' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'products' => [ 'required' ],
            'quantity' => [ 'required', 'min:1' ],
        ] );

        $attributeName = [
            'title' => __( 'product_bundle.title' ),
            'description' => __( 'product_bundle.description' ),
            'image' => __( 'product_bundle.image' ),
            'code' => __( 'product_bundle.code' ),
            'price' => __( 'product_bundle.price' ),
            'discount_price' => __( 'product_bundle.discount_price' ),
            'image' => __( 'product_bundle.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $productBundleCreate = ProductBundle::create([
                'code' => $request->code,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'discount_price' => $request->discount_price,
                'status' => 10,
            ]);

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'froyo/' . $productBundleCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $productBundleCreate->image = $target;
                   $productBundleCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            // bundle metas
            $products = explode( ',', $request->products );

            foreach ($products as $product) {

                ProductBundleMeta::create([
                    'product_id' => $product,
                    'product_bundle_id' => $productBundleCreate->id,
                    'quantity' => $request->quantity,
                    'status' => 10,
                ]);
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.product_bundles' ) ) ] ),
        ] );
    }
    
    public static function updateProductBundle( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
    
        $validator = Validator::make( $request->all(), [
            'code' => [ 'nullable' ],
            'title' => [ 'nullable' ],
            'description' => [ 'nullable' ],
            'price' => [ 'required' ],
            'discount_price' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'products' => [ 'required' ],
            'quantity' => [ 'required', 'min:1' ],
        ] );

        $attributeName = [
            'title' => __( 'product_bundle.title' ),
            'description' => __( 'product_bundle.description' ),
            'image' => __( 'product_bundle.image' ),
            'code' => __( 'product_bundle.code' ),
            'price' => __( 'product_bundle.price' ),
            'discount_price' => __( 'product_bundle.discount_price' ),
            'default_froyo_quantity' => __( 'product_bundle.default_froyo_quantity' ),
            'default_syrup_quantity' => __( 'product_bundle.default_syrup_quantity' ),
            'default_topping_quantity' => __( 'product_bundle.default_topping_quantity' ),
            'image' => __( 'product_bundle.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            
            $updateProductBundle = ProductBundle::with(['productBundleMetas'])->find( $request->id );
  
            $updateProductBundle->code = $request->code ?? $updateProductBundle->code;
            $updateProductBundle->title = $request->title ?? $updateProductBundle->title;
            $updateProductBundle->description = $request->description ?? $updateProductBundle->description;
            $updateProductBundle->price = $request->price ?? $updateProductBundle->price;
            $updateProductBundle->discount_price = $request->discount_price ?? $updateProductBundle->discount_price;

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'product/' . $updateProductBundle->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateProductBundle->image = $target;
                   $updateProductBundle->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateProductBundle->save();

            $updateProductBundle->productBundleMetas()->delete();
            // bundle metas
            $products = explode( ',', $request->products );

            foreach ($products as $product) {
                ProductBundleMeta::create([
                    'product_id' => $product,
                    'product_bundle_id' => $updateProductBundle->id,
                    'quantity' => $request->quantity,
                    'status' => 10,
                ]);
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.product_bundles' ) ) ] ),
        ] );
    }

    public static function allProductBundles( $request ) {

        $productBundles = ProductBundle::with(['productBundleMetas.product'])->select( 'product_bundles.*' );

        $filterObject = self::filter( $request, $productBundles );
        $productBundle = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $productBundle->orderBy( 'product_bundles.created_at', $dir );
                    break;
                case 1:
                    $productBundle->orderBy( 'product_bundles.id', $dir );
                    break;
                case 3:
                    $productBundle->orderBy( 'product_bundles.title', $dir );
                    break;
                case 4:
                    $productBundle->orderBy( 'product_bundles.description', $dir );
                    break;
            }
        }

            $productBundleCount = $productBundle->count();

            $limit = $request->length;
            $offset = $request->start;

            $productBundles = $productBundle->skip( $offset )->take( $limit )->get();

            if ( $productBundles ) {

                $productBundles->append( [
                    'encrypted_id',
                    'image_path',
                ] );

                foreach( $productBundles as $productBundle ){
                    if( $productBundle->productBundleMetas ){
                        $pbms = $productBundle->productBundleMetas;
                        foreach( $pbms as $pbm ){
                            $pbm->product->append( [
                                'image_path',
                            ] );
                        }
                    }
                }

            }

            $totalRecord = ProductBundle::count();

            $data = [
                'product_bundles' => $productBundles,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $productBundleCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->name ) ) {
            $model->where('title', 'LIKE', '%' . $request->name . '%')
            ->orWhereHas('variants', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->name . '%');
            })->orWhereHas('bundles', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->name . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->title ) ) {
            $model->where( 'product_bundles.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'product_bundles.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->code ) ) {
            $model->where( 'product_bundles.code', 'LIKE', '%' . $request->code . '%' );
            $filter = true;
        }
        
        if ( !empty( $request->id ) ) {
            $model->where( 'product_bundles.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->brand)) {
            $model->whereHas('brand', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->brand . '%');
            });
            $filter = true;
        }

        if (!empty($request->category)) {
            $model->whereHas('categories', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->category . '%');
            });
            $filter = true;
        }

        if (!empty($request->unit)) {
            $model->whereHas('unit', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->unit . '%');
            });
            $filter = true;
        }
        
        if (!empty($request->warehouse)) {
            $model->whereHas('warehouses', function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse);
            });
            $filter = true;
        }

        if (!empty($request->product)) {
            $model->whereHas('productBundleMetas', function ($query) use ($request) {
                $model->whereHas('product', function ($query) use ($request) {
                    $query->where('product.title', 'LIKE' . '%' . $request->product. '%');
                });
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'product_bundles.status', $request->status );
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

    public static function oneProductBundle( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $productBundle = ProductBundle::with( ['productBundleMetas.product'] )->select( 'product_bundles.*' )->find( $request->id );

        if ( $productBundle ) {
            $productBundle->append( [
                'encrypted_id',
                'image_path',
            ] );

            if( $productBundle->productBundleMetas ){
                if( $productBundle->productBundleMetas ){
                    $pbms = $productBundle->productBundleMetas;
                    foreach( $pbms as $pbm ){
                        $pbm->product->append( [
                            'image_path',
                        ] );
                    }
                }
            }
        }
        
        return response()->json( $productBundle );
    }

    public static function deleteProductBundle( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'product_bundle.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            ProductBundle::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.product_bundles' ) ) ] ),
        ] );
    }

    public static function updateProductBundleStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateProductBundle = ProductBundle::find( $request->id );
            $updateProductBundle->status = $updateProductBundle->status == 10 ? 20 : 10;

            $updateProductBundle->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'product' => $updateProductBundle,
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
}
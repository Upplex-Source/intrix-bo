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
    ProductFreeGift,
    Product,
    ProductFreeGiftGallery,
    Booking,
    FileManager,
    ProductFreeGiftVariant,
    Froyo,
    Syrup,
    Topping,
    ProductFreeGiftMeta,
    User,
    UserFreeGift,
    UserFreeGiftTransaction,
    Option,
    Order,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Carbon\Carbon;

class ProductFreeGiftService
{

    public static function createProductFreeGift( $request ) {

        $validator = Validator::make( $request->all(), [
            'sku' => [ 'nullable' ],
            'title' => [ 'nullable' ],
            'code' => [ 'nullable' ],
            'description' => [ 'nullable' ],
            'price' => [ 'required', 'numeric', 'min:0' ],
'discount_price' => [ 'nullable', 'numeric', 'min:0' ],
            'image' => [ 'nullable' ],
            'specification' => [ 'nullable' ],
            'features' => [ 'nullable' ],
            'whats_included' => [ 'nullable' ],
            'products' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'product_free_gift.title' ),
            'description' => __( 'product_free_gift.description' ),
            'image' => __( 'product_free_gift.image' ),
            'code' => __( 'product_free_gift.code' ),
            'price' => __( 'product_free_gift.price' ),
            'discount_price' => __( 'product_free_gift.discount_price' ),
            'image' => __( 'product_free_gift.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $productFreeGiftCreate = ProductFreeGift::create([
                'sku' => $request->sku,
                'title' => $request->title,
                'code' => $request->code,
                'description' => $request->description,
                'specification' => $request->specification,
                'features' => $request->features,
                'whats_included' => $request->whats_included,
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

                    $target = 'product-add-on/' . $productFreeGiftCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $productFreeGiftCreate->image = $target;
                   $productFreeGiftCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if( $request->products ){

                // product pivot
                $products = explode( ',', $request->products );

                foreach ($products as $product) {

                    $product = Product::find( $product );
                    $product->freeGifts()->attach($productFreeGiftCreate->id);
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.product_free_gifts' ) ) ] ),
        ] );
    }
    
    public static function updateProductFreeGift( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
    
        $validator = Validator::make( $request->all(), [
            'sku' => [ 'nullable' ],
            'title' => [ 'nullable' ],
            'code' => [ 'nullable' ],
            'description' => [ 'nullable' ],
            'price' => [ 'required', 'numeric', 'min:0' ],
'discount_price' => [ 'nullable', 'numeric', 'min:0' ],
            'image' => [ 'nullable' ],
            'specification' => [ 'nullable' ],
            'features' => [ 'nullable' ],
            'whats_included' => [ 'nullable' ],
            'products' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'product_free_gift.title' ),
            'description' => __( 'product_free_gift.description' ),
            'image' => __( 'product_free_gift.image' ),
            'code' => __( 'product_free_gift.code' ),
            'price' => __( 'product_free_gift.price' ),
            'discount_price' => __( 'product_free_gift.discount_price' ),
            'image' => __( 'product_free_gift.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            
            $updateProductFreeGift = ProductFreeGift::find( $request->id );
  
            $updateProductFreeGift->sku = $request->sku ? $request->sku : $updateProductFreeGift->sku;
            $updateProductFreeGift->title = $request->title ? $request->title : $updateProductFreeGift->title;
            $updateProductFreeGift->code = $request->code ? $request->code : $updateProductFreeGift->code;
            $updateProductFreeGift->description = $request->description ? $request->description : $updateProductFreeGift->description;
            $updateProductFreeGift->specification = $request->specification ? $request->specification : $updateProductFreeGift->specification;
            $updateProductFreeGift->features = $request->features ? $request->features : $updateProductFreeGift->features;
            $updateProductFreeGift->whats_included = $request->whats_included ? $request->whats_included : $updateProductFreeGift->whats_included;
            $updateProductFreeGift->price = $request->price ? $request->price : $updateProductFreeGift->price;
            $updateProductFreeGift->discount_price = $request->discount_price ? $request->discount_price : $updateProductFreeGift->discount_price;

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'product-add-on/' . $updateProductFreeGift->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateProductFreeGift->image = $target;
                   $updateProductFreeGift->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateProductFreeGift->save();

            // detach 
            $updateProductFreeGift->freeGiftProducts()->detach();

            if( $request->products ){

                // product pivot
                $products = explode( ',', $request->products );

                foreach ($products as $product) {

                    $product = Product::find( $product );
                    $product->freeGifts()->attach($updateProductFreeGift->id);
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
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.product_free_gifts' ) ) ] ),
        ] );
    }

    public static function allProductFreeGifts( $request ) {

        $productFreeGifts = ProductFreeGift::with(['freeGiftProducts'])->select( 'product_free_gifts.*' );

        $filterObject = self::filter( $request, $productFreeGifts );
        $productFreeGift = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $productFreeGift->orderBy( 'product_free_gifts.created_at', $dir );
                    break;
                case 1:
                    $productFreeGift->orderBy( 'product_free_gifts.id', $dir );
                    break;
                case 3:
                    $productFreeGift->orderBy( 'product_free_gifts.title', $dir );
                    break;
                case 4:
                    $productFreeGift->orderBy( 'product_free_gifts.description', $dir );
                    break;
            }
        }

            $productFreeGiftCount = $productFreeGift->count();

            $limit = $request->length;
            $offset = $request->start;

            $productFreeGifts = $productFreeGift->skip( $offset )->take( $limit )->get();

            if ( $productFreeGifts ) {

                $productFreeGifts->append( [
                    'encrypted_id',
                    'image_path',
                ] );

                foreach( $productFreeGifts as $productFreeGift ){
                    if( $productFreeGift->productFreeGiftMetas ){
                        $pbms = $productFreeGift->productFreeGiftMetas;
                        foreach( $pbms as $pbm ){
                            $pbm->product->append( [
                                'image_path',
                            ] );
                        }
                    }
                }

            }

            $totalRecord = ProductFreeGift::count();

            $data = [
                'product_free_gifts' => $productFreeGifts,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $productFreeGiftCount : $totalRecord,
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
            $model->where( 'product_free_gifts.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'product_free_gifts.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->code ) ) {
            $model->where( 'product_free_gifts.code', 'LIKE', '%' . $request->code . '%' );
            $filter = true;
        }
        
        if ( !empty( $request->id ) ) {
            $model->where( 'product_free_gifts.id', '!=', Helper::decode($request->id) );
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
            $model->whereHas('productFreeGiftMetas', function ($query) use ($request) {
                $model->whereHas('product', function ($query) use ($request) {
                    $query->where('product.title', 'LIKE' . '%' . $request->product. '%');
                });
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'product_free_gifts.status', $request->status );
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

    public static function oneProductFreeGift( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $productFreeGift = ProductFreeGift::with( ['freeGiftProducts'] )->select( 'product_free_gifts.*' )->find( $request->id );

        if ( $productFreeGift ) {
            $productFreeGift->append( [
                'encrypted_id',
                'image_path',
            ] );
        }
        
        return response()->json( $productFreeGift );
    }

    public static function updateProductFreeGiftStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateProductFreeGift = ProductFreeGift::find( $request->id );
            $updateProductFreeGift->status = $updateProductFreeGift->status == 10 ? 20 : 10;

            $updateProductFreeGift->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'product' => $updateProductFreeGift,
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
    
    // client
    public static function getFreeGifts( $request ) {

        $now = Carbon::now('Asia/Kuala_Lumpur');

        $freeGift = ProductFreeGift::with( [
            'freeGiftProducts',
        ] )->select( 'product_free_gifts.*' )
        ->where( 'status', 10 );

        $filterObject = self::filter( $request, $freeGift );
        $freeGift = $filterObject['model'];
        $filter = $filterObject['filter'];

        $freeGiftCount = $freeGift->count();

        $limit = $request->length ? $request->length : 10;
        $offset = $request->start ? $request->start : 0;

        $freeGifts = $freeGift->skip($offset)->take($limit + 1)->get()->map(function ($freeGift) {
            if( $freeGift->freeGiftProducts ){
                $products = $freeGift->freeGiftProducts;
                foreach( $products as $product ) {
                    $product->append( ['image_path'] );
                }
            }
            $freeGift->append( ['image_path'] );

            return $freeGift;
        });

        $hasMore = $freeGifts->count() > $limit;
        if ($hasMore) {
            $freeGifts = $freeGifts->slice(0, $limit);
        }

        $data = [
            'free_gifts' => $freeGifts,
            'draw' => $request->draw,
            'start' => $offset,
            'length' => $limit,
            'hasMore' => $hasMore,
            'nextStart' => $hasMore ? $offset + $limit : null,
            'recordsFiltered' => ( $filter || ( $request->length || $request->start ) ) ? $freeGifts->count() : $freeGiftCount,
            'recordsTotal' => $filter ? $freeGiftCount : ProductFreeGift::where( 'status', 10 )->count(),
        ];

        return $data;
    }

}
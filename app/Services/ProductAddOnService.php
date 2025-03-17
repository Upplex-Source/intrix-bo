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
    ProductAddOn,
    Product,
    ProductAddOnGallery,
    Booking,
    FileManager,
    ProductAddOnVariant,
    Froyo,
    Syrup,
    Topping,
    ProductAddOnMeta,
    User,
    UserAddOn,
    UserAddOnTransaction,
    Option,
    Order,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Carbon\Carbon;

class ProductAddOnService
{

    public static function createProductAddOn( $request ) {

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
            'title' => __( 'product_add_on.title' ),
            'description' => __( 'product_add_on.description' ),
            'image' => __( 'product_add_on.image' ),
            'code' => __( 'product_add_on.code' ),
            'price' => __( 'product_add_on.price' ),
            'discount_price' => __( 'product_add_on.discount_price' ),
            'image' => __( 'product_add_on.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $productAddOnCreate = ProductAddOn::create([
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

                    $target = 'product-add-on/' . $productAddOnCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $productAddOnCreate->image = $target;
                   $productAddOnCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if( $request->products ){

                // product pivot
                $products = explode( ',', $request->products );

                foreach ($products as $product) {

                    $product = Product::find( $product );
                    $product->addOns()->attach($productAddOnCreate->id);
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.product_add_ons' ) ) ] ),
        ] );
    }
    
    public static function updateProductAddOn( $request ) {

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
            'title' => __( 'product_add_on.title' ),
            'description' => __( 'product_add_on.description' ),
            'image' => __( 'product_add_on.image' ),
            'code' => __( 'product_add_on.code' ),
            'price' => __( 'product_add_on.price' ),
            'discount_price' => __( 'product_add_on.discount_price' ),
            'image' => __( 'product_add_on.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            
            $updateProductAddOn = ProductAddOn::find( $request->id );
  
            $updateProductAddOn->sku = $request->sku ? $request->sku : $updateProductAddOn->sku;
            $updateProductAddOn->title = $request->title ? $request->title : $updateProductAddOn->title;
            $updateProductAddOn->code = $request->code ? $request->code : $updateProductAddOn->code;
            $updateProductAddOn->description = $request->description ? $request->description : $updateProductAddOn->description;
            $updateProductAddOn->specification = $request->specification ? $request->specification : $updateProductAddOn->specification;
            $updateProductAddOn->features = $request->features ? $request->features : $updateProductAddOn->features;
            $updateProductAddOn->whats_included = $request->whats_included ? $request->sku : $updateProductAddOn->whats_included;
            $updateProductAddOn->price = $request->price ? $request->price : $updateProductAddOn->price;
            $updateProductAddOn->discount_price = $request->discount_price ? $request->sku : $updateProductAddOn->discount_price;

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'product-add-on/' . $updateProductAddOn->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateProductAddOn->image = $target;
                   $updateProductAddOn->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateProductAddOn->save();

            // detach 
            $updateProductAddOn->addOnProducts()->detach();

            if( $request->products ){

                // product pivot
                $products = explode( ',', $request->products );

                foreach ($products as $product) {

                    $product = Product::find( $product );
                    $product->addOns()->attach($updateProductAddOn->id);
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
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.product_add_ons' ) ) ] ),
        ] );
    }

    public static function allProductAddOns( $request ) {

        $productAddOns = ProductAddOn::with(['addOnProducts'])->select( 'product_add_ons.*' );

        $filterObject = self::filter( $request, $productAddOns );
        $productAddOn = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $productAddOn->orderBy( 'product_add_ons.created_at', $dir );
                    break;
                case 1:
                    $productAddOn->orderBy( 'product_add_ons.id', $dir );
                    break;
                case 3:
                    $productAddOn->orderBy( 'product_add_ons.title', $dir );
                    break;
                case 4:
                    $productAddOn->orderBy( 'product_add_ons.description', $dir );
                    break;
            }
        }

            $productAddOnCount = $productAddOn->count();

            $limit = $request->length;
            $offset = $request->start;

            $productAddOns = $productAddOn->skip( $offset )->take( $limit )->get();

            if ( $productAddOns ) {

                $productAddOns->append( [
                    'encrypted_id',
                    'image_path',
                ] );

                foreach( $productAddOns as $productAddOn ){
                    
                    if( $productAddOn->productAddOnMetas ){
                        $pbms = $productAddOn->productAddOnMetas;
                        foreach( $pbms as $pbm ){
                            $pbm->product->append( [
                                'image_path',
                            ] );
                        }
                    }
                }

            }

            $totalRecord = ProductAddOn::count();

            $data = [
                'product_add_ons' => $productAddOns,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $productAddOnCount : $totalRecord,
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
            $model->where( 'product_add_ons.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'product_add_ons.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->code ) ) {
            $model->where( 'product_add_ons.code', 'LIKE', '%' . $request->code . '%' );
            $filter = true;
        }
        
        if ( !empty( $request->id ) ) {
            $model->where( 'product_add_ons.id', '!=', Helper::decode($request->id) );
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
            $model->whereHas('productAddOnMetas', function ($query) use ($request) {
                $model->whereHas('product', function ($query) use ($request) {
                    $query->where('product.title', 'LIKE' . '%' . $request->product. '%');
                });
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'product_add_ons.status', $request->status );
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

    public static function oneProductAddOn( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $productAddOn = ProductAddOn::with( ['addOnProducts'] )->select( 'product_add_ons.*' )->find( $request->id );

        if ( $productAddOn ) {
            $productAddOn->append( [
                'encrypted_id',
                'image_path',
            ] );
        }
        
        return response()->json( $productAddOn );
    }

    public static function updateProductAddOnStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateProductAddOn = ProductAddOn::find( $request->id );
            $updateProductAddOn->status = $updateProductAddOn->status == 10 ? 20 : 10;

            $updateProductAddOn->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'product' => $updateProductAddOn,
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

    public static function getAddOns( $request )
    {
        if( !$request->user_add_on ){

            $productbundles = ProductAddOn::where('status', 10)
            ->orderBy( 'created_at', 'DESC' );
    
            if ( $request && $request->title) {
                $productbundles->where( 'title', 'LIKE', '%' . $request->title . '%' );
            }

            if ( $request && $request->bundle_id) {
                $productbundles->where( 'id', 'LIKE', '%' . $request->bundle_id . '%' );
            }

            $productbundles = $productbundles->get();
            $claimedAddOnIds = UserAddOn::where('user_id', auth()->user()->id)
            ->pluck('product_add_on_id')
            ->toArray();

            $productbundles = $productbundles->map(function ($productbundle) use ($claimedAddOnIds) {
                $productbundle->claimed = in_array($productbundle->id, $claimedAddOnIds) ? 'purchased' : 'not purchased';
                $productbundle->append( ['image_path','bundle_rules'] );
                return $productbundle;
            });

        }else {
            $productbundles = UserAddOn::with([
                'productAddOn',
                'activeCarts.cartMetas' // Load cartMetas for activeCarts
            ])
            ->where('user_id', auth()->user()->id)
            ->where(function ($query) {
                $query->where('cups_left', '>', 0)
                      ->orWhereHas('activeCarts');
            })
            ->orderBy('created_at', 'DESC');
        

            if ( $request && $request->title) {
                $productbundles->where( 'title', 'LIKE', '%' . $request->title . '%' );
            }

            if ( $request && $request->bundle_id) {
                $productbundles->where( 'id', 'LIKE', '%' . $request->bundle_id . '%' );
            }

            $productbundles = $productbundles->get();

            $productbundles = $productbundles->map(function ($productbundle){
                $productbundle->append( ['bundle_status_label'] );
                $productbundle->productAddOn->append( ['image_path','bundle_rules'] );
                $productbundle->bundle_rules = $productbundle->productAddOn->bundle_rules;
                $productbundle->cups_in_cart = $productbundle->activeCarts->sum(function ($cart) {
                    return $cart->cartMetas->count();
                });

                if( $productbundle->activeCarts ){
                    foreach( $productbundle->activeCarts as $cart ){

                        if($cart->vendingMachine){
                            $cart->vendingMachine->makeHidden(['created_at', 'updated_at', 'status'])
                            ->setAttribute('operational_hour', $cart->vendingMachine->operational_hour)
                            ->setAttribute('image_path', $cart->vendingMachine->image_path);
                        }

                        $cartMetas = $cart->cartMetas->map(function ($meta) {
                            return [
                                'id' => $meta->id,
                                'subtotal' => $meta->total_price,
                                'product' => $meta->product?->makeHidden(['created_at', 'updated_at', 'status'])
                                    ->setAttribute('image_path', $meta->product->image_path),
                                'froyo' => $meta->froyos_metas,
                                'syrup' => $meta->syrups_metas,
                                'topping' => $meta->toppings_metas,
                            ];
                        });
                
                        // Attach the cart metas to the cart object
                        $cart->cartMetas = $cartMetas;
                    }

                    foreach( $productbundle->activeCarts as $userCart ) {
                        $userCart->cart_metas = $userCart->cartMetas;
                        // $userCart->cartMetas = null;
                        unset($userCart->cartMetas);
                        $userCart->cartMetas = $userCart->cart_metas;

                    }
                }

                return $productbundle;
            });
        }
        return response()->json( [
            'message' => '',
            'message_key' => $request->user_add_on ? 'get_user_add_on_success' : 'get_product_add_on_success',
            'data' => $productbundles,
        ] );

    }

}
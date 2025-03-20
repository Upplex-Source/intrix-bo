<?php

namespace App\Services;

use Illuminate\Support\Facades\{
    DB,
    Validator,
};
use Illuminate\Validation\Rule;

use App\Models\{
    Cart,
    CartMeta,
    Froyo,
    Syrup,
    Topping,
    Product,
    ProductVariant,
    Voucher,
    VoucherUsage,
    UserVoucher,
    ProductBundle,
    ProductBundleMeta,
    UserBundle,
    UserBundleHistory,
    UserBundleHistoryMeta,
    Option,
    UserNotification,
    ProductAddOn,
    ProductFreeGift,
    CartAddOn,
};

use App\Services\{
    ProductService,
};

use Helper;

use Carbon\Carbon;

class CartService {

    public static function getCart($request)
    {
        // Validate the incoming request parameters (id and session_key)
        $validator = Validator::make($request->all(), [
            'id' => ['nullable', 'exists:carts,id'],
            'session_key' => ['nullable', 'exists:carts,session_key'],
            'per_page' => ['nullable', 'integer', 'min:1'], // Validate per_page input
        ]);
        
        // If validation fails, it will automatically throw an error
        $validator->validate();
    
        // Get the current authenticated user
        $user = auth()->user();
    
        // Start by querying carts for the authenticated user
        $query = Cart::with(['cartMetas', 'addons', 'freeGift' => function ($query) {
                $query->orderBy('created_at', 'DESC');
            }, 'voucher'])
            ->where('status', 10)
            ->orderBy('created_at', 'DESC');
    
        // Apply filters if 'id' or 'session_key' is provided
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        if ($request->has('session_key')) {
            $query->where('session_key', $request->session_key);
        }
    
        // Use paginate instead of get
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $userCarts = $query->paginate($perPage);

        // Modify each cart and its related data
        $userCarts->getCollection()->transform(function ($cart) {
            // Make vending machine attributes hidden and add additional attributes
            if($cart->voucher){
                $cart->voucher->makeHidden( [ 'created_at', 'updated_at', 'type', 'status', 'min_spend', 'min_order', 'buy_x_get_y_adjustment', 'discount_amount' ] )
                ->append(['decoded_adjustment', 'image_path','voucher_type','voucher_type_label']);
            }

            if($cart->vendingMachine){
                $cart->vendingMachine->makeHidden(['created_at', 'updated_at', 'status'])
                ->setAttribute('operational_hour', $cart->vendingMachine->operational_hour)
                ->setAttribute('image_path', $cart->vendingMachine->image_path);
            }

            // if($cart->addOns){
            //     $addOns = $cart->addOns;
            //     foreach( $addOns as $addOn ) {
            //         $addOn->addOn->makeHidden( [ 'created_at', 'updated_at' ] )
            //         ->append([ 'image_path' ]);
            //     }
            // } 

            if($cart->freeGift){
                $cart->freeGift->setAttribute('image_path', $cart->freeGift->image_path);
                $cart->freeGift->subtotal = $cart->freeGift->discount_price;
            }
    
            // Process each cart meta data
            $cartMetas = $cart->cartMetas->map(function ($meta) {
                return [
                    'id' => $meta->id,
                    'subtotal' => $meta->total_price,
                    'quantity' => $meta->quantity,
                    'color' => $meta->productVariant ? $meta->productVariant->title : null,
                    'color_code' => $meta->productVariant ? intval( $meta->productVariant->color ): null,
                    'payment_plan' => $meta->payment_plan,
                    'product' => $meta->product?->makeHidden(['created_at', 'updated_at', 'status'])
                        ->setAttribute('image_path', $meta->product->image_path),
                    'product_variant' => $meta->productVariant ? $meta->productVariant->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->productVariant->image_path) : null,
                ];
            });

            $addOnMetas = $cart->addOns->map(function ($meta) {
                return [
                    'id' => $meta->id,
                    'subtotal' => $meta->total_price,
                    'quantity' => $meta->quantity,
                    'color' => null,
                    'color_code' => null,
                    'payment_plan' => $meta->payment_plan,
                    'add_on' => $meta->addOn->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->addOn->image_path),
                ];
            });
    
            // Attach the cart metas to the cart object
            $cart->cartMetas = $cartMetas;
            $cart->addOnMetas = $addOnMetas;
            
            if( !$cart->tax ) {
                $taxSettings = Option::getTaxesSettings();
                $cart->tax = Helper::numberFormatV2( ( $taxSettings ? (Helper::numberFormatV2(($taxSettings->option_value/100),2) * $cart->total_price) : 0 ), 2, true);
            }
    
            return $cart;
        });

        foreach( $userCarts as $userCart ) {
            $userCart->cart_metas = $userCart->cartMetas;
            $userCart->add_on_metas = $userCart->addOnMetas;
            // $userCart->cartMetas = null;
            unset($userCart->cartMetas);
            unset($userCart->addOnMetas);
            $userCart->cartMetas = $userCart->cart_metas;
            $userCart->addOnMetas = $userCart->add_on_metas;
        }
    
        // Return the response with the paginated carts data
        return response()->json([
            'message' => '',
            'message_key' => 'get_cart_success',
            'carts' => $userCarts,
        ]);
    }

    public static function addToCart( $request ) {

        if( !isset( $request->items ) ) {
            $request->merge(['items'=> []]);
        }

        $validator = Validator::make($request->all(), [
            'product_code' => [ 'required', 'exists:products,code'  ],
            'color' => [ 'required', 'exists:product_variants,color'  ],
            'quantity' => [ 'integer', 'min:1'  ],
            'payment_plan' => [ 'nullable', 'in:1,2,3'  ],
            'session_key' => ['nullable', 'exists:carts,session_key'],
            'promo_code' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $existsInPromoCode = \DB::table('vouchers')->where('promo_code', $value)->exists();
                    $existsInId = \DB::table('vouchers')->where('id', $value)->exists();

                    if (!$existsInPromoCode && !$existsInId) {
                        $fail(__('The :attribute must exist in either the promo_code or id column.'));
                    }
                },
            ],
        ]);

        $validator->validate();

        if( $request->promo_code ){
            $test = self::validateCartVoucher($request);

            if ($test->getStatusCode() === 422) {
                return $test;
            }
        }

        DB::beginTransaction();
        try {
        
            $orderPrice = 0;
            $subtotal = 0;

            $voucher = Voucher::where( 'promo_code', $request->promo_code )->where( 'status', 10 )->first();
            $product = Product::where( 'code', $request->product_code )->first();
            $productVariant = ProductVariant::where( 'color', $request->color )->where( 'product_id', $product->id )->first();

            $cart = Cart::updateOrCreate(
                ['session_key' => $request->session_key], // Find cart by session_key
                [
                    'product_id' => NULL,
                    'product_variant_id' => NULL,
                    'user_id' => NULL,
                    'total_price' => 0,
                    'discount' => 0,
                    'status' => 10,
                    'voucher_id' => NULL,
                    'session_key' => $request->session_key ?? Helper::generateCartSessionKey(), // Keep existing or generate new
                    'tax' => 0,
                    'subtotal' => 0,
                    'additional_charges' => 0,
                    'payment_plan' => NULL,
                    'remarks' => NULL,
                    'step' => 1,
                ]
            );

            $productPrice = $product->price;

            switch ( $request->payment_plan ) {
                case 1:
                    $productPrice = $productVariant ? $productVariant->upfront : $product->price;
                    break;
                case 2:
                    $productPrice = $productVariant ? $productVariant->monthly : $product->price;
                    break;
                case 3:
                    $productPrice = $productVariant ? $productVariant->outrigh : $product->pricet;
                    break;
            }

            $cartMeta = CartMeta::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $productVariant ? $productVariant->id : null,
                ], // Search criteria
            
                [
                    'user_id' => NULL,
                    'discount' => NULL,
                    'status' => 10,
                    'products' => NULL,
                    'payment_plan' => $request->payment_plan,
                    'additional_charges' => NULL,
                    'total_price' => ($productPrice * $request->quantity), // Default for new entry
                    'quantity' => $request->quantity, // Default for new entry
                ]
            );
            
            // If the record already existed, manually increment quantity & total_price
            if (!$cartMeta->wasRecentlyCreated) {
                $cartMeta->increment('quantity', $request->quantity);
                $cartMeta->increment('total_price', $productPrice * $request->quantity);
            }

            $cart->load( ['cartMetas', 'addOns', 'freeGift'] );

            foreach( $cart->cartMetas as $cartMetas ) {
                $orderPrice += $cartMetas->total_price;
                $subtotal += $cartMetas->total_price;
            }

            if( $request->promo_code ){
                $voucher = Voucher::where( 'id', $request->promo_code )
                ->orWhere('promo_code', $request->promo_code)->first();

                if ( $voucher->discount_type == 3 ) {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $requestedProductIds = collect($request->input('items'))->pluck('product');
                    $x = $requestedProductIds->intersect($adjustment->buy_products)->count();
        
                    if ( $x >= $adjustment->buy_quantity ) {
                        $getProductMeta = $cart->cartMetas
                        ->where('product_id', $adjustment->get_product)
                        ->sortBy('total_price')
                        ->first();

                        if ($getProductMeta) {
                            $orderPrice -= Helper::numberFormatV2($getProductMeta->total_price,2,false,true);
                            $orderPrice += $getProductMeta->additional_charges;
                            $cart->discount = Helper::numberFormatV2($getProductMeta->total_price,2,false,true);
                            $getProductMeta->total_price = 0 + $getProductMeta->additional_charges;
                            $getProductMeta->save();
                        }
                    }

                } else if ( $voucher->discount_type == 2 ) {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );

                    $x = $orderPrice;

                    if ( $x >= $adjustment->buy_quantity ) {
                        $orderPrice -= $adjustment->discount_quantity;
                        $cart->discount = Helper::numberFormatV2($adjustment->discount_quantity,2 ,false, true);
                    }
        
                } else {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $orderPrice;
                    if ( $x >= $adjustment->buy_quantity ) {
                        $cart->discount = Helper::numberFormatV2(( $orderPrice * $adjustment->discount_quantity / 100 ),2 ,false, true);
                        $orderPrice = $orderPrice - ( $orderPrice * $adjustment->discount_quantity / 100 );
                    }
                }

                $cart->voucher_id = $voucher->id;
            }

            if( $cart->addOns ) {
                $orderPrice += $cart->addOns->sum( 'total_price' );
                $subtotal += $cart->addOns->sum( 'total_price' );   
            }

            if( $cart->freeGift ) {
                $orderPrice += $cart->freeGift->discount_price ? $cart->freeGift->discount_price : 0;   
                $subtotal += $cart->freeGift->discount_price ? $cart->freeGift->discount_price : 0;   
            }

            $cart->total_price = Helper::numberFormatV2($orderPrice,2);
            $cart->subtotal = Helper::numberFormatV2($subtotal,2);
            $taxSettings = Option::getTaxesSettings();
            $cart->tax = $taxSettings ? (Helper::numberFormatV2(($taxSettings->option_value/100),2) * Helper::numberFormatV2($cart->total_price,2)) : 0;
            
            $cart->total_price += Helper::numberFormatV2($cart->tax, 2,false,true);
            $cart->save();
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        $cartMetas = $cart->cartMetas->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'quantity' => $meta->quantity,
                'color' => $meta->productVariant ? $meta->productVariant->title : null,
                'color_code' => $meta->productVariant ? intval( $meta->productVariant->color ): null,
                'payment_plan' => $meta->payment_plan,
                'product' => $meta->product->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->product->image_path),
                'product_variant' => $meta->productVariant ?$meta->productVariant->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->productVariant->image_path) :null,
            ];
        });

        if($cart->voucher){
            $cart->voucher->makeHidden( [ 'created_at', 'updated_at', 'type', 'status', 'min_spend', 'min_order', 'buy_x_get_y_adjustment', 'discount_amount' ] )
            ->append(['decoded_adjustment', 'image_path','voucher_type','voucher_type_label']);
        }

        $addOnMetas = $cart->addOns->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'quantity' => $meta->quantity,
                'color' => null,
                'color_code' => null,
                'payment_plan' => $meta->payment_plan,
                'add_on' => $meta->addOn->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->addOn->image_path),
            ];
        });
        
        if($cart->freeGift){
            $cart->freeGift->makeHidden( [ 'created_at', 'updated_at' ] )
            ->append([ 'image_path' ]);

            $cart->freeGift->subtotal = $cart->freeGift->discount_price;
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'add_to_cart_success',
            'sesion_key' => $cart->session_key,
            'cart_id' => $cart->id,
            'total_price' => Helper::numberFormatV2($cart->total_price, 2,false, true),
            'cart_metas' => $cartMetas,
            'add_on_metas' => $addOnMetas,
            'free_gift' => $cart->freeGift,
            'subtotal' => Helper::numberFormatV2($cart->subtotal, 2,false, true),
            'discount' =>  Helper::numberFormatV2($cart->discount, 2,false, true),
            'tax' =>  Helper::numberFormatV2($cart->tax, 2,false, true),
            'voucher' => $cart->voucher ? $cart->voucher->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
        ] );
    }

    public static function updateCart( $request ) {

        if( !isset( $request->items ) ) {
            $request->merge(['items'=> []]);
        }

        $validator = Validator::make( $request->all(), [
            'id' => ['nullable', 'exists:carts,id', 'required_without:session_key'],
            'session_key' => ['nullable', 'exists:carts,session_key', 'required_without:id'],
            'payment_plan' => [ 'nullable', 'in:1,2,3'  ],
            'product_code' => ['nullable', Rule::exists('products', 'code')],
            'color' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$request->product_code) {
                        return;
                    }

                    $exists = \DB::table('product_variants')
                        ->where('color', $value)
                        ->where('product_id', Product::where( 'code', $request->product_code )->first()->id)
                        ->exists();

                    if (!$exists) {
                        $fail('The selected color is invalid for the given product code.');
                    }
                } ],
            'quantity' => [ 'required', 'integer', 'min:1'  ],
            'cart_item' => ['nullable', 'exists:cart_metas,id',
                function ($attribute, $value, $fail) use ( $request ) {
                    $cart = Cart::with(['cartMetas']);    
    
                    if ($request->has('id')) {
                        $cart->where('id', $request->id);
                    }
                
                    if ($request->has('session_key')) {
                        $cart->where('session_key', $request->session_key);
                    }
                
                    // Retrieve the cart(s) based on the applied filters
                    $updateCart = $cart->first();

                    if ( count($updateCart->cartMetas->where( 'id', $value )) == 0 ) {
                        $fail(__('Cart Item not available'));
                    }
                },
            ],
            'promo_code' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $existsInPromoCode = \DB::table('vouchers')->where('promo_code', $value)->exists();
                    $existsInId = \DB::table('vouchers')->where('id', $value)->exists();

                    if (!$existsInPromoCode && !$existsInId) {
                        $fail(__('The :attribute must exist in either the promo_code or id column.'));
                    }
                },
            ],
        ] );

        $validator->validate();

        if( $request->promo_code ){
            $test = self::validateCartVoucher($request);

            if ($test->getStatusCode() === 422) {
                return $test;
            }
        }

        $user = auth()->user();
        $query = Cart::with(['cartMetas', 'addons', 'freeGift'])
        ->where('status',10);
    
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        if ($request->has('session_key')) {
            $query->where('session_key', $request->session_key);
        }
    
        // Retrieve the cart(s) based on the applied filters
        $updateCart = $query->first();

        DB::beginTransaction();

        try {
            DB::beginTransaction();
        
            $orderPrice = 0;
            $subtotal = 0;
            $updateCart->load(['cartMetas']);
        
            $voucher = Voucher::where('promo_code', $request->promo_code)->where('status', 10)->first();

            $product = Product::where('code', $request->product_code)->first();

            $productVariant = ProductVariant::where('color', $request->color)
                ->where('product_id', $product->id)
                ->first();
                
            // Assign voucher if available
            $updateCart->voucher_id = optional($voucher)->id;
        
            if ($request->has('cart_item')) {
                
                $cartMeta = CartMeta::find($request->cart_item);

                $paymentPlan = $request->payment_plan ? $request->payment_plan : $cartMeta->payment_plan;

                $productPrice = $product->price;

                switch ( $request->payment_plan ) {
                    case 1:
                        $productPrice = $productVariant ? $productVariant->upfront : $product->price;
                        break;
                    case 2:
                        $productPrice = $productVariant ? $productVariant->monthly : $product->price;
                        break;
                    case 3:
                        $productPrice = $productVariant ? $productVariant->outright : $product->price;
                        break;
                }

                if ($cartMeta) {
                    $cartMeta->update([
                        'product_id'        => $product->id,
                        'product_variant_id'=> $productVariant ? $productVariant->id : null,
                        'quantity'          => $request->quantity,
                        'total_price'       => $productPrice * $request->quantity,
                        'payment_plan'      => $paymentPlan,
                    ]);
                }
            } else {
                CartMeta::where('cart_id', $updateCart->id)->delete();
        
                $paymentPlan = $request->payment_plan;

                $productPrice = $product->price;

                switch ( $request->payment_plan ) {
                    case 1:
                        $productPrice = $productVariant ? $productVariant->upfront : $product->price;
                        break;
                    case 2:
                        $productPrice = $productVariant ? $productVariant->monthly : $product->price;
                        break;
                    case 3:
                        $productPrice = $productVariant ? $productVariant->outright : $product->price;
                        break;
                }

                $cartMeta = CartMeta::create([
                    'cart_id'           => $updateCart->id,
                    'product_id'        => $product->id,
                    'product_variant_id'=> $productVariant ? $productVariant->id : null,
                    'quantity'          => $request->quantity,
                    'total_price'       => $productPrice * $request->quantity,
                    'status'            => 10,
                    'payment_plan'      => $request->payment_plan ,
                ]);
            }

            $updateCart->load( ['cartMetas'] );

            foreach( $updateCart->cartMetas as $cartMetas ) {
                $orderPrice += $cartMetas->total_price;
                $subtotal += $cartMetas->total_price;
            }
        
            if ($voucher) {
                $orderPrice = self::applyVoucherDiscount($updateCart, $voucher, $orderPrice, $request);
            }

            if( $updateCart->addOns ) {
                $orderPrice += $updateCart->addOns->sum( 'total_price' );
                $subtotal += $updateCart->addOns->sum( 'total_price' );   
            }

            if( $updateCart->freeGift ) {
                $orderPrice += $updateCart->freeGift->discount_price ? $updateCart->freeGift->discount_price : 0;   
                $subtotal += $updateCart->freeGift->discount_price ? $updateCart->freeGift->discount_price : 0;   
            }

            $updateCart->total_price = Helper::numberFormatV2($orderPrice, 2);
            $updateCart->subtotal = Helper::numberFormatV2($subtotal, 2);
        
            $taxSettings = Option::getTaxesSettings();
            $updateCart->tax = $taxSettings ? Helper::numberFormatV2(($taxSettings->option_value / 100) * $updateCart->total_price, 2) : 0;
            $updateCart->total_price += $updateCart->tax;
        
            $updateCart->save();
        
            DB::commit();
        
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
        

        $updateCart->save();
        $updateCart->load('cartMetas');
        DB::commit();

        $cartMetas = $updateCart->cartMetas->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'product' => $meta->product->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->product->image_path),
                'product_variant' => $meta->productVariant ? $meta->productVariant->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->productVariant->image_path) : null,
                'quantity' => $meta->quantity,
                'color' => $meta->productVariant ? $meta->productVariant->title : null,
                'color_code' => $meta->productVariant ? intval( $meta->productVariant->color ): null,
                'payment_plan' => $meta->payment_plan,
            ];
        });

        $addOnMetas = $updateCart->addOns->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'quantity' => $meta->quantity,
                'color' => null,
                'color_code' => null,
                'payment_plan' => $meta->payment_plan,
                'add_on' => $meta->addOn->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->addOn->image_path),
            ];
        });
        
        if($updateCart->freeGift){
            $updateCart->freeGift->makeHidden( [ 'created_at', 'updated_at' ] )
            ->append([ 'image_path' ]);

            $updateCart->freeGift->subtotal = $updateCart->freeGift->discount_price;

        }

        return response()->json( [
            'message' => '',
            'message_key' => 'update_cart_success',
            'sesion_key' => $updateCart->session_key,
            'cart_id' => $updateCart->id,
            'total_price' => Helper::numberFormatV2($updateCart->total_price, 2,false, true),
            'cart_metas' => $cartMetas,
            'add_on_metas' => $addOnMetas,
            'free_gift' => $updateCart->freeGift,
            'subtotal' => Helper::numberFormatV2($updateCart->subtotal, 2,false, true),
            'discount' =>  Helper::numberFormatV2($updateCart->discount, 2,false, true),
            'tax' =>  Helper::numberFormatV2($updateCart->tax, 2,false, true),
            'voucher' => $updateCart->voucher ? $updateCart->voucher->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
        ] );
    }

    private static function applyVoucherDiscount($cart, $voucher, $orderPrice, $request)
    {

        $adjustment = json_decode($voucher->buy_x_get_y_adjustment);
        
        if ($voucher->discount_type == 3) {

            $requestedProductIds = collect($request->input('items'))->pluck('product');
            $x = $requestedProductIds->intersect($adjustment->buy_products)->count();

            if ($x >= $adjustment->buy_quantity) {
                $getProductMeta = $cart->cartMetas
                    ->where('product_id', $adjustment->get_product)
                    ->sortBy('total_price')
                    ->first();

                if ($getProductMeta) {
                    $orderPrice -= Helper::numberFormatV2($getProductMeta->total_price, 2, false, true);
                    $cart->discount = Helper::numberFormatV2($getProductMeta->total_price, 2, false, true);
                    $getProductMeta->total_price = 0 + $getProductMeta->additional_charges;
                    $getProductMeta->save();
                }
            }

        } elseif ($voucher->discount_type == 2) {
            // ✅ Flat Discount
            if ($orderPrice >= $adjustment->buy_quantity) {
                $orderPrice -= $adjustment->discount_quantity;
                $cart->discount = Helper::numberFormatV2($adjustment->discount_quantity, 2, false, true);
            }

        } else {
            // ✅ Percentage Discount
            if ($orderPrice >= $adjustment->buy_quantity) {
                $discountAmount = $orderPrice * $adjustment->discount_quantity / 100;
                $cart->discount = Helper::numberFormatV2($discountAmount, 2, false, true);
                $orderPrice -= $discountAmount;
            }
        }

        $cart->voucher_id = $voucher->id;
        return $orderPrice;
    }

    public static function updateCartAddon( $request ) {

        $validator = Validator::make( $request->all(), [
            'id' => ['nullable', 'exists:carts,id', 'required_without:session_key'],
            'session_key' => ['nullable', 'exists:carts,session_key', 'required_without:id'],
            'quantity' => [ 'nullable', ],
            'type' => [ 'required', 'in:1,2' ],
            'add_on' => [
                'nullable',
                // 'required_without:free_gift',
                Rule::exists('product_add_ons', 'code')->where('status', 10),
                function ($attribute, $value, $fail) use ($request) {

                    $cart = Cart::where('session_key', $request->session_key)->first();
                    $cartMeta = $cart->cartMetas->pluck('product_id');

                    // Get the free gift
                    $freeGift = ProductAddOn::where('code', $value)->first();
                    if (!$freeGift) {
                        return $fail('Invalid free gift code.');
                    }

                    // Check if the product is linked to the free gift
                    if (!$freeGift->addOnProducts()->whereIn('product_id', $cartMeta)->exists()) {
                        return $fail('The selected free gift is not applicable for this product.');
                    }
                },
            ],
            'free_gift' => [
                'nullable',
                // 'required_without:add_on',
                Rule::exists('product_free_gifts', 'code')->where('status', 10),
                function ($attribute, $value, $fail) use ($request) {

                    $cart = Cart::where('session_key', $request->session_key)->first();
                    $cartMeta = $cart->cartMetas->pluck('product_id');

                    // Get the free gift
                    $freeGift = ProductFreeGift::where('code', $value)->first();
                    if (!$freeGift) {
                        return $fail('Invalid free gift code.');
                    }

                    // Check if the product is linked to the free gift
                    if (!$freeGift->freeGiftProducts()->whereIn('product_id', $cartMeta)->exists()) {
                        return $fail('The selected free gift is not applicable for this product.');
                    }
                },
            ],
        ] );

        $validator->validate();
        $user = auth()->user();
        $query = Cart::with(['cartMetas','addons', 'freeGift']);
    
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        if ($request->has('session_key')) {
            $query->where('session_key', $request->session_key);
        }
    
        // Retrieve the cart(s) based on the applied filters
        $updateCart = $query->first();

        if ( !$updateCart ) {
            return response()->json( [
                'message' => '',
                'message_key' => 'cart_not_found',
                'errors' => [
                    'cart' => 'Cart not found'
                ]
            ], 422 );
        }

        DB::beginTransaction();

        try {

            $request->quantity = $request->quantity ? $request->quantity : 0;

            if( $request->add_on ) {

                $addOn = ProductAddOn::where( 'code', $request->add_on)->first();

                $discountPrice = $addOn->discount_price ? $addOn->discount_price : 0;

                $cartAddOn = CartAddOn::where([
                    'cart_id'   => $updateCart->id,
                    'add_on_id' => $addOn->id,
                ])->first();

                if ($cartAddOn) {

                    if ($request->quantity < 0 ) {
                        $decrementValue = abs($request->quantity); // Convert negative to positive

                        if ($cartAddOn->quantity > $decrementValue) {
                            // If the decrement value is less than current quantity, just decrement
                            $cartAddOn->decrement('quantity', $decrementValue);
                            $cartAddOn->decrement('total_price', $discountPrice * $decrementValue);
                        } else {
                            // If decrementing would make it zero or negative, delete the add-on
                            $cartAddOn->delete();
                        }

                        $updateCart->total_price -= $discountPrice;   
                        $updateCart->subtotal -= $discountPrice;   

                    } else {

                        if( $request->action == 'remove' ) {
                            $cartAddOn->delete();
                        } else {
                            // Otherwise, update or increment quantity normally
                            $cartAddOn->increment('quantity', $request->quantity);
                            $cartAddOn->increment('total_price', $discountPrice * $request->quantity);

                            $updateCart->total_price += $discountPrice * $request->quantity;   
                            $updateCart->subtotal += $discountPrice * $request->quantity; 
                        }  
                    }
                    
                } else {
                    // If there's no existing record and quantity is positive, create a new add-on
                    if ($request->quantity > 0) {
                        $cartAddOn = CartAddOn::create([
                            'cart_id'            => $updateCart->id,
                            'add_on_id'          => $addOn->id,
                            'user_id'            => null,
                            'discount'           => null,
                            'status'             => 10,
                            'products'           => null,
                            'payment_plan'       => $request->payment_plan,
                            'additional_charges' => null,
                            'total_price'        => $discountPrice * $request->quantity,
                            'quantity'           => $request->quantity,
                        ]);

                        $updateCart->total_price += $discountPrice * $request->quantity;   
                        $updateCart->subtotal += $discountPrice * $request->quantity;   
                    }
                }

            } else {
                if( $request->type == 1  ){
                    $updateCart->total_price -= $updateCart->addOns->sum( 'total_price' );   
                    $updateCart->subtotal -= $updateCart->addOns->sum( 'total_price' );   
                    $updateCart->addOns()->delete();
                }
            }

            if( $request->free_gift ) {

                if( $request->action == 'remove' ) {
                    $updateCart->total_price -= $updateCart->freeGift->discount_price ? $updateCart->freeGift->discount_price : 0;   
                    $updateCart->subtotal -= $updateCart->freeGift->discount_price ? $updateCart->freeGift->discount_price : 0;   
                    $updateCart->free_gift_id = null;  
                } else {
                    $freeGift = ProductFreeGift::where( 'code', $request->free_gift)->first();
                    if( $updateCart->freeGift ){
                        $updateCart->total_price -= $updateCart->freeGift->discount_price ? $updateCart->freeGift->discount_price : 0;   
                        $updateCart->subtotal -= $updateCart->freeGift->discount_price ? $updateCart->freeGift->discount_price : 0;   
                    }
                    $updateCart->free_gift_id = $freeGift->id;
                    $updateCart->total_price += $freeGift->discount_price ? $freeGift->discount_price : 0;
                    $updateCart->subtotal += $freeGift->discount_price ? $freeGift->discount_price : 0;
                }
            }else {
                if( $request->type == 2  ){
                    $updateCart->total_price -= $updateCart->freeGift->discount_price ? $updateCart->freeGift->discount_price : 0;   
                    $updateCart->subtotal -= $updateCart->freeGift->discount_price ? $updateCart->freeGift->discount_price : 0;   
                    $updateCart->free_gift_id = null;  
                }
            }

            $updateCart->save();
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        $cartMetas = $updateCart->cartMetas->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'quantity' => $meta->quantity,
                'color' => $meta->productVariant ? $meta->productVariant->title : null,
                'color_code' => $meta->productVariant ? intval( $meta->productVariant->color ): null,
                'payment_plan' => $meta->payment_plan,
                'product' => $meta->product->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->product->image_path),
                'product_variant' => $meta->productVariant ?$meta->productVariant->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->productVariant->image_path) :null,
            ];
        });

        $updateCart->load( [ 'addOns', 'freeGift' ] );

        if($updateCart->voucher){
            $updateCart->voucher->makeHidden( [ 'created_at', 'updated_at', 'type', 'status', 'min_spend', 'min_order', 'buy_x_get_y_adjustment', 'discount_amount' ] )
            ->append(['decoded_adjustment', 'image_path','voucher_type','voucher_type_label']);
        }

        $addOnMetas = $updateCart->addOns->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'quantity' => $meta->quantity,
                'color' => null,
                'color_code' => null,
                'payment_plan' => $meta->payment_plan,
                'add_on' => $meta->addOn->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->addOn->image_path),
            ];
        });
        
        if($updateCart->freeGift){
            $updateCart->freeGift->makeHidden( [ 'created_at', 'updated_at' ] )
            ->append([ 'image_path' ]);

            $updateCart->freeGift->subtotal = $updateCart->freeGift->discount_price;
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'update_cart_success',
            'sesion_key' => $updateCart->session_key,
            'cart_id' => $updateCart->id,
            'total_price' => Helper::numberFormatV2($updateCart->total_price, 2,false, true),
            'cart_metas' => $cartMetas,
            'add_on_metas' => $addOnMetas,
            'free_gift' => $updateCart->freeGift,
            'subtotal' => Helper::numberFormatV2($updateCart->subtotal, 2,false, true),
            'discount' =>  Helper::numberFormatV2($updateCart->discount, 2,false, true),
            'tax' =>  Helper::numberFormatV2($updateCart->tax, 2,false, true),
            'voucher' => $updateCart->voucher ? $updateCart->voucher->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
        ] );
    }

    public static function updateCartAddress( $request ) {

        $validator = Validator::make( $request->all(), [
            'id' => ['nullable', 'exists:carts,id', 'required_without:session_key'],
            'session_key' => ['nullable', 'exists:carts,session_key', 'required_without:id'],
            'fullname' => ['nullable', 'required_without:company_name'],
            'company_name' => ['nullable', 'required_without:fullname'],
            'email' => ['required', 'email'],
            'phone_number' => ['required'],
            'address_1' => ['required'],
            'address_2' => ['nullable'],
            'city' => ['required'],
            'state' => ['required'],
            'postcode' => ['required'],
            'country' => ['required'],
            'remarks' => ['nullable'],
            'payment_plan' => ['nullable', 'in:1,2,3'],
        ] );

        $validator->validate();
        $user = auth()->user();
        $query = Cart::with(['cartMetas','addons', 'freeGift']);
    
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        if ($request->has('session_key')) {
            $query->where('session_key', $request->session_key);
        }
    
        // Retrieve the cart(s) based on the applied filters
        $updateCart = $query->first();

        if ( !$updateCart ) {
            return response()->json( [
                'message' => '',
                'message_key' => 'cart_not_found',
                'errors' => [
                    'cart' => 'Cart not found'
                ]
            ], 422 );
        }

        DB::beginTransaction();

        try {

            $updateCart->fullname = $request->fullname;
            $updateCart->company_name = $request->company_name;
            $updateCart->email = $request->email;
            $updateCart->phone_number = $request->phone_number;
            $updateCart->address_1 = $request->address_1;
            $updateCart->address_2 = $request->address_2;
            $updateCart->city = $request->city;
            $updateCart->state = $request->state;
            $updateCart->postcode = $request->postcode;
            $updateCart->country = $request->country;
            $updateCart->remarks = $request->remarks;
            $updateCart->payment_plan = $request->payment_plan;
            $updateCart->step = 2;

            $updateCart->save();
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        $cartMetas = $updateCart->cartMetas->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'quantity' => $meta->quantity,
                'color' => $meta->productVariant ? $meta->productVariant->title : null,
                'color_code' => $meta->productVariant ? intval( $meta->productVariant->color ): null,
                'payment_plan' => $meta->payment_plan,
                'product' => $meta->product->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->product->image_path),
                'product_variant' => $meta->productVariant ?$meta->productVariant->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->productVariant->image_path) :null,
            ];
        });

        $addOnMetas = $updateCart->addOns->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'quantity' => $meta->quantity,
                'color' => null,
                'color_code' => null,
                'payment_plan' => $meta->payment_plan,
                'add_on' => $meta->addOn->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->addOn->image_path),
            ];
        });

        if($updateCart->voucher){
            $updateCart->voucher->makeHidden( [ 'created_at', 'updated_at', 'type', 'status', 'min_spend', 'min_order', 'buy_x_get_y_adjustment', 'discount_amount' ] )
            ->append(['decoded_adjustment', 'image_path','voucher_type','voucher_type_label']);
        } ;
        
        // if($updateCart->addOns){
        //     $addOns = $updateCart->addOns;
        //     foreach( $addOns as $addOn ) {
        //         $addOn->addOn->makeHidden( [ 'created_at', 'updated_at' ] )
        //         ->append([ 'image_path' ]);
        //     }
        // } 
        
        if($updateCart->freeGift){
            $updateCart->freeGift->makeHidden( [ 'created_at', 'updated_at' ] )
            ->append([ 'image_path' ]);

            $updateCart->freeGift->subtotal = $updateCart->freeGift->discount_price;
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'update_cart_address_success',
            'sesion_key' => $updateCart->session_key,
            'cart_id' => $updateCart->id,
            'total_price' => Helper::numberFormatV2($updateCart->total_price, 2,false, true),
            'cart_metas' => $cartMetas,
            'add_on_metas' => $addOnMetas,
            'free_gift' => $updateCart->freeGift,
            'subtotal' => Helper::numberFormatV2($updateCart->subtotal, 2,false, true),
            'discount' =>  Helper::numberFormatV2($updateCart->discount, 2,false, true),
            'tax' =>  Helper::numberFormatV2($updateCart->tax, 2,false, true),
            'voucher' => $updateCart->voucher ? $updateCart->voucher->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
        ] );
    }

    public static function deleteCart( $request ) {

        $validator = Validator::make( $request->all(), [
            'id' => ['nullable', 'exists:carts,id', 'required_without:session_key'],
            'session_key' => ['nullable', 'exists:carts,session_key', 'required_without:id'],
        ] );

        $validator->validate();
        $user = auth()->user();
        $query = Cart::with(['cartMetas','addons', 'freeGift']);
    
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        if ($request->has('session_key')) {
            $query->where('session_key', $request->session_key);
        }
    
        // Retrieve the cart(s) based on the applied filters
        $updateCart = $query->first();

        if ( !$updateCart ) {
            return response()->json( [
                'message' => '',
                'message_key' => 'cart_not_found',
                'errors' => [
                    'cart' => 'Cart not found'
                ]
            ], 422 );
        }

        DB::beginTransaction();

        try {
            $updateCart->status = 20;

            if( $updateCart->userBundle ){
                $userBundle = $updateCart->userBundle;
                $userBundle->cups_left += count($updateCart->cartMetas);
                $userBundle->save();
            }

            $updateCart->save();
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'delete_cart_success',
        ] );
    }

    public static function deleteCartItem( $request ) {

        $validator = Validator::make( $request->all(), [
            'id' => ['nullable', 'exists:carts,id'],
            'session_key' => ['nullable', 'exists:carts,session_key', 'required_without:id'],
            'cart_item' => ['nullable', 'exists:cart_metas,id',
                function ($attribute, $value, $fail) use ( $request ) {
                    $cart = Cart::with(['cartMetas','addons', 'freeGift']);    
    
                    if ($request->has('id')) {
                        $cart->where('id', $request->id);
                    }
                
                    if ($request->has('session_key')) {
                        $cart->where('session_key', $request->session_key);
                    }
                
                    // Retrieve the cart(s) based on the applied filters
                    $updateCart = $cart->first();

                    if ( count($updateCart->cartMetas->where( 'id', $value )) == 0 ) {
                        $fail(__('Cart Item not available'));
                    }
                },
            ],
        ] );

        $validator->validate();
        $user = auth()->user();
        $query = Cart::with(['cartMetas','addons', 'freeGift']);    
    
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        if ($request->has('session_key')) {
            $query->where('session_key', $request->session_key);
        }
    
        // Retrieve the cart(s) based on the applied filters
        $updateCart = $query->first();

        if ( !$updateCart ) {
            return response()->json( [
                'message' => '',
                'message_key' => 'cart_not_found',
                'errors' => [
                    'cart' => 'Cart not found'
                ]
            ], 422 );
        }

        DB::beginTransaction();

        try {
        
            CartMeta::where('id', $request->cart_item)->delete();

            $updateCart->load(['cartMetas']);

            $orderPrice = $updateCart->cartMetas->sum(fn($meta) => $meta->product->price ?? 0);

            $updateCart->total_price = Helper::numberFormatV2($orderPrice, 2);
            $taxSettings = Option::getTaxesSettings();
            $updateCart->tax = $taxSettings
                ? (Helper::numberFormatV2(($taxSettings->option_value / 100), 2) * Helper::numberFormatV2($updateCart->total_price, 2))
                : 0;
            $updateCart->total_price += Helper::numberFormatV2($updateCart->tax, 2);
            $updateCart->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }
        
        if( $updateCart->cartMetas ) {
            $cartMetas = $updateCart->cartMetas->map(function ($meta) {
                return [
                    'id' => $meta->id,
                    'subtotal' => $meta->total_price,
                    'quantity' => $meta->quantity,
                    'color' => $meta->productVariant ? $meta->productVariant->title : null,
                    'color_code' => $meta->productVariant ? intval( $meta->productVariant->color ): null,
                    'payment_plan' => $meta->payment_plan,
                    'product' => $meta->product->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->product->image_path),
                    'product_variant' => $meta->productVariant->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->productVariant->image_path),
                ];
            });
        }

        if( $updateCart->addOnMetas ) {
            $addOnMetas = $updateCart->addOnMetas->map(function ($meta) {
                return [
                    'id' => $meta->id,
                    'subtotal' => $meta->total_price,
                    'quantity' => $meta->quantity,
                    'color' => null,
                    'color_code' => null,
                    'payment_plan' => $meta->payment_plan,
                    'add_on' => $meta->addOn->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->addOn->image_path),
                ];
            });
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'delete_cart_item_success',
            'sesion_key' => $updateCart->session_key,
            'cart_id' => $updateCart->id,
            'total_price' => $updateCart->total_price,
            'cart_metas' => $cartMetas,
            'add_on_metas' => $addOnMetas
        ] );
    }

    public static function validateCartVoucher( $request ){

        $voucher = Voucher::where('status', 10)
            ->where( 'id', $request->promo_code )
            ->orWhere('promo_code', $request->promo_code)
            ->where(function ( $query) {
                $query->where(function ( $q) {
                    $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', Carbon::now());
                })
                ->where(function ( $q) {
                    $q->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', Carbon::now());
                });
        })->first();

        if ( !$voucher ) {
            return response()->json( [
                'message_key' => 'voucher_not_available',
                'message' => __('voucher.voucher_not_available'),
                'errors' => [
                    'voucher' => __('voucher.voucher_not_available')
                ]
            ], 422 );
        }

        // total claimable
        if ( $voucher->total_claimable <= 0 ) {
            return response()->json( [
                'message_key' => 'voucher_fully_claimed',
                'message' => __('voucher.voucher_fully_claimed'),
                'errors' => [
                    'voucher' => __('voucher.voucher_fully_claimed')
                ]
            ], 422 );
        }
        
        // check is has claimed this
        if( $voucher->type != 1 ){
            $userVoucher = UserVoucher::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->where('status',10)->first();
            if(!$userVoucher){
                if( $voucher->type == 2 ){
                    return response()->json( [
                        'message_key' => 'voucher_unclaimed',
                        'message' => __('voucher.voucher_unclaimed'),
                        'errors' => [
                            'voucher' => __('voucher.voucher_unclaimed')
                        ]
                    ], 422 );
                }else{
                    return response()->json( [
                        'message_key' => 'voucher_condition_not_met',
                        'message' => __('voucher.voucher_condition_not_met'),
                        'errors' => [
                            'voucher' => __('voucher.voucher_condition_not_met')
                        ]
                    ], 422 );
                }
            }
        }

        if( !$request->cart_item ){

            if ( $voucher->discount_type == 3 ) {

                $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
    
                $requestedProductIds = collect($request->input('items'))->pluck('product');
                $x = $requestedProductIds->intersect($adjustment->buy_products)->count();

                if ( $x < $adjustment->buy_quantity ) {
                    return response()->json( [
                        'required_amount' => $adjustment->buy_quantity,
                        'message' => __( 'voucher.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] ),
                        'message_key' => 'voucher.min_quantity_of_x_' . $adjustment->buy_products[0] . '_' .  Product::find( $adjustment->buy_products[0] )->value( 'title' ) ,
                        'errors' => [
                            'voucher' => __( 'voucher.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] )
                        ]
                    ], 422 );
                }
                
                $y = $requestedProductIds->intersect($adjustment->get_product)->count();
    
                if (in_array($adjustment->get_product, $adjustment->buy_products)) {
                    if( $adjustment->buy_quantity == $adjustment->get_quantity ){
                        $y = $x;
                    } else {
                        $y -= $adjustment->buy_quantity;
                    }
                } 
    
                if ( $y < $adjustment->get_quantity ) {
                    return response()->json( [
                        'required_amount' => $adjustment->get_quantity,
                        'message' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->get_quantity . ' ' . Product::where( 'id',  $adjustment->get_product[0] )->value( 'title' ) ] ),
                        'message_key' => 'voucher.min_quantity_of_y',
                        'errors' => [
                            'voucher' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->get_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] )
                        ]
                    ], 422 );
                }
    
            } else {

                $product = Product::where( 'code', $request->product_code )->first();
                $productVariant = ProductVariant::where( 'color', $request->color )->where( 'product_id', $product->id )->first();
    
                $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );

                $productPrice = $product->price;

                switch ( $request->payment_plan ) {
                    case 1:
                        $productPrice = $productVariant->upfront;
                        break;
                    case 2:
                        $productPrice = $productVariant->monthly;
                        break;
                    case 3:
                        $productPrice = $productVariant->outright;
                        break;
                }

                $orderPrice = $productPrice * $request->quantity;

                if ( $orderPrice < $adjustment->buy_quantity ) {
                    return response()->json( [
                        'required_amount' => $adjustment->buy_quantity,
                        'message' => __( 'voucher.min_spend_of_x', [ 'title' => $adjustment->buy_quantity ] ),
                        'message_key' => 'voucher.min_spend_of_x',
                        'errors' => [
                            'voucher' => __( 'voucher.min_spend_of_x', [ 'title' => $adjustment->buy_quantity ] )
                        ]
                    ], 422 );
                }
    
            }
        } else {

            $cartItem = CartMeta::find( $request->cart_item );

            $product = Product::where( 'code', $cartItem->product->code )->first();
            $productVariant = ProductVariant::where( 'color', $cartItem->productVariant->color )->where( 'product_id', $cartItem->product->id )->first();

            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );

            $productPrice = $product->price;

            switch ( $cartItem->payment_plan ) {
                case 1:
                    $productPrice = $productVariant->upfront;
                    break;
                case 2:
                    $productPrice = $productVariant->monthly;
                    break;
                case 3:
                    $productPrice = $productVariant->outright;
                    break;
            }

            $orderPrice = $productPrice * $request->quantity;

            if ( $orderPrice < $adjustment->buy_quantity ) {
                return response()->json( [
                    'required_amount' => $adjustment->buy_quantity,
                    'message' => __( 'voucher.min_spend_of_x', [ 'title' => $adjustment->buy_quantity ] ),
                    'message_key' => 'voucher.min_spend_of_x',
                    'errors' => [
                        'voucher' => __( 'voucher.min_spend_of_x', [ 'title' => $adjustment->buy_quantity ] )
                    ]
                ], 422 );
            }

        }
    
        return response()->json( [
            'message' => 'voucher.voucher_validated',
        ] );
    }

    public static function checkCartEligibility( $request, $cartMeta ){

        $voucher = Voucher::where('status', 10)
            ->where( 'id', $request->promo_code )
            ->orWhere('promo_code', $request->promo_code)
            ->where(function ( $query) {
                $query->where(function ( $q) {
                    $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', Carbon::now());
                })
                ->where(function ( $q) {
                    $q->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', Carbon::now());
                });
        })->first();

        if ( !$voucher ) {
            return response()->json( [
                'message' => 'voucher.voucher_not_available',
                'message_key' => 'voucher.voucher_not_available',
                'errors' => [
                    'voucher' => __( 'voucher.voucher_not_available' )
                ]
            ], 422 );
        }

        $user = auth()->user();
        $voucherUsages = VoucherUsage::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->get();

        if ( $voucherUsages->count() >= $voucher->usable_amount ) {
            return response()->json( [
                'message' => __('voucher.voucher_you_have_maximum_used'),
                'message_key' => 'voucher.voucher_you_have_maximum_used',
                'errors' => [
                    'voucher' => __( 'voucher.voucher_you_have_maximum_used' )
                ]
            ], 422 );
        }

        // total claimable
        if ( $voucher->total_claimable <= 0 ) {
            return response()->json( [
                'message' => __('voucher.voucher_fully_claimed'),
               'message_key' => 'voucher.voucher_fully_claimed',
                'errors' => [
                    'voucher' => __( 'voucher.voucher_fully_claimed' )
                ]
            ], 422 );
        }
        
        // check is has claimed this
        if( $voucher->type != 1 ){
            $userVoucher = UserVoucher::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->where('status',10)->first();
            if(!$userVoucher){
                if( $voucher->type == 2 ){
                    return response()->json( [
                        'message_key' => 'voucher_unclaimed',
                        'message' => __('voucher.voucher_unclaimed'),
                        'errors' => [
                            'voucher' => __( 'voucher.voucher_unclaimed' )
                        ]
                    ], 422 );
                }else{
                    return response()->json( [
                        'message_key' => 'voucher_unclaimed',
                        'message' => __('voucher.voucher_condition_not_met'),
                        'errors' => [
                            'voucher' => __( 'voucher.voucher_condition_not_met' )
                        ]
                    ], 422 );
                }
            }
        }

        if ( $voucher->discount_type == 3 ) {

            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );

            $requestedProductIds = $cartMeta->pluck('product_id');
            $x = $requestedProductIds->intersect($adjustment->buy_products)->count();
 
            if ( $x < $adjustment->buy_quantity ) {

                return response()->json( [
                    'required_amount' => $adjustment->buy_quantity,
                    'message' => __( 'voucher.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] ),
                    'message_key' => 'voucher.min_quantity_of_x_' . $adjustment->buy_products[0] . '_' .  Product::find( $adjustment->buy_products[0] )->value( 'title' ) ,

                    'errors' => [
                        'voucher' => __( 'voucher.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] )

                    ]
                ], 422 );
            }
            
            $y = $requestedProductIds->intersect($adjustment->get_product)->count();

            if (in_array($adjustment->get_product, $adjustment->buy_products)) {
                if( $adjustment->buy_quantity == $adjustment->get_quantity ){
                    $y = $x;
                } else {
                    $y -= $adjustment->buy_quantity;
                }
            } 

            if ( $y < $adjustment->get_quantity ) {
                return response()->json( [
                    'required_amount' => $adjustment->get_quantity,
                    'message' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->get_quantity . ' ' . Product::where( 'id',  $adjustment->get_product[0] )->value( 'title' ) ] ),
                    'message_key' => 'voucher.min_quantity_of_y',
                    'errors' => [
                        'voucher' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->get_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] )
                    ]
                ], 422 );
            }

        } else {

            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
            $orderPrice = 0;
            if(isset($request->items)){
                foreach ( $request->items as $product ) {

                    $froyos = $product['froyo'];
                    $froyoCount = count($froyos);
                    $syrups = $product['syrup'];
                    $syrupCount = count($syrups);
                    $toppings = $product['topping'];
                    $toppingCount = count($toppings);
                    $product = Product::find($product['product']);
                    $metaPrice = 0;
    
                    $orderPrice += $product->price ?? 0;
    
                    // new calculation 
                    $froyoPrices = Froyo::whereIn('id', $froyos)->sum('price');
                    $orderPrice += $froyoPrices;

                    $syrupPrices = Syrup::whereIn('id', $syrups)->sum('price');
                    $orderPrice += $syrupPrices;

                    $toppingPrices = Topping::whereIn('id', $toppings)->sum('price');
                    $orderPrice += $toppingPrices;
                }
            }

            if ( $orderPrice < $adjustment->buy_quantity ) {
                return response()->json( [
                    'required_amount' => $adjustment->buy_quantity,
                    'message' => __( 'voucher.min_spend_of_x', [ 'title' => $adjustment->buy_quantity ] ),
                    'message_key' => 'voucher.min_spend_of_x',
                    'errors' => [
                        'voucher' => __( 'voucher.min_spend_of_x', [ 'title' => $adjustment->buy_quantity ] )
                    ]
                ], 422 );
            }

        }
    
        return response()->json( [
            'message' => 'voucher.voucher_validated',
        ] );
    }

    public static function calculateBundleCharges( $cartMetas ){
        
        $totalCartDeduction = 0;
        
        foreach( $cartMetas as $cartMeta ){
            $cartMeta->total_price = 0;

            $froyos = json_decode($cartMeta->froyos, true);
            $syrups = json_decode($cartMeta->syrups, true);
            $toppings = json_decode($cartMeta->toppings, true);

            $product = Product::find( $cartMeta->product_id );

            // calculate free item
            $froyoPrices = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
            rsort($froyoPrices);

            $froyoCount = count($froyos);
            $freeCount = $product->free_froyo_quantity;

            if ($froyoCount > $freeCount) {
                $chargeableCount = $froyoCount - $freeCount;
                $chargeableFroyoPrices = array_slice($froyoPrices, 0, $chargeableCount, true);
                $totalDeduction = array_sum($chargeableFroyoPrices);
                $cartMeta->total_price += $totalDeduction;
                $totalCartDeduction += $totalDeduction;
            }

            $syrupPrices = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
            rsort($syrupPrices);

            $syrupCount = count($syrups);
            $freeCount = $product->free_syrup_quantity;

            if ($syrupCount > $freeCount) {
                $chargeableCount = $syrupCount - $freeCount;
                $chargeablesyrupPrices = array_slice($syrupPrices, 0, $chargeableCount, true);

                $totalDeduction = array_sum($chargeablesyrupPrices);
                $cartMeta->total_price += $totalDeduction;
                $totalCartDeduction += $totalDeduction;
            }
        
            $toppingPrices = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
            rsort($toppingPrices);
            
            $toppingCount = count($toppings);
            $freeCount = $product->free_topping_quantity;

            if ($toppingCount > $freeCount) {
                $chargeableCount = $toppingCount - $freeCount;
                $chargeabletoppingPrices = array_slice($toppingPrices, 0, $chargeableCount, true);
                $totalDeduction = array_sum($chargeabletoppingPrices);

                $cartMeta->total_price += $totalDeduction;
                $totalCartDeduction += $totalDeduction;

            }

            $cartMeta->save();
        }
        return $totalCartDeduction;
    }
}
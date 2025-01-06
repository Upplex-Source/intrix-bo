<?php

namespace App\Services;

use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    Cart,
    CartMeta,
    Froyo,
    Syrup,
    Topping,
    Product,
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
        ]);
    
        // If validation fails, it will automatically throw an error
        $validator->validate();
    
        // Get the current authenticated user
        $user = auth()->user();
    
        // Start by querying carts for the authenticated user
        $query = Cart::where('user_id', $user->id)
        ->with(['cartMetas','vendingMachine'])
        ->where('status',10);
    
        // If 'id' is provided, filter by cart ID
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        // If 'session_key' is provided, filter by session key
        if ($request->has('session_key')) {
            $query->where('session_key', $request->session_key);
        }
    
        // Retrieve the cart(s) based on the applied filters
        $userCarts = $query->get()
        ->makeHidden( ['products','froyos','syrups','toppings'] );
    
        // If no carts are found, return an empty response with a success message
        if ($userCarts->isEmpty()) {
            return response()->json([
                'message' => '',
                'message_key' => 'get_cart_success',
                'carts' => []
            ]);
        }

        foreach ($userCarts as $cart) {
            // Prepare the cart metas data to be returned for each cart
            $cart->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $cart->vendingMachine->operational_hour)->setAttribute('image_path', $cart->vendingMachine->image_path);

            $cartMetas = $cart->cartMetas->map(function ($meta) {
                return [
                    'id' => $meta->id,
                    'subtotal' => $meta->total_price,
                    'product' => $meta->product->makeHidden(['created_at', 'updated_at', 'status'])->setAttribute('image_path', $meta->product->image_path),
                    'froyo' => $meta->froyos_metas,
                    'syrup' => $meta->syrups_metas,
                    'topping' => $meta->toppings_metas,
                ];
            });
        
            // Attach the cart metas to the cart object
            $cart->cartMetas = $cartMetas;
        }
    
        // Return the response with the carts data
        return response()->json([
            'message' => '',
            'message_key' => 'get_cart_success',
            'carts' => $userCarts,
        ]);
    }

    public static function addToCart( $request ) {

        $validator = Validator::make($request->all(), [
            'vending_machine' => [ 'required', 'exists:vending_machines,id'  ],
            'items.*.product' => [ 'nullable', 'exists:products,id' ],
            'items.*.froyo' => [ 'nullable', 'exists:froyos,id' ],
            'items.*.syrup' => [ 'nullable', 'exists:syrups,id' ],
            'items.*.topping' => [ 'nullable', 'exists:toppings,id' ],
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
        
            $orderPrice = 0;

            $cart = Cart::create( [
                'user_id' => auth()->user()->id,
                'product_id' => null,
                'product_bundle_id' => null,
                'outlet_id' => null,
                'vending_machine_id' => $request->vending_machine,
                'total_price' => $orderPrice,
                'discount' => 0,
                'status' => 10,
                'session_key' => Helper::generateCartSessionKey(),
            ] );

            foreach ( $request->items as $product ) {

                $froyos = $product['froyo'];
                $froyoCount = count($froyos);
                $syrups = $product['syrup'];
                $syrupCount = count($syrups);
                $toppings = $product['topping'];
                $toppingCount = count($toppings);
                $product = Product::find($product['product']);
                $metaPrice = 0;

                $orderMeta = CartMeta::create( [
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'product_bundle_id' => null,
                    'froyos' =>  json_encode($froyos),
                    'syrups' =>  json_encode($syrups),
                    'toppings' =>  json_encode($toppings),
                    'total_price' =>  $metaPrice,
                ] );

                $orderPrice += $product->price ?? 0;
                $metaPrice += $product->price ?? 0;

                if (($product->default_froyo_quantity != null || $product->default_froyo_quantity != 0 ) && $froyoCount > $product->default_froyo_quantity) {
                    $froyoPrices = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
                    asort($froyoPrices);
                    $mostExpensiveFroyoPrice = end($froyoPrices);
                    $orderPrice += $mostExpensiveFroyoPrice;
                    $metaPrice += $mostExpensiveFroyoPrice;
                } 
                
                if (($product->default_syrup_quantity != null || $product->default_syrup_quantity != 0 ) && $syrupCount > $product->default_syrup_quantity) {
                    $syrupPrices = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
                    asort($syrupPrices);
                    $mostExpensiveSyrupPrice = end($syrupPrices);
                    $orderPrice += $mostExpensiveSyrupPrice;
                    $metaPrice += $mostExpensiveSyrupPrice;
                } 

                if (($product->default_topping_quantity != null || $product->default_topping_quantity != 0 ) && $toppingCount > $product->default_topping_quantity) {
                    $toppingPrices = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
                    asort($toppingPrices);
                    $mostExpensiveToppingPrice = end($toppingPrices);
                    $orderPrice += $mostExpensiveToppingPrice;
                    $metaPrice += $mostExpensiveToppingPrice;
                }

                $orderMeta->total_price = $metaPrice;
                $orderMeta->save();
            }

            $cart->total_price = $orderPrice;
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
                'product' => $meta->product->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->product->image_path),
                'froyo' => $meta->froyos_metas,
                'syrup' => $meta->syrups_metas,
                'topping' => $meta->toppings_metas,
            ];
        });

        return response()->json( [
            'message' => '',
            'message_key' => 'add_to_cart_success',
            'sesion_key' => $cart->session_key,
            'cart_id' => $cart->id,
            'vending_machine' => $cart->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $cart->vendingMachine->operational_hour),
            'total' => $cart->total_price,
            'cart_metas' => $cartMetas
        ] );
    }

    public static function updateCart( $request ) {

        $validator = Validator::make( $request->all(), [
            'id' => ['nullable', 'exists:carts,id', 'required_without:session_key'],
            'session_key' => ['nullable', 'exists:carts,session_key', 'required_without:id'],
            'vending_machine' => [ 'required', 'exists:vending_machines,id'  ],
            'items.*.product' => [ 'nullable', 'exists:products,id' ],
            'items.*.froyo' => [ 'nullable', 'exists:froyos,id' ],
            'items.*.syrup' => [ 'nullable', 'exists:syrups,id' ],
            'items.*.topping' => [ 'nullable', 'exists:toppings,id' ],
        ] );

        $validator->validate();
        $user = auth()->user();
        $query = Cart::where('user_id', $user->id)
        ->with(['cartMetas','vendingMachine'])
        ->where('status',10);
    
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
            ] );
        }

        DB::beginTransaction();

        try {
        
            $orderPrice = 0;

            $updateCart->vending_machine_id = $request->vending_machine;
            CartMeta::where( 'cart_id', $updateCart->id )->delete();

            foreach ( $request->items as $product ) {

                $froyos = $product['froyo'];
                $froyoCount = count($froyos);
                $syrups = $product['syrup'];
                $syrupCount = count($syrups);
                $toppings = $product['topping'];
                $toppingCount = count($toppings);
                $product = Product::find($product['product']);
                $metaPrice = 0;

                $orderMeta = CartMeta::create( [
                    'cart_id' => $updateCart->id,
                    'product_id' => $product->id,
                    'product_bundle_id' => null,
                    'froyos' =>  json_encode($froyos),
                    'syrups' =>  json_encode($syrups),
                    'toppings' =>  json_encode($toppings),
                    'total_price' =>  $metaPrice,
                ] );

                $orderPrice += $product->price ?? 0;
                $metaPrice += $product->price ?? 0;

                if (($product->default_froyo_quantity != null || $product->default_froyo_quantity != 0 ) && $froyoCount > $product->default_froyo_quantity) {
                    $froyoPrices = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
                    asort($froyoPrices);
                    $mostExpensiveFroyoPrice = end($froyoPrices);
                    $orderPrice += $mostExpensiveFroyoPrice;
                    $metaPrice += $mostExpensiveFroyoPrice;
                } 
                
                if (($product->default_syrup_quantity != null || $product->default_syrup_quantity != 0 ) && $syrupCount > $product->default_syrup_quantity) {
                    $syrupPrices = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
                    asort($syrupPrices);
                    $mostExpensiveSyrupPrice = end($syrupPrices);
                    $orderPrice += $mostExpensiveSyrupPrice;
                    $metaPrice += $mostExpensiveSyrupPrice;
                } 

                if (($product->default_topping_quantity != null || $product->default_topping_quantity != 0 ) && $toppingCount > $product->default_topping_quantity) {
                    $toppingPrices = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
                    asort($toppingPrices);
                    $mostExpensiveToppingPrice = end($toppingPrices);
                    $orderPrice += $mostExpensiveToppingPrice;
                    $metaPrice += $mostExpensiveToppingPrice;
                }

                $orderMeta->total_price = $metaPrice;
                $orderMeta->save();
            }

            $updateCart->total_price = $orderPrice;
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
                'product' => $meta->product->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->product->image_path),
                'froyo' => $meta->froyos_metas,
                'syrup' => $meta->syrups_metas,
                'topping' => $meta->toppings_metas,
            ];
        });

        return response()->json( [
            'message' => '',
            'message_key' => 'update_cart_success',
            'sesion_key' => $updateCart->session_key,
            'cart_id' => $updateCart->id,
            'vending_machine' => $updateCart->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $updateCart->vendingMachine->operational_hour),
            'total' => $updateCart->total_price,
            'cart_metas' => $cartMetas
        ] );
    }

    public static function deleteCart( $request ) {

        $validator = Validator::make( $request->all(), [
            'id' => ['nullable', 'exists:carts,id', 'required_without:session_key'],
            'session_key' => ['nullable', 'exists:carts,session_key', 'required_without:id'],
        ] );

        $validator->validate();
        $user = auth()->user();
        $query = Cart::where('user_id', $user->id)
        ->with(['cartMetas','vendingMachine']);
    
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
            ] );
        }

        DB::beginTransaction();

        try {
            $updateCart->status = 20;
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
}
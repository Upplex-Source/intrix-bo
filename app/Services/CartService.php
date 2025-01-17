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
    Voucher,
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
        $query = Cart::where('user_id', $user->id)
            ->with(['cartMetas', 'vendingMachine', 'voucher'])
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

            $cart->vendingMachine->makeHidden(['created_at', 'updated_at', 'status'])
                ->setAttribute('operational_hour', $cart->vendingMachine->operational_hour)
                ->setAttribute('image_path', $cart->vendingMachine->image_path);
    
            // Process each cart meta data
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
    
            return $cart;
        });
    
        // Return the response with the paginated carts data
        return response()->json([
            'message' => '',
            'message_key' => 'get_cart_success',
            'carts' => $userCarts,
        ]);
    }
    

    public static function addToCart( $request ) {

        $validator = Validator::make($request->all(), [
            'vending_machine' => [ 'required', 'exists:vending_machines,id'  ],
            'items' => ['nullable', 'array'],
            '' => ['nullable', 'array'],
            'items.*.product' => ['required', 'exists:products,id'],
            'items.*.froyo' => ['nullable', 'array'],
            'items.*.froyo.*' => ['exists:froyos,id'], // Validate each froyo ID
            'items.*.syrup' => ['nullable', 'array'],
            'items.*.syrup.*' => ['exists:syrups,id'], // Validate each syrup ID
            'items.*.topping' => ['nullable', 'array'],
            'items.*.topping.*' => ['exists:toppings,id'], // Validate each topping ID
            'promo_code' => ['nullable', 'exists:vouchers,promo_code'],
        ]);

        if (isset($request->items)) {
            $validator->after(function ($validator) use ($request) {
                foreach ($request->items as $index => $item) {
                    // Fetch the product and its default quantities
                    $product = Product::find($item['product']);
        
                    if (!$product) {
                        $validator->errors()->add("items.$index.product", 'Invalid product ID.');
                        continue;
                    }
        
                    // Check froyo quantity
                    if (isset($item['froyo']) && count($item['froyo']) > $product->default_froyo_quantity) {
                        $validator->errors()->add("items.$index.froyo", "You can select up to {$product->default_froyo_quantity} froyos.");
                    }
        
                    // Check syrup quantity
                    if (isset($item['syrup']) && count($item['syrup']) > $product->default_syrup_quantity) {
                        $validator->errors()->add("items.$index.syrup", "You can select up to {$product->default_syrup_quantity} syrups.");
                    }
        
                    // Check topping quantity
                    if (isset($item['topping']) && count($item['topping']) > $product->default_topping_quantity) {
                        $validator->errors()->add("items.$index.topping", "You can select up to {$product->default_topping_quantity} toppings.");
                    }
                }
            });
        }
        
        if ($validator->fails()) {
            $rawErrors = $validator->errors()->toArray();
            $formattedErrors = [
                'vending_machine' => $rawErrors['vending_machine'][0] ?? null, // Include vending machine error
                'items' => []
            ];
        
            foreach ($rawErrors as $key => $messages) {
                // Handle items validation errors
                if (preg_match('/items\.(\d+)\.(\w+)/', $key, $matches)) {
                    $index = $matches[1]; // Extract index (e.g., 0)
                    $field = $matches[2]; // Extract field (e.g., froyo)
        
                    // Group errors by index
                    if (!isset($formattedErrors['items'][$index])) {
                        $formattedErrors['items'][$index] = [];
                    }
        
                    $formattedErrors['items'][$index][$field] = $messages[0]; // Add the first error message
                }
            }
        
            // Remove null vending machine error if not present
            if (!$formattedErrors['vending_machine']) {
                unset($formattedErrors['vending_machine']);
            }
        
            return response()->json(['errors' => $formattedErrors], 422);
        }
        
        $validator->validate();

        DB::beginTransaction();
        try {
        
            $orderPrice = 0;
            $voucher = Voucher::where( 'promo_code', $request->promo_code )->where( 'status', 10 )->first();

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
                'voucher_id' => $voucher ? $voucher->id :null,
            ] );

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
    
                    // new calculation 
                    $froyoPrices = Froyo::whereIn('id', $froyos)->sum('price');
                    $orderPrice += $froyoPrices;
                    $metaPrice += $froyoPrices;

                    $syrupPrices = Syrup::whereIn('id', $syrups)->sum('price');
                    $orderPrice += $syrupPrices;
                    $metaPrice += $syrupPrices;

                    $toppingPrices = Topping::whereIn('id', $toppings)->sum('price');
                    $orderPrice += $toppingPrices;
                    $metaPrice += $toppingPrices;

                    /*
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
                    */
    
                    $orderMeta->total_price = $metaPrice;
                    $orderMeta->save();
                }
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

        if($cart->voucher){
            $cart->voucher->makeHidden( [ 'created_at', 'updated_at', 'type', 'status', 'min_spend', 'min_order', 'buy_x_get_y_adjustment', 'discount_amount' ] )
            ->append(['decoded_adjustment', 'image_path','voucher_type','voucher_type_label']);
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'add_to_cart_success',
            'sesion_key' => $cart->session_key,
            'cart_id' => $cart->id,
            'vending_machine' => $cart->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $cart->vendingMachine->operational_hour),
            'total' => Helper::numberFormatV2($cart->total_price, 2, true),
            'cart_metas' => $cartMetas,
            'voucher' => $cart->voucher,
        ] );
    }

    public static function updateCart( $request ) {

        $validator = Validator::make( $request->all(), [
            'id' => ['nullable', 'exists:carts,id', 'required_without:session_key'],
            'session_key' => ['nullable', 'exists:carts,session_key', 'required_without:id'],
            'vending_machine' => [ 'required', 'exists:vending_machines,id'  ],
            'items' => ['nullable', 'array'],
            'items.*.product' => ['required', 'exists:products,id'],
            'items.*.froyo' => ['nullable', 'array'],
            'items.*.froyo.*' => ['exists:froyos,id'], // Validate each froyo ID
            'items.*.syrup' => ['nullable', 'array'],
            'items.*.syrup.*' => ['exists:syrups,id'], // Validate each syrup ID
            'items.*.topping' => ['nullable', 'array'],
            'items.*.topping.*' => ['exists:toppings,id'], // Validate each topping ID
            'cart_item' => ['nullable', 'exists:cart_metas,id'],
            'promo_code' => ['nullable', 'exists:vouchers,promo_code'],
        ] );

        if (isset($request->items)) {
            $validator->after(function ($validator) use ($request) {
                foreach ($request->items as $index => $item) {
                    // Fetch the product and its default quantities
                    $product = Product::find($item['product']);
        
                    if (!$product) {
                        $validator->errors()->add("items.$index.product", 'Invalid product ID.');
                        continue;
                    }
        
                    // Check froyo quantity
                    if (isset($item['froyo']) && count($item['froyo']) > $product->default_froyo_quantity) {
                        $validator->errors()->add("items.$index.froyo", "You can select up to {$product->default_froyo_quantity} froyos.");
                    }
        
                    // Check syrup quantity
                    if (isset($item['syrup']) && count($item['syrup']) > $product->default_syrup_quantity) {
                        $validator->errors()->add("items.$index.syrup", "You can select up to {$product->default_syrup_quantity} syrups.");
                    }
        
                    // Check topping quantity
                    if (isset($item['topping']) && count($item['topping']) > $product->default_topping_quantity) {
                        $validator->errors()->add("items.$index.topping", "You can select up to {$product->default_topping_quantity} toppings.");
                    }
                }
            });
        }
        
        if ($validator->fails()) {
            $rawErrors = $validator->errors()->toArray();
            $formattedErrors = [
                'vending_machine' => $rawErrors['vending_machine'][0] ?? null, // Include vending machine error
                'items' => []
            ];
        
            foreach ($rawErrors as $key => $messages) {
                // Handle items validation errors
                if (preg_match('/items\.(\d+)\.(\w+)/', $key, $matches)) {
                    $index = $matches[1]; // Extract index (e.g., 0)
                    $field = $matches[2]; // Extract field (e.g., froyo)
        
                    // Group errors by index
                    if (!isset($formattedErrors['items'][$index])) {
                        $formattedErrors['items'][$index] = [];
                    }
        
                    $formattedErrors['items'][$index][$field] = $messages[0]; // Add the first error message
                }
            }
        
            // Remove null vending machine error if not present
            if (!$formattedErrors['vending_machine']) {
                unset($formattedErrors['vending_machine']);
            }
        
            return response()->json(['errors' => $formattedErrors], 422);
        }

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

            $voucher = Voucher::where( 'promo_code', $request->promo_code )->where( 'status', 10 )->first();

            $updateCart->voucher_id = $voucher ? $voucher->id :null;
            
            if ($request->has('cart_item')) {
                $cartMeta = CartMeta::find($request->cart_item);
                if (!$cartMeta) {
                    return response()->json(['message' => 'Cart item not found.'], 404);
                }
    
                // Update specific cart item
                $product = Product::find($cartMeta->product_id);
                $froyos = $request->items[0]['froyo'] ?? [];
                $syrups = $request->items[0]['syrup'] ?? [];
                $toppings = $request->items[0]['topping'] ?? [];
    
                // Calculate new total price for this cart item
                $metaPrice = 0;
                $metaPrice += $product->price ?? 0;

                // new calculation 
                $froyoPrices = Froyo::whereIn('id', $froyos)->sum('price');
                $orderPrice += $froyoPrices;
                $metaPrice += $froyoPrices;

                $syrupPrices = Syrup::whereIn('id', $froyos)->sum('price');
                $orderPrice += $syrupPrices;
                $metaPrice += $syrupPrices;

                $toppingPrices = Topping::whereIn('id', $froyos)->sum('price');
                $orderPrice += $toppingPrices;
                $metaPrice += $toppingPrices;

                /*
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
                */

                $cartMeta->froyos = json_encode($froyos);
                $cartMeta->syrups = json_encode($syrups);
                $cartMeta->toppings = json_encode($toppings);
                $cartMeta->total_price = $metaPrice;
                $cartMeta->save();
    
                $orderPrice += $metaPrice;
    
            } else {
                // Update the entire cart, deleting all previous items
                CartMeta::where('cart_id', $updateCart->id)->delete();
    
                foreach ($request->items as $product) {
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
    
                    // new calculation 
                    $froyoPrices = Froyo::whereIn('id', $froyos)->sum('price');
                    $orderPrice += $froyoPrices;
                    $metaPrice += $froyoPrices;

                    $syrupPrices = Syrup::whereIn('id', $syrups)->sum('price');
                    $orderPrice += $syrupPrices;
                    $metaPrice += $syrupPrices;

                    $toppingPrices = Topping::whereIn('id', $toppings)->sum('price');
                    $orderPrice += $toppingPrices;
                    $metaPrice += $toppingPrices;

                    /*
    
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
                    */

                    $orderMeta->total_price = $metaPrice;
                    $orderMeta->save();
                }
                DB::commit();
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

        $updateCart->save();
        $updateCart->load('cartMetas');
        DB::commit();

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
            'total' => Helper::numberFormatV2($updateCart->total_price, 2, true),
            'cart_metas' => $cartMetas,
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

    public static function deleteCartItem( $request ) {

        $validator = Validator::make( $request->all(), [
            'id' => ['nullable', 'exists:carts,id'],
            'cart_item' => ['nullable', 'exists:cart_metas,id'],
        ] );

        $validator->validate();
        $user = auth()->user();
        $query = Cart::where('user_id', $user->id);
    
        if ($request->has('id')) {
            $query->where('id', $request->id);
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
        
            CartMeta::where('id', $request->cart_item)->delete();
            $orderPrice = 0;
            
            foreach ( $updateCart->cartMetas as $cartProduct ) {

                $froyos = json_decode($cartProduct->froyos,true);
                $froyoCount = count($froyos);
                $syrups = json_decode($cartProduct->syrups,true);
                $syrupCount = count($syrups);
                $toppings = json_decode($cartProduct->toppings,true);
                $toppingCount = count($toppings);
                $product = Product::find($cartProduct->product_id);

                $orderPrice += $product->price ?? 0;

                if (($product->default_froyo_quantity != null || $product->default_froyo_quantity != 0 ) && $froyoCount > $product->default_froyo_quantity) {
                    $froyoPrices = Froyo::whereIn('id', $froyos)->pluck('price', 'id')->toArray();
                    asort($froyoPrices);
                    $mostExpensiveFroyoPrice = end($froyoPrices);
                    $orderPrice += $mostExpensiveFroyoPrice;
                } 

                if (($product->default_syrup_quantity != null || $product->default_syrup_quantity != 0 ) && $syrupCount > $product->default_syrup_quantity) {
                    $syrupPrices = Syrup::whereIn('id', $syrups)->pluck('price', 'id')->toArray();
                    asort($syrupPrices);
                    $mostExpensiveSyrupPrice = end($syrupPrices);
                    $orderPrice += $mostExpensiveSyrupPrice;
                } 

                if (($product->default_topping_quantity != null || $product->default_topping_quantity != 0 ) && $toppingCount > $product->default_topping_quantity) {
                    $toppingPrices = Topping::whereIn('id', $toppings)->pluck('price', 'id')->toArray();
                    asort($toppingPrices);
                    $mostExpensiveToppingPrice = end($toppingPrices);
                    $orderPrice += $mostExpensiveToppingPrice;
                }
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
            'message_key' => 'delete_cart_item_success',
            'sesion_key' => $updateCart->session_key,
            'cart_id' => $updateCart->id,
            'vending_machine' => $updateCart->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $updateCart->vendingMachine->operational_hour),
            'total' => $updateCart->total_price,
            'cart_metas' => $cartMetas
        ] );
    }
}
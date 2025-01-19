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
    VoucherUsage,
    UserVoucher,
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
            'items.*.product' => ['required', 'exists:products,id'],
            'items.*.froyo' => ['nullable', 'array'],
            'items.*.froyo.*' => ['exists:froyos,id'], // Validate each froyo ID
            'items.*.syrup' => ['nullable', 'array'],
            'items.*.syrup.*' => ['exists:syrups,id'], // Validate each syrup ID
            'items.*.topping' => ['nullable', 'array'],
            'items.*.topping.*' => ['exists:toppings,id'], // Validate each topping ID
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
                'promo_code' => $rawErrors['promo_code'][0] ?? null, // Include promo_code error
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

            if (!$formattedErrors['promo_code']) {
                unset($formattedErrors['promo_code']);
            }

            return response()->json(['errors' => $formattedErrors], 422);
        }
        
        // check voucher type
        if ( $request->promo_code ) {

            $voucher = Voucher::where( 'id', $request->promo_code )
            ->orWhere('promo_code', $request->promo_code)->first();

            if( !$voucher ){
                return response()->json( [
                    'message' => 'Voucher not found',
                    'message_key' => 'voucher_not_found',
                ] );
            }

            if( $voucher->type == 1 ){
                return response()->json( [
                    'message' => 'Voucher Not applicable to cart',
                    'message_key' => 'voucher_not_applicable_to_cart',
                ] );
            }

            $test = self::validateCartVoucher($request);

            if ($test->getStatusCode() === 422) {
                return $test;
            }
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
    
                    $orderMeta->total_price = $metaPrice;
                    $orderMeta->save();
                }
            }
            
            // load relationship for later use
            $cart->load('cartMetas');

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
                            $orderPrice -= $getProductMeta->total_price;
                            $getProductMeta->total_price = 0;
                            $getProductMeta->save();
                        }
                    }

                } else if ( $voucher->discount_type == 2 ) {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );

                    $x = $orderPrice;

                    if ( $x >= $adjustment->buy_quantity ) {
                        $orderPrice -= $adjustment->discount_quantity;
                    }
        
                } else {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $orderPrice;
                    if ( $x >= $adjustment->buy_quantity ) {
                        $orderPrice = $orderPrice - ( $orderPrice * $adjustment->discount_quantity / 100 );
                    }
                }

                $cart->voucher_id = $voucher->id;
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
                'promo_code' => $rawErrors['promo_code'][0] ?? null, // Include promo_code error
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

            if (!$formattedErrors['promo_code']) {
                unset($formattedErrors['promo_code']);
            }
        
            return response()->json(['errors' => $formattedErrors], 422);
        }

        // check voucher type
        if ( $request->promo_code ) {

            $voucher = Voucher::where( 'id', $request->promo_code )
            ->orWhere('promo_code', $request->promo_code)->first();

            if( !$voucher ){
                return response()->json( [
                    'message' => 'Voucher not found',
                    'message_key' => 'voucher_not_found',
                ] );
            }

            if( $voucher->type == 1 ){
                return response()->json( [
                    'message' => 'Voucher Not applicable to cart',
                    'message_key' => 'voucher_not_applicable_to_cart',
                ] );
            }

            $test = self::validateCartVoucher($request);

            if ($test->getStatusCode() === 422) {
                return $test;
            }
        }

        // validate delete cart item
        if ($request->has('cart_item')) {
            $cartMeta = CartMeta::find($request->cart_item);
            if (!$cartMeta) {
                return response()->json(['message' => 'Cart item not found.'], 404);
            }

            if( !$request->items && $request->promo_codes){
                $cartMetaToDelete = CartMeta::find($request->cart_item);

                if ($cartMetaToDelete) {
                    $cart = $cartMetaToDelete->cart;
            
                    $remainingCartMetas = $cart->cartMetas->where('id', '!=', $cartMetaToDelete->id);
            
                    if( $request->promo_code || $cart->voucher_id ){
                        $isEligible = self::checkCartEligibility($request, $remainingCartMetas);

                        if ($isEligible->getStatusCode() === 422) {
                            return $isEligible;
                        }

                        if (!$isEligible) {
                            return response()->json([
                                'message' => 'Deleting this item will make the cart ineligible.',
                                'errors' => 'cart_ineligible',
                            ], 422);
                        }
                    }
                }
            }
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

            $updateCart->load( ['cartMetas'] );
            $updateCart->vending_machine_id = $request->vending_machine;

            $voucher = Voucher::where( 'promo_code', $request->promo_code )->where( 'status', 10 )->first();

            $updateCart->voucher_id = $voucher ? $voucher->id :null;
            
            if ($request->has('cart_item')) {
                $cartMeta = CartMeta::find($request->cart_item);
                if (!$cartMeta) {
                    return response()->json(['message' => 'Cart item not found.'], 404);
                }
                
                if( !$request->items ){
                    $orderPrice -= $cartMeta->total_price;
                    $orderPrice += $updateCart->cartMetas->sum('total_price') ?? 0;
                    $cartMeta->delete();
                }else{
                    // Update specific cart item
                    $product = Product::find($cartMeta->product_id);
                    $froyos = $request->items[0]['froyo'] ?? [];
                    $syrups = $request->items[0]['syrup'] ?? [];
                    $toppings = $request->items[0]['topping'] ?? [];
        
                    // Calculate new total price for this cart item
                    $metaPrice = 0;
                    $metaPrice += $product->price ?? 0;
                    $orderPrice -= $cartMeta->total_price;
                    $orderPrice += $updateCart->cartMetas->sum('total_price') ?? 0;

                    // new calculation 
                    $froyoPrices = Froyo::whereIn('id', $froyos)->sum('price');
                    // $orderPrice += $froyoPrices;
                    $metaPrice += $froyoPrices;

                    $syrupPrices = Syrup::whereIn('id', $syrups)->sum('price');
                    // $orderPrice += $syrupPrices;
                    $metaPrice += $syrupPrices;

                    $toppingPrices = Topping::whereIn('id', $toppings)->sum('price');
                    // $orderPrice += $toppingPrices;
                    $metaPrice += $toppingPrices;

                    $cartMeta->froyos = json_encode($froyos);
                    $cartMeta->syrups = json_encode($syrups);
                    $cartMeta->toppings = json_encode($toppings);
                    $cartMeta->total_price = $metaPrice;
                    $cartMeta->save();

                    $orderPrice += $metaPrice;

                    DB::commit();

                    if( $request->promo_code ){
                        $voucher = Voucher::where( 'id', $request->promo_code )
            ->orWhere('promo_code', $request->promo_code)->first();

                        if ( $voucher->discount_type == 3 ) {
        
                            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
                
                            $requestedProductIds = $updateCart->cartMetas->pluck('product_id');

                            $x = $requestedProductIds->intersect($adjustment->buy_products)->count();

                            if ( $x >= $adjustment->buy_quantity ) {
                                $getProductMeta = $updateCart->cartMetas
                                ->where('product_id', $adjustment->get_product)
                                ->sortBy('total_price')
                                ->first();                    
        
                                if ($getProductMeta) {
                                    $orderPrice -= $getProductMeta->total_price;
                                    // $getProductMeta->total_price = 0;
                                    $getProductMeta->save();
                                }
                            }
        
                        } else if ( $voucher->discount_type == 2 ) {
        
                            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
                
                            $x = $updateCart->total_price;
                            if ( $x >= $adjustment->buy_quantity ) {
                                $orderPrice -= $adjustment->discount_quantity;
                            }
                
                        } else {
        
                            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
                
                            $x = $updateCart->total_price;
                            if ( $x >= $adjustment->buy_quantity ) {
                                $orderPrice = $orderPrice - ( $orderPrice * $adjustment->discount_quantity );
                            }
                        }
        
                        $updateCart->voucher_id = $voucher->id;
                    }
                }
    
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

                    $orderMeta->total_price = $metaPrice;
                    $orderMeta->save();
                }

                $updateCart->load( ['cartMetas'] );

                if( $request->promo_code ){
                    $voucher = Voucher::where( 'id', $request->promo_code )
            ->orWhere('promo_code', $request->promo_code)->first();

                    if ( $voucher->discount_type == 3 ) {
    
                        $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
            
                        $requestedProductIds = collect($request->input('items'))->pluck('product');
                        $x = $requestedProductIds->intersect($adjustment->buy_products)->count();

                        if ( $x >= $adjustment->buy_quantity ) {
                            $getProductMeta = $updateCart->cartMetas
                            ->where('product_id', $adjustment->get_product)
                            ->sortBy('total_price')
                            ->first();                    

                            if ($getProductMeta) {
                                $orderPrice -= $getProductMeta->total_price;
                                $getProductMeta->total_price = 0;
                                $getProductMeta->save();
                            }
                        }
    
                    } else if ( $voucher->discount_type == 2 ) {
    
                        $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
            
                        $x = $updateCart->total_price;
                        if ( $x >= $adjustment->buy_quantity ) {
                            $orderPrice -= $adjustment->discount_quantity;
                        }
            
                    } else {
    
                        $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
            
                        $x = $updateCart->total_price;
                        if ( $x >= $adjustment->buy_quantity ) {
                            $orderPrice = $orderPrice - ( $orderPrice * $adjustment->discount_quantity );
                        }
                    }
    
                    $updateCart->voucher_id = $voucher->id;
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
                'message' => 'voucher.voucher_not_available',
                'errors' => 'voucher',
            ], 422 );
        }

        $user = auth()->user();
        $voucherUsages = VoucherUsage::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->get();

        if ( $voucherUsages->count() >= $voucher->usable_amount ) {
            return response()->json( [
                'message' => __('voucher.voucher_you_have_maximum_used'),
                'errors' => 'voucher',
            ], 422 );
        }

        // total claimable
        if ( $voucher->total_claimable <= 0 ) {
            return response()->json( [
                'message' => __('voucher.voucher_fully_claimed'),
                'errors' => 'voucher',
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
                        'errors' => 'voucher',
                    ], 422 );
                }else{
                    return response()->json( [
                        'message_key' => 'voucher_unclaimed',
                        'message' => __('voucher.voucher_condition_not_met'),
                        'errors' => 'voucher',
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
                        'message' => __( 'voucher.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity ] ),
                        'errors' => 'voucher',
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
                        'message' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->get_quantity ] ),
                        'errors' => 'voucher',
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
                        'errors' => 'voucher',
                    ], 422 );
                }
    
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
                'errors' => 'voucher',
            ], 422 );
        }

        $user = auth()->user();
        $voucherUsages = VoucherUsage::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->get();

        if ( $voucherUsages->count() >= $voucher->usable_amount ) {
            return response()->json( [
                'message' => __('voucher.voucher_you_have_maximum_used'),
                'errors' => 'voucher',
            ], 422 );
        }

        // total claimable
        if ( $voucher->total_claimable <= 0 ) {
            return response()->json( [
                'message' => __('voucher.voucher_fully_claimed'),
                'errors' => 'voucher',
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
                        'errors' => 'voucher',
                    ], 422 );
                }else{
                    return response()->json( [
                        'message_key' => 'voucher_unclaimed',
                        'message' => __('voucher.voucher_condition_not_met'),
                        'errors' => 'voucher',
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
                    'message' => __( 'voucher.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity ] ),
                    'errors' => 'voucher',
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
                    'message' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->get_quantity ] ),
                    'errors' => 'voucher',
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
                    'errors' => 'voucher',
                ], 422 );
            }

        }
    
        return response()->json( [
            'message' => 'voucher.voucher_validated',
        ] );
    }
}
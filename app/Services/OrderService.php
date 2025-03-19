<?php

namespace App\Services;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
    File,
};

use Illuminate\Validation\Rule;
use App\Models\{
    FileManager,
    Option,
    Order,
    OrderTransaction,
    OrderMeta,
    Product,
    Froyo,
    Syrup,
    Topping,
    Cart,
    CartMeta,
    Voucher,
    VoucherUsage,
    UserVoucher,
    ProductVariant,
    UserBundle,
    UserBundleHistory,
    UserBundleHistoryMeta,
    UserBundleTransaction,
    Guest,
    ProductAddOn,
    ProductFreeGift,
};

use Helper;
use IPay88\Request\RequestBuilder as IPay88RequestBuilder;

use Carbon\Carbon;

class OrderService
{
    public static function calendarAllOrders( $request ) {

        $orders = Order::where( 'invoice_date', '>=', $request->start )
            ->where( 'invoice_date', '<=', $request->end )
            ->orderBy( 'invoice_date', 'ASC' )
            ->get();

        $currentOrders = [];
        foreach ( $orders as $order ) {

            $plateNumber = $order->vehicle ? $order->vehicle->license_plate : '-';
            $notes = $order->notes ? $order->notes : '-';

            array_push( $currentOrders, [
                'id' => Helper::encode( $order->id ),
                'allDay' => true,
                'start' => $order->invoice_date . ' 00:00:00',
                'end' => $order->invoice_date . ' 23:59:59',
                'title' => [
                    'html' => 'Reference:' . $order->reference . '<br>Plate Number:' . $plateNumber . '<br>Notes:' . $notes,
                ],
                'color' => '#F79932',
            ] );
        }

        return response()->json( $currentOrders );
    }

    public static function allOrders( $request, $export = false ) {

        $order = Order::with( [
            'user',
            'orderMetas',
        ] )->select( 'orders.*' )
        ->orderBy( 'created_at', 'DESC' );
            
        $filterObject = self::filter( $request, $order );
        $order = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $order->orderBy( 'orders.order_date', $dir );
                    break;
                case 2:
                    $order->orderBy( 'orders.reference', $dir );
                    break;
                case 3:
                    $order->orderBy( 'orders.owner_id', $dir );
                    break;
                case 4:
                    $order->orderBy( 'orders.farm_id', $dir );
                    break;
                case 5:
                    $order->orderBy( 'orders.buyer_id', $dir );
                    break;
                case 6:
                    $order->orderBy( 'orders.status', $dir );
                    break;
            }
        }

        if ( $export == false ) {

            $orderCount = $order->count();

            $limit = $request->length;
            $offset = $request->start;

            $orders = $order->skip( $offset )->take( $limit )->get();

            if ( $orders ) {
                $orders->append( [
                    'encrypted_id',
                ] );
            }

            $totalRecord = Order::count();

            $data = [
                'orders' => $orders,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $orderCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

        } else {

            return $order->get();
        }        
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->order_date ) ) {
            if ( str_contains( $request->order_date, 'to' ) ) {
                $dates = explode( ' to ', $request->order_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'orders.order_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->order_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'orders.order_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->reference ) ) {
            $model->where( 'orders.reference', 'LIKE', '%' . $request->customer . '%' );
            $filter = true;
        }

        if ( !empty( $request->owner ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'owner', function ( $q ) use ( $request ) {
                    $q->where( 'fullname', 'LIKE', '%' . $request->owner . '%' );
                });
            });
            $filter = true;
        }

        if ( !empty( $request->farm ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'farm', function ( $q ) use ( $request ) {
                    $q->where( 'title', 'LIKE', '%' . $request->farm . '%' );
                });
            });
            $filter = true;
        }

        if ( !empty( $request->buyer ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'buyer', function ( $q ) use ( $request ) {
                    $q->where( 'name', 'LIKE', '%' . $request->buyer . '%' );
                });
            });
            $filter = true;
        }

        if ( !empty( $request->user ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'user', function ( $q ) use ( $request ) {
                    $q->where( 'phone_number', 'LIKE', '%' . $request->user . '%' );
                });
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'orders.status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneOrder( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $order = Order::with( [
            'orderMetas','user'
        ] )->find( $request->id );

        $orderMetas = $order->orderMetas->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'quantity' => $meta->quantity,
                'product' => $meta->product->makeHidden(['created_at', 'updated_at', 'status'])->setAttribute('image_path', $meta->product->image_path),
                'product_variant' => $meta->productVariant->makeHidden(['created_at', 'updated_at', 'status'])->setAttribute('image_path', $meta->product->image_path),
            ];
        });
    
        // Attach the cart metas to the cart object
        $order->orderMetas = $orderMetas;

        return response()->json( $order );
    }

    public static function getLatestOrderIncrement() {

        $latestOrder = Order::where( 'reference', 'LIKE', '%' . date( 'Y/m' ) . '%' )
            ->orderBy( 'reference', 'DESC' )
            ->first();

        if ( $latestOrder ) {
            $parts = explode( ' ', $latestOrder->reference );
            return $parts[1];
        }

        return 0;
    }

    public static function createOrder( $request ) {

        if ($request->has('products')) {
            $decodedProducts = [];
            foreach ($request->products as $product) {
                $productArray = json_decode($product, true);
        
                $productArray['productId'] = explode('-', $productArray['productId'])[0];
        
                $decodedProducts[] = $productArray;
            }
        
            $request->merge(['products' => $decodedProducts]);
        }

        $validator = Validator::make( $request->all(), [
            'user' => [ 'required', 'exists:users,id'  ],
            'vending_machine' => [ 'nullable', 'exists:vending_machines,id'  ],
            'products' => [ 'nullable' ],
            'products.*.productId' => [ 'nullable', 'exists:products,id' ],
            'products.*.froyo' => [ 'nullable', 'exists:froyos,id' ],
            'products.*.syrup' => [ 'nullable', 'exists:syrups,id' ],
            'products.*.topping' => [ 'nullable', 'exists:toppings,id' ],
        ] );

        $attributeName = [
            'user' => __( 'order.user' ),
            'vending_machine' => __( 'order.vending_machine' ),
            'products' => __( 'order.products' ),
            'froyo' => __( 'order.froyo' ),
            'syrup' => __( 'order.syrup' ),
            'topping' => __( 'order.topping' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $orderPrice = 0;

            $createOrder = Order::create( [
                'product_id' => null,
                'product_bundle_id' => null,
                'outlet_id' => null,
                'user_id' => $request->user,
                'vending_machine_id' => $request->vending_machine,
                'total_price' => 0,
                'discount' => 0,
                'reference' => Helper::generateOrderReference(),
                'payment_method' => 1,
                'status' => 1,
            ] );

            foreach ( $request->products as $product ) {
                $metaPrice = 0;

                $froyos = $product['froyo'];
                $froyoCount = count($froyos);
                $syrups = $product['syrup'];
                $syrupCount = count($syrups);
                $toppings = $product['topping'];
                $toppingCount = count($toppings);
                $product = Product::find($product['productId']);

                $orderMeta = OrderMeta::create( [
                    'order_id' => $createOrder->id,
                    'product_id' => $product->id,
                    'product_bundle_id' => null,
                    'froyos' =>  json_encode($froyos),
                    'syrups' =>  json_encode($syrups),
                    'toppings' =>  json_encode($toppings),
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
                */

                $orderMeta->total_price = $metaPrice;
                $orderMeta->save();
            }

            $createOrder->total_price = Helper::numberFormatV2($orderPrice,2);
            $createOrder->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.orders' ) ) ] ),
        ] );
    }

    public static function updateOrder( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'id' => [ 'required', 'exists:orders,id'  ],
            'products' => [ 'required' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
            'payment_plan' => [ 'nullable' ],
            'color' => [ 'nullable' ],
            'quantity' => [ 'numeric', 'min:1' ],
        ] );

        $attributeName = [
            'reference' => __( 'order.reference' ),
            'farm' => __( 'order.farm' ),
            'buyer' => __( 'order.buyer' ),
            'grade' => __( 'order.grade' ),
            'weight' => __( 'order.weight' ),
            'rate' => __( 'order.rate' ),
            'total' => __( 'order.total' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $orderPrice = 0;

            $updateOrder = Order::find( $request->id );
            $updateOrder->address_1 = $request->address_1;
            $updateOrder->address_2 = $request->address_2;
            $updateOrder->city = $request->city;
            $updateOrder->state = $request->state;
            $updateOrder->postcode = $request->postcode;
            $updateOrder->remarks = $request->remarks;
            $updateOrder->payment_plan = $request->payment_plan;
            $updateOrder->email = $request->email;
            $updateOrder->phone_number = $request->phone_number;
            $updateOrder->fullname = $request->fullname;
            $updateOrder->company_name = $request->company_name;
            $updateOrder->save();

            $color = $request->color ? $request->color : $updateOrder->orderMetas->first()->productVariant->color;
            OrderMeta::where( 'order_id', $updateOrder->id )->delete();

            $voucher = $updateOrder->voucher;
            $product = Product::find( $request->products );
            $productVariant = ProductVariant::where( 'color', $color )->where( 'product_id', $product->id )->first();

            $subtotal = 0;
            $orderPrice = 0;

            $taxSettings = Option::getTaxesSettings();

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
            
            $orderMeta = OrderMeta::create( [
                'order_id' => $updateOrder->id,
                'product_id' => $product->id,
                'product_variant_id' => $productVariant->id,
                'total_price' =>  $request->quantity * $productPrice,
                'quantity' => $request->quantity,
            ] );
            
            $orderPrice += $orderMeta->total_price;
            $subtotal += $orderMeta->total_price;

            if( $voucher ){
                if ( $voucher->discount_type == 3 ) {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $userCart->cartMetas->whereIn( 'product_id', $adjustment->buy_products )->count();

                    if ( $x >= $adjustment->buy_quantity ) {
                        $getProductMeta = $userCart->cartMetas
                        ->where('product_id', $adjustment->get_product)
                        ->sortBy('total_price')
                        ->first();                    

                        if ($getProductMeta) {

                            $discount = 0;
                            $discount += $getProductMeta->product->price;

                            $orderPrice -= Helper::numberFormatV2($discount,2,false,true);
                            $order->discount = Helper::numberFormatV2($discount,2,false,true);
                            $getProductMeta->total_price = 0 + $getProductMeta->additional_charges;
                            $getProductMeta->save();
                        }
                    }

                } else if ( $voucher->discount_type == 2 ) {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $orderMeta->total_price;
                    if ( $x >= $adjustment->buy_quantity ) {
                        $orderPrice -= $adjustment->discount_quantity;
                        $order->discount = Helper::numberFormatV2($adjustment->discount_quantity,2,false,true);
                    }
        
                } else {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $orderMeta->total_price;
                    if ( $x >= $adjustment->buy_quantity ) {
                        $order->discount = Helper::numberFormatV2(( $orderPrice * $adjustment->discount_quantity / 100 ),2,false,true);
                        $orderPrice = $orderPrice - ( $orderPrice * $adjustment->discount_quantity / 100 );
                    }
                }
            }
            $updateOrder->total_price = Helper::numberFormatV2($orderPrice,2,false,true);
            $updateOrder->tax = $taxSettings ? (Helper::numberFormatV2(($taxSettings->option_value/100),2) * Helper::numberFormatV2($order->total_price,2)) : 0;
            $updateOrder->total_price += Helper::numberFormatV2($updateOrder->tax,2,false,true);
            $updateOrder->subtotal = Helper::numberFormatV2($subtotal,2,false,true);
            $updateOrder->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.orders' ) ) ] ),
        ] );
    }

    public static function exportOrders($request)
    {
        $orders = self::allOrders($request, true);

        $grades = [
            'A',
            'B',
            'C',
            'D',
        ];

        $grandSubtotalTotal = $grandTotalTotal = 0;
        $grandRates['A']['rates'] = 0;
        $grandRates['A']['weight'] = 0;
        $grandRates['B']['rates'] = 0;
        $grandRates['B']['weight'] = 0;
        $grandRates['C']['rates'] = 0;
        $grandRates['C']['weight'] = 0;
        $grandRates['D']['rates'] = 0;
        $grandRates['D']['weight'] = 0;
    
        $html = '<table>';
    
        $html .= '
            <thead>
                <tr>
                    <th colspan="6"></th>
                    <th colspan="' . (count($grades) * 3) . '" class="text-center"><strong>' . __('order.order_items') . '</strong></th>
                    <th colspan="2"></th>
                <tr>
                    <th><strong>' . __('datatables.no') . '</strong></th>
                    <th><strong>' . __('order.reference') . '</strong></th>
                    <th><strong>' . __('order.order_date') . '</strong></th>
                    <th><strong>' . __('order.owner') . '</strong></th>
                    <th><strong>' . __('order.farm') . '</strong></th>
                    <th><strong>' . __('order.buyer') . '</strong></th>';
    
        foreach ($grades as $grade) {
            $html .= '<th><strong>' . __('order.grade') . '</strong></th>';
            $html .= '<th><strong>' . __('order.rate') . '</strong></th>';
            $html .= '<th><strong>' . __('order.weight') . '</strong></th>';
        }
    
        $html .= '<th><strong>' . __('order.total') . '</strong></th>';
        $html .= '</tr>
            </thead>';
        $html .= '<tbody>';
    
        foreach ($orders as $key => $order) {
    
            $html .= '
                <tr>
                    <td>' . (intval($key) + 1) . '</td>
                    <td>' . $order['reference'] . '</td>
                    <td>' . $order['order_date'] . '</td>
                    <td>' . ($order->farm->owner->name ?? '-') . '</td>
                    <td>' . ($order->farm->title ?? '-') . '</td>
                    <td>' . ($order->buyer->name ?? '-') . '</td>';
    
            $grandRates = [];
                            
            foreach($grades as $grade) {
                $grandRates[$grade]['rates'] = 0;
                $grandRates[$grade]['weight'] = 0;
            }
        
            foreach($order->orderMetas as $orderMeta) {
                $grade = $orderMeta['grade'];
                $grandRates[$grade]['rates'] += $orderMeta['rate'];
                $grandRates[$grade]['weight'] += $orderMeta['weight'];
            }

            foreach($grades as $grade) {
                 $html .= '<td>' . $grade . '</td>';
                 $html .= '<td>' . ( $grandRates[$grade]['rates'] != 0 ? $grandRates[$grade]['rates'] : '-' ) . '</td>';
                 $html .= '<td>' . ( $grandRates[$grade]['weight'] != 0 ? $grandRates[$grade]['weight'] : '-' ) . '</td>';              
            }
    
            // $html .= '<td>' . $order['subtotal'] . '</td>';
            $html .= '<td>' . $order['total'] . '</td>';
    
            $grandTotalTotal += $order['total'];
            $grandSubtotalTotal += $order['subtotal'];
    
            $html .= '</tr>';
        }
    
        $html .= '
            </tbody></table>';
    
        Helper::exportReport($html, 'Order');
    }

    public static function salesReport( $request ) {

        $date = $request->date ? $request->date : date( 'Y m' );

        $start = Carbon::createFromFormat( 'Y m', $date, 'Asia/Kuala_Lumpur' )->startOfMonth()->timezone( 'UTC' );

        $end = Carbon::createFromFormat( 'Y m', $date, 'Asia/Kuala_Lumpur' )->endOfMonth()->timezone( 'UTC' );

        $currenctPeriodSales = [];

        $salesRecords = Order::with( [
            'farm.owner',
            'buyer',
            'orderMetas',
        ] )->where( 'created_at', '>=', $start->format( 'Y-m-d H:i:s' ) )
            ->where( 'created_at', '<=', $end->format( 'Y-m-d H:i:s' ) )
            ->orderBy( 'created_at', 'DESC' )
            ->get();

        if ( $salesRecords ) {
            $salesRecords->append( [
                'encrypted_id',
            ] );
        }

        return [
            'orders' => $salesRecords->toArray(),
        ];
    }

    private static function getIngredientPrice($id, $type, $prices = null)
    {
        // If we already have the prices, we can directly use them
        if ($prices && isset($prices[$id])) {
            return $prices[$id];
        }
    
        // Otherwise, fall back to the original way (if necessary)
        $amount = 0;
    
        switch ($type) {
            case 'froyo':
                $amount = Froyo::find($id)->price;
                break;
            case 'syrup':
                $amount = Syrup::find($id)->price;
                break;
            case 'topping':
                $amount = Topping::find($id)->price;
                break;
            default:
                $amount = Froyo::find($id)->price;
                break;
        }
    
        return $amount;
    }

    public static function updateOrderStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateOrder = Order::find( $request->id );
            $updateOrder->status = $updateOrder->status != 20 ? 20 : 1;

            $updateOrder->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'froyo' => $updateOrder,
                    'message_key' => 'update_order_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_order_failed',
            ], 500 );
        }
    }

    public static function updateOrderStatusView( $request ) {

        DB::beginTransaction();

        try {

            $updateOrder = Order::find( $request->id );
            $updateOrder->status = $request->status;

            $updateOrder->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'froyo' => $updateOrder,
                    'message_key' => 'update_order_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_froyo_failed',
            ], 500 );
        }
    }

    public static function generateQrCode($order)
    {

        $orderId = $order->reference;

        // Set QR code options (optional)
        $options = new QROptions([
            'version'    => 5,   // Controls the size of the QR code
            'eccLevel'   => QRCode::ECC_L, // Error correction level (L, M, Q, H)
            'outputType' => QRCode::OUTPUT_IMAGE_PNG, // Image output format (PNG)
            'scale'      => 5,   // Pixel size
        ]);

        // Generate the QR code
        $qrcode = new QRCode($options);
        $qrImage = $qrcode->render($orderId);

        // Remove the "data:image/png;base64," prefix
        $base64Image = str_replace('data:image/png;base64,', '', $qrImage);
    
        // Decode the Base64 string
        $decodedImage = base64_decode($base64Image);

        $fileName = "qr-codes/order-{$orderId}.png";
        $filePath = "public/{$fileName}";

        // Save the QR code image in storage/app/public
        Storage::put($filePath, $decodedImage);

        // Generate the URL for the QR code
        $qrUrl = asset("storage/{$fileName}");

        return $qrUrl;
    }
    
    public static function getOrder($request)
    {
        // Validate the incoming request parameters (id and reference)
        $validator = Validator::make($request->all(), [
            'id' => ['nullable', 'exists:orders,id'],
            'status' => ['nullable', 'in:1,2,3,10,20'],
            'reference' => ['nullable', 'exists:orders,reference'],
            'per_page' => ['nullable', 'integer', 'min:1'], // Validate per_page input
        ]);
    
        // If validation fails, it will automatically throw an error
        $validator->validate();
    
        // Get the current authenticated user
        $user = auth()->user();
    
        // Start by querying orders for the authenticated user
        $query = Order::with(['voucher'])
            ->orderBy('created_at', 'DESC');
    
        // Apply filters
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
    
        if ($request->has('reference')) {
            $query->where('reference', $request->reference);
        }
    
        if ($request->has('user_bundle')) {
            $query->whereHas('userBundle');
        }
    
        // Use paginate instead of get
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $userOrders = $query->paginate($perPage);
    
        // Modify each order and its related data
        $userOrders->getCollection()->transform(function ($order) {
            $order->append( ['order_status_label'] );
    
            $orderMetas = $order->orderMetas->map(function ($meta) {
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

            $order->orderMetas = $orderMetas;

            return $order;
        });

        foreach( $userOrders as $userOrder ) {
            $userOrder->order_metas = $userOrder->orderMetas;
            $userOrder->orderMetas = null;
            unset($userOrder->orderMetas);
        }
    
        // Return the paginated response
        return response()->json([
            'message' => '',
            'message_key' => 'get_order_success',
            'orders' => $userOrders,
        ]);
    }
    

    public static function checkout( $request ) {

        $validator = Validator::make($request->all(), [
            'session_key' => [
                'required',
                function ($attribute, $value, $fail) {
                    $cart = DB::table('carts')->where('session_key', $value)->first();
        
                    if (!$cart) {
                        return $fail('The session key does not exist.');
                    }
        
                    if ($cart->status != 10) {
                        return $fail('Cart has Expired');
                    }
        
                    if ($cart->step != 2) {
                        return $fail('Please Update the shipment Details');
                    }
                },
            ],
            'cart_item' => [
                'nullable',
                Rule::exists('cart_metas', 'id')->where('status', 10),
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
            'fullname' => ['required'],
            'company_name' => ['nullable'],
            'email' => ['required'],
            'phone_number' => ['required'],
            'address_1' => ['required'],
            'address_2' => ['nullable'],
            'city' => ['required'],
            'state' => ['required'],
            'postcode' => ['required'],
            'country' => ['required'],
            'remarks' => ['nullable'],
            'payment_plan' => [ 'nullable', 'in:1,2,3'  ],
        ]);

        $user = auth()->user();

        $query = Cart::where('status',10);

        if ($request->has('id')) {
            $query->where('id', $request->id);
        }
    
        if ($request->has('session_key')) {
            $query->where('session_key', $request->session_key);
        }
    
        $userCart = $query->first();

        $validator->validate();

        if( $request->promo_code ){
            $test = self::validateVoucher($request);

            if ($test->getStatusCode() === 422) {
                return $test;
            }
        }

        DB::beginTransaction();
        try {
        
            $subtotal = 0;
            $orderPrice = 0;

            $voucher = Voucher::where( 'promo_code', $request->promo_code )->where( 'status', 10 )->first();

            $taxSettings = Option::getTaxesSettings();

            $guest = Guest::where('email', $request->email)->first() 
                ?? $userCart->guest 
                ?? Guest::create([
                'fullname' => $request->fullname,
                'email' => $request->email,
                'session_key' => $userCart->session_key,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'state' => $request->state,
                'postcode' => $request->postcode,
                'calling_code' => '+60',
                'status' => 10,
                'phone_number' => $request->phone_number,  
                'ip_address' => $request->ip(), // Get client's IP address
                'user_agent' => $request->header('User-Agent'), // Get client's user agent
                'last_visit' => now(), // Store current timestamp
                'country' => $request->country,
                'company_name' => $request->company_name,
            ] );

            // for checking payment plan
            $presetCart = CartMeta::where( 'cart_id', $userCart->id )->first();

            $order = Order::create( [
                'user_id' => null,
                'product_id' => null,
                'total_price' => $orderPrice,
                'discount' => 0,
                'status' => 1,
                'reference' => Helper::generateOrderReference(),
                'tax' => 0,
                'remarks' => $request->remarks,
                'payment_plan' => $presetCart->payment_plan ? $presetCart->payment_plan : $request->payment_plan,
                'guest_id' => $guest->id,
                'fullname' => $request->fullname,
                'email' => $request->email,
                'session_key' => $userCart->session_key,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'state' => $request->state,
                'postcode' => $request->postcode,
                'calling_code' => '+60',
                'status' => 10,
                'phone_number' => $request->phone_number,  
                'ip_address' => $request->ip(), // Get client's IP address
                'user_agent' => $request->header('User-Agent'), // Get client's user agent
                'last_visit' => now(), // Store current timestamp
                'country' => $request->country,
                'company_name' => $request->company_name,
                'add_on_id' => $userCart->add_on_id,
                'free_gift_id' => $userCart->free_gift_id,
            ] );

            if( $userCart->add_on_id ) {
                $addOn = $userCart->addOn;
                $orderPrice += $addOn->discount_price ? $addOn->discount_price : 0;
            }
    
            if( $userCart->free_gift_id ) {
                $freeGift = $userCart->freeGift;
                $orderPrice += $freeGift->discount_price ? $freeGift->discount_price : 0;
            }

            foreach( $userCart->cartMetas as $checkoutCart ) {
                $productPrice = $checkoutCart->product->price;

                switch ( $checkoutCart->payment_plan ) {
                    case 1:
                        $productPrice = $checkoutCart->productVariant ? $checkoutCart->productVariant->upfront : $checkoutCart->product->price;
                        break;
                    case 2:
                        $productPrice = $checkoutCart->productVariant ? $checkoutCart->productVariant->monthly : $checkoutCart->product->price;
                        break;
                    case 3:
                        $productPrice = $checkoutCart->productVariant ? $checkoutCart->productVariant->outright : $checkoutCart->product->price;
                        break;
                }
    
                $orderMeta = OrderMeta::create( [
                    'order_id' => $order->id,
                    'product_id' => $checkoutCart->product->id,
                    'product_variant_id' => $checkoutCart->productVariant ? $checkoutCart->productVariant->id : null,
                    'total_price' =>  $checkoutCart->quantity * $productPrice,
                    'quantity' => $checkoutCart->quantity,
                ] );
    
                $orderPrice += $orderMeta->total_price;
    
                $checkoutCart->status = 20;
                $checkoutCart->save();
    
                $order->subtotal = $orderPrice;
            }

            if( $request->promo_code || $userCart->voucher_id ){

                if( $request->promo_code ) {
                    $voucher = Voucher::where( 'id', $request->promo_code )
                    ->orWhere('promo_code', $request->promo_code)->first();
                }else if( $userCart->voucher_id ) {
                    $voucher = Voucher::where( 'id', $userCart->voucher_id )->first();
                }

                if ( $voucher->discount_type == 3 ) {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $userCart->cartMetas->whereIn( 'product_id', $adjustment->buy_products )->count();

                    if ( $x >= $adjustment->buy_quantity ) {
                        $getProductMeta = $userCart->cartMetas
                        ->where('product_id', $adjustment->get_product)
                        ->sortBy('total_price')
                        ->first();                    

                        if ($getProductMeta) {

                            $discount = 0;
                            $discount += $getProductMeta->product->price;

                            $orderPrice -= Helper::numberFormatV2($discount,2,false,true);
                            $order->discount = Helper::numberFormatV2($discount,2,false,true);
                            $getProductMeta->total_price = 0 + $getProductMeta->additional_charges;
                            $getProductMeta->save();
                        }
                    }

                } else if ( $voucher->discount_type == 2 ) {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $orderMeta->total_price;
                    if ( $x >= $adjustment->buy_quantity ) {
                        $orderPrice -= $adjustment->discount_quantity;
                        $order->discount = Helper::numberFormatV2($adjustment->discount_quantity,2,false,true);
                    }
        
                } else {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $orderMeta->total_price;
                    if ( $x >= $adjustment->buy_quantity ) {
                        $order->discount = Helper::numberFormatV2(( $orderPrice * $adjustment->discount_quantity / 100 ),2,false,true);
                        $orderPrice = $orderPrice - ( $orderPrice * $adjustment->discount_quantity / 100 );
                    }
                }

                $order->voucher_id = $voucher->id;
                
                VoucherUsage::create( [
                    'user_id' => null,
                    'order_id' => $order->id,
                    'voucher_id' => $voucher->id,
                    'status' => 10
                ] );

            }

            $order->load( ['orderMetas','addon', 'freeGift'] );

            $order->total_price = Helper::numberFormatV2($orderPrice,2,false,true);
            $order->tax = $taxSettings ? (Helper::numberFormatV2(($taxSettings->option_value/100),2) * Helper::numberFormatV2($order->total_price,2)) : 0;
            $order->total_price += Helper::numberFormatV2($order->tax,2,false,true);

            if (!$userCart->cartMetas->contains('status', 10)) {
                $userCart->status = 20;
                $userCart->save();
            }
            
            $userCart->save();

            $hasTestProduct = $userCart->cartMetas->contains(function ($cartMeta) {
                return $cartMeta->product && $cartMeta->product->code === 'TEST';
            });

            if( $hasTestProduct ) {
                $merchantKey = config('services.ipay88.merchant_key');
                $merchantCode = config('services.ipay88.merchant_code');
    
                $request = new \IPay88\Payment\Request( $merchantKey );
                $order_amount = number_format(1, 2, '.', '');
                $data = array(
                    'merchantCode' => $request->setMerchantCode( $merchantCode ),
                    'paymentId' =>  '',
                    'refNo' => $request->setRefNo( $order->reference ),
                    'amount' => $order_amount,
                    'currency' => $request->setCurrency( 'MYR' ),
                    'prodDesc' => $request->setProdDesc( 'Testing' ),
                    'userName' => $request->setUserName( $order->fullname ? $order->fullname : 'intrix_guest' ),
                    'userEmail' => $request->setUserEmail( $order->email ? $order->email : 'intrixguest@mail.com' ),
                    'userContact' => $request->setUserContact( $order->phone_number ? $order->phone_number : '123123123' ),
                    'remark' => $request->setRemark( 'test' ),
                    'lang' => $request->setLang( 'UTF-8' ),
                    'signature' => hash('sha256', $merchantKey.$merchantCode.$order->reference.strtr( $order_amount, array( '.' => '', ',' => '' ) ).'MYR' ),
                    'responseUrl'   => $request->setResponseUrl(config('services.ipay88.staging_callback_url')),
                    'backendUrl'    => $request->setBackendUrl(config('services.ipay88.staging_callback_url')),
                );

                $paymentUrl = route('payment.show', ['payment_data' => $data]);

                $orderTransaction = OrderTransaction::create( [
                    'order_id' => $order->id,
                    'checkout_id' => null,
                    'checkout_url' => null,
                    'payment_url' => $paymentUrl,
                    'transaction_id' => null,
                    'layout_version' => 'v1',
                    'redirect_url' => null,
                    'notify_url' => null,
                    'order_no' => $order->reference . '-' . $order->payment_attempt,
                    'order_title' => $order->reference,
                    'order_detail' => $order->reference,
                    'amount' => $order->total_price,
                    'currency' => 'MYR',
                    'transaction_type' => 1,
                    'status' => 10,
                ] );
    
                $order->payment_url = $paymentUrl;
                $order->order_transaction_id = $orderTransaction->id;
                $order->status = 2;
                $order->save();
    
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'payment_url' => $paymentUrl
                ]);
            }

            $order->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        $orderMetas = $order->orderMetas->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'quantity' => $meta->quantity,
                'color' => $meta->productVariant ? $meta->productVariant->title : null,
                'color_code' => $meta->productVariant ? intval( $meta->productVariant->color ): null,
                'payment_plan' => $meta->payment_plan,
                'product' => $meta->product->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->product->image_path),
                'product_variant' => $meta->productVariant ? $meta->productVariant->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->productVariant->image_path) : null,
            ];
        });

        return response()->json( [
            'message' => '',
            'message_key' => 'create_order_success',
            'payment_url' => $order->payment_url,
            'reference' => $order->reference,
            'order_id' => $order->id,
            'total' => Helper::numberFormatV2($order->total_price , 2 ,true),
            'order_metas' => $orderMetas,
            'voucher' => $order->voucher ? $order->voucher->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
        ] );
    }

    public static function directCheckout( $request ) {

        $validator = Validator::make($request->all(), [
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
            'fullname' => ['required'],
            'company_name' => ['nullable'],
            'email' => ['required'],
            'phone_number' => ['required'],
            'address_1' => ['required'],
            'address_2' => ['nullable'],
            'city' => ['required'],
            'state' => ['required'],
            'postcode' => ['required'],
            'country' => ['required'],
            'remarks' => ['nullable'],
            'product_code' => [ 'required', 'exists:products,code'  ],
            'color' => [ 'required', 'exists:product_variants,color'  ],
            'payment_plan' => [ 'nullable', 'in:1,2,3'  ],
            'quantity' => [ 'numeric', 'min:1'  ],
            'add_on' => [
                'nullable',
                Rule::exists('product_add_ons', 'code')->where('status', 10),
                function ($attribute, $value, $fail) use ($request) {
                    // Ensure product_code exists
                    if (!isset($request->product_code)) {
                        return $fail('The product_code is required for validation.');
                    }

                    // Get the product by code
                    $product = Product::where('code', $request->product_code)->where( 'status', 10 )->first();
                    if (!$product) {
                        return $fail('Invalid product code.');
                    }

                    // Get the free gift
                    $addOn = ProductAddOn::where('code', $value)->first();
                    if (!$addOn) {
                        return $fail('Invalid free gift code.');
                    }

                    // Check if the product is linked to the free gift
                    if (!$addOn->addOnProducts()->where('product_id', $product->id)->exists()) {
                        return $fail('The selected free gift is not applicable for this product.');
                    }
                },
            ],
           'free_gift' => [
                'nullable',
                Rule::exists('product_free_gifts', 'code')->where('status', 10),
                function ($attribute, $value, $fail) use ($request) {
                    // Ensure product_code exists
                    if (!isset($request->product_code)) {
                        return $fail('The product_code is required for validation.');
                    }

                    // Get the product by code
                    $product = Product::where('code', $request->product_code)->where( 'status', 10 )->first();
                    if (!$product) {
                        return $fail('Invalid product code.');
                    }

                    // Get the free gift
                    $freeGift = ProductFreeGift::where('code', $value)->first();
                    if (!$freeGift) {
                        return $fail('Invalid free gift code.');
                    }

                    // Check if the product is linked to the free gift
                    if (!$freeGift->freeGiftProducts()->where('product_id', $product->id)->exists()) {
                        return $fail('The selected free gift is not applicable for this product.');
                    }
                },
            ],
        ]);

        $validator->validate();

        // create a tmp cart
        $voucher = Voucher::where( 'promo_code', $request->promo_code )->where( 'status', 10 )->first();
        $product = Product::where( 'code', $request->product_code )->first();
        $productVariant = ProductVariant::where( 'color', $request->color )->where( 'product_id', $product->id )->first();

        $userCart = Cart::updateOrCreate(
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
                $productPrice = $productVariant ? $productVariant->outright : $product->price;
                break;
        }

        $cartMeta = CartMeta::create( [
            'cart_id' => $userCart->id ,
            'product_id' => $product->id ,
            'product_variant_id' => $productVariant ? $productVariant->id : null,
            'user_id' => NULL ,
            'total_price' => $productPrice * $request->quantity,
            'discount' => NULL ,
            'status' => 10 ,
            'products' => NULL ,
            'additional_charges' => NULL ,
            'quantity' => $request->quantity,
        ] );
    
        $request->merge([
            'cart_item' => $cartMeta->id,
            'session_key' => $userCart->session_key,
        ]);

        if( $request->promo_code ){
            $test = self::validateVoucher($request);

            if ($test->getStatusCode() === 422) {
                return $test;
            }
        }

        DB::beginTransaction();
        try {
        
            $subtotal = 0;
            $orderPrice = 0;

            $voucher = Voucher::where( 'promo_code', $request->promo_code )->where( 'status', 10 )->first();

            $taxSettings = Option::getTaxesSettings();

            $guest = Guest::where('email', $request->email)->first() 
                ?? $userCart->guest 
                ?? Guest::create([
                'fullname' => $request->fullname,
                'email' => $request->email,
                'session_key' => $userCart->session_key,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'state' => $request->state,
                'postcode' => $request->postcode,
                'calling_code' => '+60',
                'status' => 10,
                'phone_number' => $request->phone_number,  
                'ip_address' => $request->ip(), // Get client's IP address
                'user_agent' => $request->header('User-Agent'), // Get client's user agent
                'last_visit' => now(), // Store current timestamp
                'country' => $request->country,
                'company_name' => $request->company_name,
            ] );

            $order = Order::create( [
                'user_id' => null,
                'product_id' => null,
                'total_price' => $orderPrice,
                'discount' => 0,
                'status' => 1,
                'reference' => Helper::generateOrderReference(),
                'tax' => 0,
                'remarks' => $request->remarks,
                'payment_plan' => $request->payment_plan,
                'payment_attempt' => 1,
                'guest_id' => $guest->id,
                'fullname' => $request->fullname,
                'email' => $request->email,
                'session_key' => $userCart->session_key,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'state' => $request->state,
                'postcode' => $request->postcode,
                'calling_code' => '+60',
                'status' => 10,
                'phone_number' => $request->phone_number,  
                'ip_address' => $request->ip(), // Get client's IP address
                'user_agent' => $request->header('User-Agent'), // Get client's user agent
                'last_visit' => now(), // Store current timestamp
                'country' => $request->country,
                'company_name' => $request->company_name,
                'add_on_id' => $request->add_on_id,
                'free_gift_id' => $request->free_gift_id,
            ] );

            if( $request->add_on ) {
                $addOn = ProductAddOn::where ( 'code', $request->add_on)->first();
                $orderPrice += $addOn->discount_price ? $addOn->discount_price : 0;
            }
    
            if( $request->free_gift ) {
                $freeGift = ProductFreeGift::where ( 'code', $request->free_gift)->first();
                $orderPrice += $freeGift->discount_price ? $freeGift->discount_price : 0;
            }

            $checkoutCart = CartMeta::find( $request->cart_item );

            $orderMeta = OrderMeta::create( [
                'order_id' => $order->id,
                'product_id' => $checkoutCart->product->id,
                'product_variant_id' => $checkoutCart->productVariant ? $checkoutCart->productVariant->id : null,
                'total_price' =>  $checkoutCart->quantity * $checkoutCart->product->price,
                'quantity' => $checkoutCart->quantity,
            ] );

            $orderPrice += $orderMeta->total_price;

            $checkoutCart->status = 20;
            $checkoutCart->save();

            $order->subtotal = $orderPrice;

            if( $request->promo_code || $userCart->voucher_id ){

                if( $request->promo_code ) {
                    $voucher = Voucher::where( 'id', $request->promo_code )
                    ->orWhere('promo_code', $request->promo_code)->first();
                }else if( $userCart->voucher_id ) {
                    $voucher = Voucher::where( 'id', $userCart->voucher_id )->first();
                }

                if ( $voucher->discount_type == 3 ) {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $userCart->cartMetas->whereIn( 'product_id', $adjustment->buy_products )->count();

                    if ( $x >= $adjustment->buy_quantity ) {
                        $getProductMeta = $userCart->cartMetas
                        ->where('product_id', $adjustment->get_product)
                        ->sortBy('total_price')
                        ->first();                    

                        if ($getProductMeta) {

                            $discount = 0;
                            $discount += $getProductMeta->product->price;

                            $orderPrice -= Helper::numberFormatV2($discount,2,false,true);
                            $order->discount = Helper::numberFormatV2($discount,2,false,true);
                            $getProductMeta->total_price = 0 + $getProductMeta->additional_charges;
                            $getProductMeta->save();
                        }
                    }

                } else if ( $voucher->discount_type == 2 ) {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $orderMeta->total_price;
                    if ( $x >= $adjustment->buy_quantity ) {
                        $orderPrice -= $adjustment->discount_quantity;
                        $order->discount = Helper::numberFormatV2($adjustment->discount_quantity,2,false,true);
                    }
        
                } else {

                    $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );
        
                    $x = $orderMeta->total_price;
                    if ( $x >= $adjustment->buy_quantity ) {
                        $order->discount = Helper::numberFormatV2(( $orderPrice * $adjustment->discount_quantity / 100 ),2,false,true);
                        $orderPrice = $orderPrice - ( $orderPrice * $adjustment->discount_quantity / 100 );
                    }
                }

                $order->voucher_id = $voucher->id;
                
                VoucherUsage::create( [
                    'user_id' => null,
                    'order_id' => $order->id,
                    'voucher_id' => $voucher->id,
                    'status' => 10
                ] );

            }

            $order->load( ['orderMetas','addon', 'freeGift'] );

            $order->total_price = Helper::numberFormatV2($orderPrice,2,false,true);
            $order->tax = $taxSettings ? (Helper::numberFormatV2(($taxSettings->option_value/100),2) * Helper::numberFormatV2($order->total_price,2)) : 0;
            $order->total_price += Helper::numberFormatV2($order->tax,2,false,true);

            if (!$userCart->cartMetas->contains('status', 10)) {
                $userCart->status = 20;
                $userCart->save();
            }
            
            $userCart->save();

            if( $request->product_code == 'TEST' ) {
                $merchantKey = config('services.ipay88.merchant_key');
                $merchantCode = config('services.ipay88.merchant_code');
    
                $request = new \IPay88\Payment\Request( $merchantKey );
                $order_amount = number_format(1, 2, '.', '');
                $data = array(
                    'merchantCode' => $request->setMerchantCode( $merchantCode ),
                    'paymentId' =>  '',
                    'refNo' => $request->setRefNo( $order->reference ),
                    'amount' => $order_amount,
                    'currency' => $request->setCurrency( 'MYR' ),
                    'prodDesc' => $request->setProdDesc( 'Testing' ),
                    'userName' => $request->setUserName( $order->fullname ? $order->fullname : 'intrix_guest' ),
                    'userEmail' => $request->setUserEmail( $order->email ? $order->email : 'intrixguest@mail.com' ),
                    'userContact' => $request->setUserContact( $order->phone_number ? $order->phone_number : '123123123' ),
                    'remark' => $request->setRemark( 'test' ),
                    'lang' => $request->setLang( 'UTF-8' ),
                    'signature' => hash('sha256', $merchantKey.$merchantCode.$order->reference.strtr( $order_amount, array( '.' => '', ',' => '' ) ).'MYR' ),
                    'responseUrl'   => $request->setResponseUrl(config('services.ipay88.staging_callback_url')),
                    'backendUrl'    => $request->setBackendUrl(config('services.ipay88.staging_callback_url')),
                );

                $paymentUrl = route('payment.show', ['payment_data' => $data]);

                $orderTransaction = OrderTransaction::create( [
                    'order_id' => $order->id,
                    'checkout_id' => null,
                    'checkout_url' => null,
                    'payment_url' => $paymentUrl,
                    'transaction_id' => null,
                    'layout_version' => 'v1',
                    'redirect_url' => null,
                    'notify_url' => null,
                    'order_no' => $order->reference . '-' . $order->payment_attempt,
                    'order_title' => $order->reference,
                    'order_detail' => $order->reference,
                    'amount' => $order->total_price,
                    'currency' => 'MYR',
                    'transaction_type' => 1,
                    'status' => 10,
                ] );
    
                $order->payment_url = $paymentUrl;
                $order->order_transaction_id = $orderTransaction->id;
                $order->status = 2;
                $order->save();
    
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'payment_url' => $paymentUrl
                ]);
            }

            $order->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        $orderMetas = $order->orderMetas->map(function ($meta) {
            return [
                'id' => $meta->id,
                'subtotal' => $meta->total_price,
                'product' => $meta->product->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('image_path', $meta->product->image_path),
                'quantity' => $meta->quantity,
                'color' => $meta->productVariant ? $meta->productVariant->title : null,
                'color_code' => $meta->productVariant ? intval( $meta->productVariant->color ): null,
                'payment_plan' => $meta->payment_plan,
            ];
        });

        return response()->json( [
            'message' => '',
            'message_key' => $order->userBundle ? 'bundle redeemed success' : 'create_order_success',
            'payment_url' => $order->payment_url,
            'sesion_key' => $order->session_key,
            'order_id' => $order->id,
            'total' => Helper::numberFormatV2($order->total_price , 2 ,true),
            'order_metas' => $orderMetas,
            'voucher' => $order->voucher ? $order->voucher->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
        ] );
    }

    public static function validatePromoCode($request)
    {
        // Validate request input
        $validator = Validator::make($request->all(), [
            'promo_code' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = \DB::table('vouchers')
                        ->where('promo_code', $value)
                        ->orWhere('id', $value)
                        ->exists();
    
                    if (!$exists) {
                        $fail(__('The :attribute promo_code exist in either the promo_code or id column.'));
                    }
                },
            ],
            'quantity' => ['numeric', 'min:1'],
            'product_code' => ['required', 'exists:products,code'],
            'color' => ['required', 'exists:product_variants,color'],
            'payment_plan' => ['nullable', 'in:1,2,3'],
        ]);
    
        $validator->validate();
    
        // Fetch product and variant details
        $product = Product::where('code', $request->product_code)->first();
        $productVariant = ProductVariant::where('color', $request->color)
            ->where('product_id', $product->id)
            ->first();
    
        // Determine product price based on payment plan
        $productPrice = $product->price;
        switch ($request->payment_plan) {
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
    
        // Calculate subtotal before discount
        $subtotal = $productPrice * $request->quantity;
        $discountAmount = 0;
    
        if ($request->promo_code) {
            // Validate voucher
            $voucher = Voucher::where('status', 10)
            ->where(fn($query) => 
                $query->where('id', $request->promo_code)
                      ->orWhere('promo_code', $request->promo_code)
            )
            ->first();
        
            if (!$voucher) {
                $errorKey = 'voucher_not_found';
            } elseif ($voucher->start_date && $voucher->start_date > now()) {
                $errorKey = 'voucher_not_started_x';
                $errorParams = ['time' => $voucher->start_date->format('d M Y, H:i A')];
            } elseif ($voucher->expired_date && $voucher->expired_date < now()) {
                $errorKey = 'voucher_expired';
            } elseif ($voucher->total_claimable <= 0) {
                $errorKey = 'voucher_fully_claimed';
            }
            
            if (isset($errorKey)) {
                return response()->json([
                    'message_key' => $errorKey,
                    'message' => __('voucher.' . $errorKey, $errorParams ?? []),
                    'errors' => [
                        'voucher' => [__('voucher.' . $errorKey, $errorParams ?? [])]
                    ]
                ], 422);
            }
    
            // Apply discount logic
            $adjustment = json_decode($voucher->buy_x_get_y_adjustment, true);
            if ($voucher->discount_type == 3) {
                // Buy X Get Y logic
                $buyQuantity = $adjustment['buy_quantity'] ?? 0;
                $getProductId = $adjustment['get_product'] ?? null;
    
                if ($request->quantity >= $buyQuantity) {
                    $getProduct = Product::find($getProductId);
                    if ($getProduct) {
                        $discountAmount = $getProduct->price;
                    }
                }else {
                    return response()->json([
                        'message_key' => 'voucher_not_available',
                        'message' => "Minimum spend not reach, Minimum spend required: RM" . $buyQuantity,
                        'errors' => [
                            'voucher' => [
                               "Minimum spend not reach, Minimum spend required: RM" . $buyQuantity
                            ]
                        ]
                    ], 422);
                }
            } elseif ($voucher->discount_type == 2) {
                // Fixed discount
                if ($subtotal >= ($adjustment['buy_quantity'] ?? 0)) {
                    $discountAmount = $adjustment['discount_quantity'] ?? 0;
                }else {
                    return response()->json([
                        'message_key' => 'voucher_not_available',
                        'message' => "Minimum spend not reach, Minimum spend required: RM" . $adjustment['buy_quantity'],
                        'errors' => [
                            'voucher' => [
                               "Minimum spend not reach, Minimum spend required: RM" . $adjustment['buy_quantity']
                            ]
                        ]
                    ], 422);
                }
            } else {
                // Percentage discount
                $percentage = $adjustment['discount_quantity'] ?? 0;
                if ($subtotal >= ($adjustment['buy_quantity'] ?? 0)) {
                    $discountAmount = ($subtotal * $percentage) / 100;
                }else {
                    return response()->json([
                        'message_key' => 'voucher_not_available',
                        'message' => "Minimum spend not reach, Minimum spend required: RM" . $adjustment['buy_quantity'],
                        'errors' => [
                            'voucher' => [
                               "Minimum spend not reach, Minimum spend required: RM" . $adjustment['buy_quantity']
                            ]
                        ]
                    ], 422);
                }
            }
        }
    
        // Final price after discount
        $finalPrice = max(0, $subtotal - $discountAmount);
    
        return response()->json([
            'message' => 'Price calculated successfully',
            'subtotal' => number_format($subtotal, 2),
            'discount' => number_format($discountAmount, 2),
            'final_price' => number_format($finalPrice, 2),
        ]);
    }

    public static function scannedOrder( $request ) {

        DB::beginTransaction();

        try {

            $updateOrder = Order::with( [
                'orderMetas','vendingMachine','user'
            ] )->where( 'reference', $request->reference )
            ->whereNotIn('status', [10, 20])->first();

            if( !$updateOrder ){
                return response()->json( [
                    'errors' => [
                        'message' => 'Order Not found',
                        'message_key' => 'scan order failed',
                    ]
                ], 500 );
            }

            if( $updateOrder ){
                if( $updateOrder->status == 1 ){
                    return response()->json( [
                        'message' => 'Unpaid Order',
                        'message_key' => 'scan order failed',
                    ], 500 );
                }
                $updateOrder->status = 10;
                $updateOrder->save();
                DB::commit();
                return response()->json( [
                    
                    'errors' => [
                        'message' => 'Order Pickep Up',
                        'message_key' => 'scan order success',
                    ]
                ] );
            }

            $updateOrder = $updateOrder->paginate(10);
    
            // Modify each order and its related data
            $updateOrder->getCollection()->transform(function ($order) {
                $order->vendingMachine->makeHidden(['created_at', 'updated_at', 'status'])
                    ->setAttribute('operational_hour', $order->vendingMachine->operational_hour)
                    ->setAttribute('image_path', $order->vendingMachine->image_path);
        
                $orderMetas = $order->orderMetas->map(function ($meta) {
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
        
                $order->orderMetas = $orderMetas;

                return $order;
            });
        
            // Return the paginated response
            return response()->json([
                'message' => '',
                'message_key' => 'get_order_success',
                'orders' => $updateOrder,
            ]);

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_froyo_failed',
            ], 500 );
        }
    }

    public static function validateVoucher( $request ){
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
        
        // check is user able to claim this
        // $userVoucher = UserVoucher::where( 'voucher_id', $voucher->id )->where( 'user_id', $user->id )->where('status',10)->first();
        // if(!$userVoucher){
        //     $userPoints = $user->wallets->where( 'type', 2 )->first();

        //     if ( $userPoints->balance < $voucher->points_required ) {
    
        //         return response()->json( [
        //             'required_amount' => $voucher->points_required,
        //             'message' => 'Mininum of ' . $voucher->points_required . ' points is required to claim this voucher',
        //             'errors' => 'voucher',
        //         ], 422 );
    
        //     }
        // }

        $query = CartMeta::where('status',10);

        if ($request->has('cart_item')) {
            $query->where('id', $request->cart_item);
        }
    
        $cart = $query->first();

        if ( $voucher->discount_type == 3 ) {

            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );

            $x = $cart->cartMetas->whereIn( 'product_id', $adjustment->buy_products )->count();

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
            
            $y = $cart->cartMetas->whereIn( 'product_id', $adjustment->get_product )->count();

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
                    'message' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id', $adjustment->buy_products[0] )->value( 'title' ) ] ),
                    'message_key' => 'voucher.min_quantity_of_y',
                        'errors' => [
                            'voucher' => __( 'voucher.min_quantity_of_y', [ 'title' => $adjustment->get_quantity . ' ' . Product::where( 'id', $adjustment->buy_products[0] )->value( 'title' ) ] )
                        ]
                ], 422 );
            }

        } else {

            $adjustment = json_decode( $voucher->buy_x_get_y_adjustment );

            if ( $cart->total_price < $adjustment->buy_quantity ) {
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

    public static function retryPayment( $request ) {

        $validator = Validator::make($request->all(), [
            'order_id' => ['required', 'exists:orders,id'],
        ]);
        
        $validator->validate();

        $order = Order::where('id', $request->order_id)
            ->where('status', '!=', 3)
            ->where('user_id', auth()->user()->id )
            ->first();

        if (!$order) {
            return response()->json([
                'message' => '',
                'message_key' => 'order_not_available',
                'errors' => [
                    'order' => 'order not available'
                ]
            ], 422);
        }

        DB::beginTransaction();
        try {
                
            $data = [
                'TransactionType' => 'SALE',
                'PymtMethod' => 'ANY',
                'ServiceID' => config('services.eghl.merchant_id'),
                'PaymentID' => $order->reference . '-' . $order->payment_attempt,
                'OrderNumber' => $order->reference,
                'PaymentDesc' => $order->reference,
                'MerchantName' => 'Yobe Froyo',
                'MerchantReturnURL' => config('services.eghl.staging_callabck_url'),
                'MerchantApprovalURL' => config('services.eghl.staging_success_url'),
                'MerchantUnApprovalURL' => config('services.eghl.staging_failed_url'),
                'Amount' => Helper::numberFormatV2($order->total_price, 2),
                'CurrencyCode' => 'MYR',
                'CustIP' => request()->ip(),
                'CustName' => $order->user->username ?? 'Yobe Guest',
                'HashValue' => '',
                'CustEmail' => $order->user->email ?? 'yobeguest@gmail.com',
                'CustPhone' => $order->user->phone_number,
                'MerchantTermsURL' => null,
                'LanguageCode' => 'en',
                'PageTimeout' => '780',
            ];

            $data['HashValue'] = Helper::generatePaymentHash($data);
            $url2 = config('services.eghl.test_url') . '?' . http_build_query($data);
            
            $orderTransaction = OrderTransaction::create( [
                'order_id' => $order->id,
                'checkout_id' => null,
                'checkout_url' => null,
                'payment_url' => $url2,
                'transaction_id' => null,
                'layout_version' => 'v1',
                'redirect_url' => null,
                'notify_url' => null,
                'order_no' => $order->reference . '-' . $order->payment_attempt,
                'order_title' => $order->reference,
                'order_detail' => $order->reference,
                'amount' => $order->total_price,
                'currency' => 'MYR',
                'transaction_type' => 1,
                'status' => 10,
            ] );

            $order->payment_url = $url2;
            $order->order_transaction_id = $orderTransaction->id;
            $order->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        $orderMetas = $order->orderMetas->map(function ($meta) {
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
            'message_key' => 'retry_payment_inititate',
            'payment_url' => $order->payment_url,
            'sesion_key' => $order->session_key,
            'order_id' => $order->id,
            'vending_machine' => $order->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $order->vendingMachine->operational_hour),
            'total' => Helper::numberFormatV2($order->total_price , 2 ,true),
            'order_metas' => $orderMetas
        ] );
    }

    public static function showPaymentPage( $request ) {

        $validator = Validator::make($request->all(), [
            'payment_data.refNo' => [
                'required',
                Rule::exists('orders', 'reference')->whereIn('status', [ 1,2,3 ] ),
            ],
        ]);
        
        $validator->validate();

        $data = $request->all();

        return view('admin.order.payment_redirect', compact('data'));
    }
}
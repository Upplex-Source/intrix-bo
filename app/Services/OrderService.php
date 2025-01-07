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

use App\Models\{
    FileManager,
    Option,
    Order,
    OrderMeta,
    Product,
    Froyo,
    Syrup,
    Topping,
    Cart,
    CartMeta,
};

use Helper;

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
                'color' => '#aad418',
            ] );
        }

        return response()->json( $currentOrders );
    }

    public static function allOrders( $request, $export = false ) {

        $order = Order::with( [
            'vendingMachine',
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
            'orderMetas','vendingMachine','user'
        ] )->find( $request->id );

        $order->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $order->vendingMachine->operational_hour)->setAttribute('image_path', $order->vendingMachine->image_path);

        $orderMetas = $order->orderMetas->map(function ($meta) {
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
            'vending_machine' => [ 'required', 'exists:vending_machines,id'  ],
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

            $createOrder->total_price = $orderPrice;
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
            'id' => [ 'required', 'exists:orders,id'  ],
            'user' => [ 'required', 'exists:users,id'  ],
            'vending_machine' => [ 'required', 'exists:vending_machines,id'  ],
            'products' => [ 'nullable' ],
            'products.*.productId' => [ 'nullable', 'exists:products,id' ],
            'products.*.froyo' => [ 'nullable', 'exists:froyos,id' ],
            'products.*.syrup' => [ 'nullable', 'exists:syrups,id' ],
            'products.*.topping' => [ 'nullable', 'exists:toppings,id' ],
        ] );

        $attributeName = [
            'reference' => __( 'order.reference' ),
            'farm' => __( 'order.farm' ),
            'buyer' => __( 'order.buyer' ),
            'grade' => __( 'order.grade' ),
            'weight' => __( 'order.weight' ),
            'rate' => __( 'order.rate' ),
            'total' => __( 'order.total' ),
            // 'subtotal' => __( 'order.subtotal' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $orderPrice = 0;

            $updateOrder = Order::find( $request->id );
            $updateOrder->user_id = $request->user;
            $updateOrder->vending_machine_id = $request->vending_machine;
            $updateOrder->save();

            OrderMeta::where( 'order_id', $updateOrder->id )->delete();

            foreach ( $request->products as $product ) {

                $froyos = $product['froyo'];
                $froyoCount = count($froyos);
                $syrups = $product['syrup'];
                $syrupCount = count($syrups);
                $toppings = $product['topping'];
                $toppingCount = count($toppings);
                $product = Product::find($product['productId']);

                $orderMeta = OrderMeta::create( [
                    'order_id' => $updateOrder->id,
                    'product_id' => $product->id,
                    'product_bundle_id' => null,
                    'froyos' =>  json_encode($froyos),
                    'syrups' =>  json_encode($syrups),
                    'toppings' =>  json_encode($toppings),
                ] );

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

            $updateOrder->total_price = $orderPrice;
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
                'message_key' => 'create_froyo_failed',
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
        $query = Order::where('user_id', $user->id)
            ->with(['orderMetas', 'vendingMachine'])
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
    
        // Use paginate instead of get
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $userOrders = $query->paginate($perPage);
    
        // Modify each order and its related data
        $userOrders->getCollection()->transform(function ($order) {
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
            $order->qr_code = $order->status != 20 ? self::generateQrCode($order) : null;
            return $order;
        });
    
        // Return the paginated response
        return response()->json([
            'message' => '',
            'message_key' => 'get_order_success',
            'orders' => $userOrders,
        ]);
    }
    

    public static function checkout( $request ) {

        $validator = Validator::make($request->all(), [
            'cart' => ['required', 'exists:carts,id'],
        ]);

        $user = auth()->user();

        $query = Cart::where('user_id', $user->id)
        ->where('id', $request->cart)
        ->where('status',10);
    
        $userCart = $query->first();
        
        if (!$userCart) {
            return response()->json([
                'message' => '',
                'message_key' => 'cart_is_empty',
                'carts' => []
            ]);
        }
        
        $validator->validate();

        DB::beginTransaction();
        try {
        
            $orderPrice = 0;

            $order = Order::create( [
                'user_id' => auth()->user()->id,
                'product_id' => null,
                'product_bundle_id' => null,
                'outlet_id' => null,
                'vending_machine_id' => $userCart->vending_machine_id,
                'total_price' => $orderPrice,
                'discount' => 0,
                'status' => 1,
                'reference' => Helper::generateOrderReference(),
            ] );

            foreach ( $userCart->cartMetas as $cartProduct ) {

                $froyos = json_decode($cartProduct->froyos,true);
                $froyoCount = count($froyos);
                $syrups = json_decode($cartProduct->syrups,true);
                $syrupCount = count($syrups);
                $toppings = json_decode($cartProduct->toppings,true);
                $toppingCount = count($toppings);
                $product = Product::find($cartProduct->product_id);
                $metaPrice = 0;

                $orderMeta = OrderMeta::create( [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_bundle_id' => null,
                    'froyos' =>  $cartProduct->froyos,
                    'syrups' =>  $cartProduct->syrups,
                    'toppings' =>  $cartProduct->toppings,
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

                $cartProduct->status = 20;
                $cartProduct->save();
            }

            $order->total_price = $orderPrice;
            $order->save();

            $userCart->status = 20;
            $userCart->save();
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
            'message_key' => 'create_order_success',
            'payment_url' => route('payment.testSuccess'),
            'sesion_key' => $order->session_key,
            'order_id' => $order->id,
            'vending_machine' => $order->vendingMachine->makeHidden( ['created_at','updated_at'.'status'] )->setAttribute('operational_hour', $order->vendingMachine->operational_hour),
            'total' => $order->total_price,
            'order_metas' => $orderMetas
        ] );
    }

    public static function scannedOrder( $request ) {

        DB::beginTransaction();

        try {

            $updateOrder = Order::with( [
                'orderMetas','vendingMachine','user'
            ] )->where( 'reference', $request->reference )
            ->whereNotIn('status', [10, 20])->first();
            $updateOrder->status = 10;

            $updateOrder->save();
            DB::commit();

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
}
<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    ApiLog,
    InsuranceQuote,
    InsuranceQuoteOption,
    EghlTransaction,
    User,
    UserTopup,
    UserWallet,
    AdministratorNotification,
    OrderTransaction,
    Order,
    TopupRecord,
    WalletTransaction,
    Wallet,
    UserBundle,
    ProductBundle,
    UserBundleTransaction,
    Option,
    UserNotification,
    UserNotificationUser,
    UserNotificationSeen,
};

use App\Jobs\{
    GenerateInsuranceReceipt
};

use App\Services\{
    AffiliateTransactionService,
};

use Eghl\SDK\Eghl;
use Eghl\SDK\Request\WebPayment;

use Helper;

use Carbon\Carbon;

class Ipay88Service {

    public static function init( $request ) {

        if ( $request->scope == 'topup' ) {
            $model = 'App\Models\UserTopup';
        } else if( $request->scope == 'concierge' ) {
            $model = 'App\Models\UserConcierge';
        } else {
            $model = 'App\Models\InsuranceQuote';
        }
        $scope = $request->scope;

        $user = User::find( $request->user_id );

        if( !$user ) {
            echo 'a';
            return response()->json( [ 'message' => __( 'api.wrong_user' ) ], 400 );
        }

        $order = $model::where( 'reference', $request->reference )->first();

        if( !$order ) {
            echo 'b';
            return response()->json( [ 'message' => __( 'api.wrong_order_reference' ) ], 400 );
        }

        if( $order->user_id != $user->id ) {
            echo 'c';
            return response()->json( [ 'message' => __( 'api.wrong_order_reference_and_user' ) ], 400 );
        }

        $rm = new Eghl( [
            'clientId' => config( 'services.rm.client_id' ),
            'clientSecret' => config( 'services.rm.client_secret' ),
            'privateKey' => config( 'services.rm.private_key' ),
            'isSandbox' => config( 'services.rm.is_sandbox' ),
        ] );

        try {

            if ( $request->scope == 'topup' ) {
                $orderAmount = $order->amount * 100;
            } else if( $request->scope == 'concierge' ) {
                $orderAmount = $order->amount * 100;
            } else {
                $orderAmount = $order->actual_amount * 100;
            }

            $wp = new WebPayment;
            $wp->order->id = $order->reference;
            $wp->order->title = $request->scope == 'topup' ? 'MeCar+ Topup' : 'MeCar+';
            $wp->order->currencyType = 'MYR';
            $wp->order->amount = intval( $orderAmount );
            $wp->order->detail = '';
            $wp->order->additionalData = '';
            $wp->storeId = config( 'services.rm.store_id' );
            $wp->redirectUrl = config( 'services.rm.redirect_url' );
            $wp->notifyUrl = config( 'services.rm.notify_url' );
            $wp->layoutVersion = 'v3';

            $response = $rm->payment->createWebPayment( $wp );

            // echo $response->url . '<br>';

            $scope = '';

            switch ( $request->scope ) {
                case 'topup':
                    $scope = 1;
                    break;
                case 'concierge':
                    $scope = 3;
                    break;
                default:
                    $scope = 2;
                    break;
            }

            EghlTransaction::create( [
                'store_id' => $wp->storeId,
                'checkout_id' => $response->checkoutId,
                'checkout_url' => $response->url,
                'layout_version' => $wp->layoutVersion,
                'redirect_url' => $wp->redirectUrl,
                'notify_url' => $wp->notifyUrl,
                'order_no' => $wp->order->id,
                'order_title' => $wp->order->title,
                'transaction_type' => $scope,
                'amount' => $wp->order->amount / 100,
            ] );

            return redirect()->away( $response->url );

        } catch ( \Throwable $th ) {

            echo $th->getMessage() . ' in line: ' . $th->getLine();
        }
    }

    public static function notify( $request ) {

        ApiLog::create( [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'raw_response' => json_encode( $request->all() ),
        ] );
    }

    public static function query( $request ) {

        $rm = new Eghl( [
            'clientId' => config( 'services.rm.client_id' ),
            'clientSecret' => config( 'services.rm.client_secret' ),
            'privateKey' => config( 'services.rm.private_key' ),
            'isSandbox' => config( 'services.rm.is_sandbox' ),
        ] );

        try {

            $orderId = $request->order_id;
            $response = $rm->payment->findByOrderId( $orderId );

            echo '<pre>';
            var_dump( $response );
            echo '</pre>';

        } catch ( \Throwable $th ) {

            echo $th->getMessage() . ' in line: ' . $th->getLine();       
        }
    }

    public static function callback( $request ) {

        $url = $request->fullUrl();
        $baseUrl = parse_url($url, PHP_URL_SCHEME) . "://" . parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);

        ApiLog::create( [
            'url' => $baseUrl,
            'method' => $request->method(),
            'raw_response' => json_encode( $request->all() ),
        ] );

        $order = Order::where( 'reference', $request->RefNo )->first();
        $orderStatus = false;

        if( $order ){
            $orderTransaction = OrderTransaction::where( 'order_id', $order->id )->first(); 
            $returnStatus = intval( $request->Status );
            $order->status = $returnStatus == 0 ? 20 : 10;

            if( $returnStatus == 1 ){
                $orderStatus = true;
                $orderTransaction->status = 11;
            }

            if( $returnStatus != 1 ){
                $order->payment_attempt += 1;
                $orderTransaction->status = 20;

                return redirect()->away( config( 'services.frontend.url' ) . 'order/' . $order->reference . '/failed' . '?' . http_build_query([
                    'order_id'     => null,
                    'payment_url'  => null,
                    'reference'    => null,
                    'total_price'  => null,
                    'message'      => 'Oops something when wrong, Please try ordering Again.',
                ]) );

                return response()->json( [
                    'message' => 'Oops something when wrong, Please try ordering Again.',
                    'message_key' => 'order_failed,',
                    'data' => [
                        'status' => $orderStatus
                    ],
                ] );

            }
            
            $order->payment_method = 2;
            $order->save();
            $orderTransaction->save();

            $addOnMetas = $order->addOns->map(function ($meta) {
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
           
            if($order->freeGift){
                $order->freeGift->makeHidden( [ 'created_at', 'updated_at' ] )
                ->append([ 'image_path' ]);
    
                $order->freeGift->subtotal = $order->freeGift->discount_price;
    
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
                    'product_image' => ( $meta->productVariant && $meta->productVariant->image ) ? $meta->productVariant->image_path : $meta->product->image_path,
                ];
            });

            return redirect()->away( config( 'services.frontend.url' ) . 'order/' . $order->reference . '/success' . '?' . http_build_query([
                'order_id'     => $order->id,
                'payment_url'  => $order->payment_url,
                'reference'    => $order->reference,
                'total_price'  => Helper::numberFormatV2($order->total_price, 2, true),
                'message'      => 'Order created successfully. Please proceed with the payment.',
            ]) );
            
            return response()->json( [
                'message' => '',
                'message_key' => 'create_order_success',
                'payment_url' => $order->payment_url,
                'reference' => $order->reference,
                'order_id' => $order->id,
                'status' => $order->order_status_label,
                'add_on_metas' => $addOnMetas,
                'free_gift' => $order->freeGift,
                'total_price' => Helper::numberFormatV2($order->total_price , 2 ,true),
                'order_metas' => $orderMetas,
                'voucher' => $order->voucher ? $order->voucher->makeHidden( ['description', 'created_at', 'updated_at' ] ) : null,
                'subtotal' => Helper::numberFormatV2($order->subtotal, 2,false, true),
                'discount' =>  Helper::numberFormatV2($order->discount, 2,false, true),
                'tax' =>  Helper::numberFormatV2($order->tax, 2,false, true),
                'tranction_date' => $order->created_at->format('Y-m-d'),
                'payment_method' => 'Credit Card',
                'shipment_method' => 'Free Shipping ( 3 - 7 days )',
                'delivery_address' => $order->address_1 . $order->address_2 . $order->city . $order->postcode . $order->state,
                'contact_name' => $order->fullname ? $order->fullname : $order->company_name,
                'phone_number' => $order->phone_number,
                'email' => $order->email,
            ] );
        }

        return response()->json( [
            'message' => 'Order Not Found',
            'message_key' => 'order_not_found',
            'data' => [
                'status' => false
            ],
        ] );
    }

    public static function processTransaction( $orderId, $response ) {

        $transaction = EghlTransaction::where( 'order_no', $orderId )->first();

        if ( $transaction->status == 1 ) {

            $transaction->transaction_id = $response->transactionId;
            $transaction->status = 10;
            $transaction->save();

            if ( $transaction->transaction_type == 1 ) {

                $updateUserTopup = UserTopup::where( 'reference', $orderId )->first();
                $updateUserTopup->internal_status = 'success';
                $updateUserTopup->transaction_no = $response->transactionId;
                $updateUserTopup->status = 10;
                $updateUserTopup->save();

                $userWallet = UserWallet::lockForUpdate()
                    ->where( 'user_id', $updateUserTopup->user_id )
                    ->where( 'type', 1 )
                    ->first();

                WalletService::transact( $userWallet, [
                    'amount' => $transaction->amount,
                    'remark' => '##{rm_topup}##',
                    'type' => $userWallet->type,
                    'transaction_type' => 1,
                ] );

            } else if ( $transaction->transaction_type == 3 ) {

                $updateUserConcierge = UserConcierge::where( 'reference', $orderId )->first();
                $updateUserConcierge->internal_payment_status = 'paid';
                $updateUserConcierge->payment_status = 10;
                $updateUserConcierge->status = 10;
                $updateUserConcierge->save();

                $meta['url'] = '/jpj-services';
                $meta['content_id'] = $updateUserConcierge->encrypted_id;

                AdministratorNotification::create([
                    'title' => 'New Concierge Payment completed',
                    'content' => 'Please check the concierge',
                    'system_title' => 'New concierge payment completed',
                    'system_content' => 'New concierge payment completed',
                    'meta_data' => json_encode( $meta ),
                    'type' => 2,
                    'role_id' => 1,
                ]);

            } else {

                $updateInsuranceQuote = InsuranceQuote::where( 'reference', $orderId )->first();
                $updateInsuranceQuote->internal_status = 'paid';
                $updateInsuranceQuote->transaction_no = $response->transactionId;
                $updateInsuranceQuote->status = 10;
                $updateInsuranceQuote->paid_at = date( 'Y-m-d H:i:s' );
                $updateInsuranceQuote->save();

                $updateOption = InsuranceQuoteOption::find( $updateInsuranceQuote->insurance_quote_option_id );
                $updateOption->status = 10;
                $updateOption->save();

                $meta['url'] = '/insurances';
                $meta['content_id'] = $updateInsuranceQuote->encrypted_id;

                AdministratorNotification::create([
                    'title' => 'New Insurance Payment completed',
                    'content' => 'Please check the insurance',
                    'system_title' => 'Insurance payment completed',
                    'system_content' => 'Insurance payment completed',
                    'meta_data' => json_encode( $meta ),
                    'type' => 2,
                    'role_id' => 1,
                ]);

                GenerateInsuranceReceipt::dispatch( $updateInsuranceQuote->id );
                
                // Calculate affiliate bonus
                if( $updateInsuranceQuote->user->referral ) {

                    $affiliate = $updateInsuranceQuote->user->referral->affiliate;

                    if ( $affiliate && $affiliate->status == 10 && $updateInsuranceQuote->status == 10 ) {
                        AffiliateTransactionService::caculateAffiliateBonus( $updateInsuranceQuote, $affiliate, 2 );
                    }
                }
                
            }
        }
    }

    public static function allEghlTransactions( $request ) {

        $eghlTransaction = EghlTransaction::select( 'revenue_monster_transactions.*' );

        $filterObject = self::filter( $request, $eghlTransaction );
        $eghlTransaction = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $eghlTransaction->orderBy( 'created_at', $dir );
                    break;
                case 2:
                    $eghlTransaction->orderBy( 'store_id', $dir );
                    break;
                case 3:
                    $eghlTransaction->orderBy( 'checkout_id', $dir );
                    break;
                case 4:
                    $eghlTransaction->orderBy( 'transaction_id', $dir );
                    break;
                case 5:
                    $eghlTransaction->orderBy( 'order_no', $dir );
                    break;
                case 6:
                    $eghlTransaction->orderBy( 'order_title', $dir );
                    break;
                case 7:
                    $eghlTransaction->orderBy( 'currency', $dir );
                    break;
                case 8:
                    $eghlTransaction->orderBy( 'transaction_type', $dir );
                    break;
                case 9:
                    $eghlTransaction->orderBy( 'status', $dir );
                    break;
            }
        }

        $eghlTransactionCount = $eghlTransaction->count();

        $limit = $request->length;
        $offset = $request->start;

        $eghlTransactions = $eghlTransaction->skip( $offset )->take( $limit )->get();

        $subTotal = 0;

        if ( $eghlTransactions ) {
            $eghlTransactions->append( [
                'encrypted_id',
                'listing_amount',
            ] );

            foreach ( $eghlTransactions as $transaction ) {
                $subTotal += $transaction->amount;
            }
        }
        
        $eghlTransactionObject = EghlTransaction::select( DB::raw( 'COUNT(*) as total, SUM(amount) AS grand_total' ) )->first();
        $grandTotal = $eghlTransactionObject->grand_total;
        $totalRecord = $eghlTransactionObject->total;

        $data = [
            'revenue_monster_transactions' => $eghlTransactions,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $eghlTransactionCount : $totalRecord,
            'recordsTotal' => $totalRecord,
            'subTotal' => [
                Helper::numberFormat( $subTotal, 2 )
            ],
            'grandTotal' => [ 
                Helper::numberFormat( $grandTotal, 2 )
            ],
        ];
        
        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->store_id ) ) {
            $model->where( 'store_id', 'LIKE', "%$request->store_id%" );
            $filter = true;
        }

        if ( !empty( $request->checkout_id ) ) {
            $model->where( 'checkout_id', 'LIKE', "%$request->checkout_id%" );
            $filter = true;
        }

        if ( !empty( $request->transaction_id ) ) {
            $model->where( 'transaction_id', 'LIKE', "%$request->transaction_id%" );
            $filter = true;
        }

        if ( !empty( $request->order_no ) ) {
            $model->where( 'order_no', 'LIKE', "%$request->order_no%" );
            $filter = true;
        }

        if ( !empty( $request->order_title ) ) {
            $model->where( 'order_title', 'LIKE', "%$request->order_title%" );
            $filter = true;
        }

        if ( !empty( $request->currency ) ) {
            $model->where( 'currency', 'LIKE', "%$request->currency%" );
            $filter = true;
        }

        if ( !empty( $request->transaction_type ) ) {
            $model->where( 'transaction_type', $request->transaction_type );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

}
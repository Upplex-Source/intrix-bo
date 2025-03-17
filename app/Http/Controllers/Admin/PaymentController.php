<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    Ipay88Service,
};

use Helper;

class PaymentController extends Controller
{
    public function callback( Request $request ) {

        echo 'Close This';
    }

    public function initIpay88( Request $request ) {

        return Ipay88Service::init( $request );
    }

    public function notifyIpay88( Request $request ) {

        return Ipay88Service::notify( $request );
    }

    public function queryIpay88( Request $request ) {

        return Ipay88Service::query( $request );
    }

    public function callbackIpay88( Request $request ) {

        return Ipay88Service::callback( $request );
    }

    public function success( Request $request ) {

        return response()->json( [
            'message' => '',
            'message_key' => 'order_placed',
            'data' => [
                'status' => true
            ],
        ] );
    }

    public function failed( Request $request ) {

        return response()->json( [
            'message' => '',
            'message_key' => 'order_failed_you_may_retry_payment',
            'data' => [
                'status' => true
            ],
        ] );
    }
}

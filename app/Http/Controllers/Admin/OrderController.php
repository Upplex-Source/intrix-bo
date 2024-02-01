<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    OrderService,
    BookingService,
};

use Illuminate\Support\Facades\{
    DB,
};

class OrderController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.orders' );
        $this->data['content'] = 'admin.order.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.orders' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activate' ),
            '20' => __( 'datatables.suspend' ),
        ];
        $this->data['data']['company'] = [];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.orders' );
        $this->data['content'] = 'admin.order.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.orders' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['grade'] = [
            'A',
            'B',
            'C',
            'D',
        ];
        $this->data['data']['order_increment'] = rand(1000, 9999);

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.orders' );
        $this->data['content'] = 'admin.order.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.orders' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function salesReport( Request $request ) {

        $this->data['header']['title'] = __( 'template.orders' );
        $this->data['content'] = 'admin.order.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.orders' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allOrders( Request $request ) {
        return OrderService::allOrders( $request );
    }

    public function oneOrder( Request $request ) {
        return OrderService::oneOrder( $request );
    }

    public function createOrder( Request $request ) {
        return OrderService::createOrder( $request );
    }

    public function updateOrder( Request $request ) {
        return OrderService::updateOrder( $request );
    }

    public function allSalesReport( Request $request ) {
        return OrderService::allSalesReport( $request );
    }

    public function export( Request $request ) {
        return OrderService::exportOrders( $request );
    }

    public function updateOrderStatus( Request $request ) {
        return OrderService::updateOrderStatus( $request );
    }

}

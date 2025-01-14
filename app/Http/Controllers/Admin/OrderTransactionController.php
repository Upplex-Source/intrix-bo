<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    OrderTransactionService,
    BookingService,
};

use Illuminate\Support\Facades\{
    DB,
};

class OrderController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.order_transactions' );
        $this->data['content'] = 'admin.order_transaction.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.order_transactions' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '1' => __( 'datatables.order_placed' ),
            '2' => __( 'datatables.order_pending_payment' ),
            '3' => __( 'datatables.order_paid' ),
            '10' => __( 'datatables.order_completed' ),
            '20' => __( 'datatables.order_canceled' ),
        ];
        $this->data['data']['company'] = [];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.order_transactions' );
        $this->data['content'] = 'admin.order_transaction.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.order_transactions' ),
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

        $this->data['header']['title'] = __( 'template.order_transactions' );
        $this->data['content'] = 'admin.order_transaction.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.order_transactions' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function salesReport( Request $request ) {

        $this->data['header']['title'] = __( 'template.sales_report' );
        $this->data['content'] = 'admin.order_transaction.sales_report';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.order_transactions' ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['orders'] = OrderTransactionService::salesReport( $request );
        $this->data['data']['grades'] = [
            'A',
            'B',
            'C',
            'D',
        ];

        $this->data['data']['status'] = [
            '1' => __( 'datatables.order_placed' ),
            '2' => __( 'datatables.order_pending_payment' ),
            '3' => __( 'datatables.order_paid' ),
            '10' => __( 'datatables.order_completed' ),
            '20' => __( 'datatables.order_canceled' ),
        ];
        return view( 'admin.main' )->with( $this->data );
    }

    public function allOrderTransactions( Request $request ) {
        return OrderTransactionService::allOrders( $request );
    }

    public function oneOrderTransaction( Request $request ) {
        return OrderTransactionService::oneOrderTransaction( $request );
    }

    public function queryOrderTransaction( Request $request ) {
        return OrderTransactionService::queryOrderTransaction( $request );
    }

    public function updateOrderTransactionStatus( Request $request ) {
        return OrderTransactionService::updateOrderTransactionStatus( $request );
    }

}

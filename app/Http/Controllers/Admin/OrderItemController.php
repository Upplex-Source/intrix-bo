<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    OrderService,
    OrderItemService,
};

use Illuminate\Support\Facades\{
    DB,
};

class OrderItemController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.order_items' );
        $this->data['content'] = 'admin.order_item.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.order_items' ),
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

    public function allOrderItems( Request $request ) {
        return OrderItemService::allOrderItems( $request );
    }

    public function oneOrderItem( Request $request ) {
        return OrderItemService::oneOrderItem( $request );
    }

}

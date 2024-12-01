<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    DeliveryOrderService,
    WarehouseService,
};

use App\Models\{
    DeliveryOrder,
};

use Helper;

class DeliveryOrderController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.delivery_orders' );
        $this->data['content'] = 'admin.delivery_order.index';
        $this->data['breadcrumb'] = [
            [
            'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.delivery_orders' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
            '13' => __( 'datatables.convert_invoice' ),
            '14' => __( 'datatables.convert_delivery_order' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.delivery_orders' ) ) ] );
        $this->data['content'] = 'admin.delivery_order.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.delivery_order.index' ),
                'text' => __( 'template.delivery_orders' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.delivery_orders' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['tax_types'] = Helper::taxTypes();

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.delivery_orders' ) ) ] );
        $this->data['content'] = 'admin.delivery_order.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.delivery_order.index' ),
                'text' => __( 'template.delivery_orders' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.delivery_orders' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['tax_types'] = Helper::taxTypes();

        return view( 'admin.main' )->with( $this->data );
    }

    public function allDeliveryOrders( Request $request ) {

        return DeliveryOrderService::allDeliveryOrders( $request );
    }

    public function oneDeliveryOrder( Request $request ) {

        return DeliveryOrderService::oneDeliveryOrder( $request );
    }

    public function createDeliveryOrder( Request $request ) {

        return DeliveryOrderService::createDeliveryOrder( $request );
    }

    public function updateDeliveryOrder( Request $request ) {
        return DeliveryOrderService::updateDeliveryOrder( $request );
    }

    public function updateDeliveryOrderStatus( Request $request ) {

        return DeliveryOrderService::updateDeliveryOrderStatus( $request );
    }

    public function removeDeliveryOrderAttachment( Request $request ) {

        return DeliveryOrderService::removeDeliveryOrderAttachment( $request );
    }

    public function ckeUpload( Request $request ) {

        return DeliveryOrderService::ckeUpload( $request );
    }

    public function convertDeliveryOrder( Request $request ) {

        return DeliveryOrderService::convertDeliveryOrder( $request );
    }
}

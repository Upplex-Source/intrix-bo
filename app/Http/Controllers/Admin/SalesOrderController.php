<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    SalesOrderService,
    WarehouseService,
};

use App\Models\{
    SalesOrder,
};

use Helper;

class SalesOrderController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.sales_orders' );
        $this->data['content'] = 'admin.sales_order.index';
        $this->data['breadcrumb'] = [
            [
            'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.sales_orders' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.sales_orders' ) ) ] );
        $this->data['content'] = 'admin.sales_order.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.sales_order.index' ),
                'text' => __( 'template.sales_orders' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.sales_orders' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['tax_types'] = Helper::taxTypes();

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.sales_orders' ) ) ] );
        $this->data['content'] = 'admin.sales_order.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.sales_order.index' ),
                'text' => __( 'template.sales_orders' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.sales_orders' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['tax_types'] = Helper::taxTypes();

        return view( 'admin.main' )->with( $this->data );
    }

    public function allSalesOrders( Request $request ) {

        return SalesOrderService::allSalesOrders( $request );
    }

    public function oneSalesOrder( Request $request ) {

        return SalesOrderService::oneSalesOrder( $request );
    }

    public function createSalesOrder( Request $request ) {

        return SalesOrderService::createSalesOrder( $request );
    }

    public function updateSalesOrder( Request $request ) {
        return SalesOrderService::updateSalesOrder( $request );
    }

    public function updateSalesOrderStatus( Request $request ) {

        return SalesOrderService::updateSalesOrderStatus( $request );
    }

    public function removeSalesOrderAttachment( Request $request ) {

        return SalesOrderService::removeSalesOrderAttachment( $request );
    }

    public function ckeUpload( Request $request ) {

        return SalesOrderService::ckeUpload( $request );
    }

    public function convertInvoice( Request $request ) {

        return SalesOrderService::convertInvoice( $request );
    }
}

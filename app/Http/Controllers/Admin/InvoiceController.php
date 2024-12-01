<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    InvoiceService,
    WarehouseService,
};

use App\Models\{
    Invoice,
};

use Helper;

class InvoiceController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.invoices' );
        $this->data['content'] = 'admin.invoice.index';
        $this->data['breadcrumb'] = [
            [
            'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.invoices' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.invoices' ) ) ] );
        $this->data['content'] = 'admin.invoice.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.invoice.index' ),
                'text' => __( 'template.invoices' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.invoices' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['tax_types'] = Helper::taxTypes();

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.invoices' ) ) ] );
        $this->data['content'] = 'admin.invoice.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.invoice.index' ),
                'text' => __( 'template.invoices' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.invoices' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['tax_types'] = Helper::taxTypes();

        return view( 'admin.main' )->with( $this->data );
    }

    public function allInvoices( Request $request ) {

        return InvoiceService::allInvoices( $request );
    }

    public function oneInvoice( Request $request ) {

        return InvoiceService::oneInvoice( $request );
    }

    public function createInvoice( Request $request ) {

        return InvoiceService::createInvoice( $request );
    }

    public function updateInvoice( Request $request ) {
        return InvoiceService::updateInvoice( $request );
    }

    public function updateInvoiceStatus( Request $request ) {

        return InvoiceService::updateInvoiceStatus( $request );
    }

    public function removeInvoiceAttachment( Request $request ) {

        return InvoiceService::removeInvoiceAttachment( $request );
    }

    public function ckeUpload( Request $request ) {

        return InvoiceService::ckeUpload( $request );
    }

    public function convertDeliveryOrder ( Request $request ) {

        return InvoiceService::convertDeliveryOrder ( $request );
    }
}

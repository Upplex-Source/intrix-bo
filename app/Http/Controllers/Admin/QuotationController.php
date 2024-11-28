<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    QuotationService,
    WarehouseService,
};

use App\Models\{
    Quotation,
};

use Helper;

class QuotationController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.quotations' );
        $this->data['content'] = 'admin.quotation.index';
        $this->data['breadcrumb'] = [
            [
            'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.quotations' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
            '12' => __( 'datatables.convert_sales_order' ),
            '13' => __( 'datatables.convert_invoice' ),
            '14' => __( 'datatables.convert_delivery_order' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.quotations' ) ) ] );
        $this->data['content'] = 'admin.quotation.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.quotation.index' ),
                'text' => __( 'template.quotations' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.quotations' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['tax_types'] = Helper::taxTypes();

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.quotations' ) ) ] );
        $this->data['content'] = 'admin.quotation.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.quotation.index' ),
                'text' => __( 'template.quotations' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.quotations' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['tax_types'] = Helper::taxTypes();

        return view( 'admin.main' )->with( $this->data );
    }

    public function allQuotations( Request $request ) {

        return QuotationService::allQuotations( $request );
    }

    public function oneQuotation( Request $request ) {

        return QuotationService::oneQuotation( $request );
    }

    public function createQuotation( Request $request ) {

        return QuotationService::createQuotation( $request );
    }

    public function updateQuotation( Request $request ) {
        return QuotationService::updateQuotation( $request );
    }

    public function updateQuotationStatus( Request $request ) {

        return QuotationService::updateQuotationStatus( $request );
    }

    public function removeQuotationAttachment( Request $request ) {

        return QuotationService::removeQuotationAttachment( $request );
    }

    public function ckeUpload( Request $request ) {

        return QuotationService::ckeUpload( $request );
    }

    public function convertSalesOrder( Request $request ) {

        return QuotationService::convertSalesOrder( $request );
    }

    public function sendEmail( Request $request ) {

        return QuotationService::sendEmail( $request );
    } 
}

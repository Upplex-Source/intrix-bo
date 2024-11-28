<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    PurchaseService,
};

use Helper;

class PurchaseController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.purchases' );
        $this->data['content'] = 'admin.purchase.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.purchases' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.purchases' ) ) ] );
        $this->data['content'] = 'admin.purchase.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.purchase.index' ),
                'text' => __( 'template.purchases' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.purchases' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['tax_types'] = Helper::taxTypes();

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.purchases' ) ) ] );
        $this->data['content'] = 'admin.purchase.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.purchase.index' ),
                'text' => __( 'template.purchases' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.purchases' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['tax_types'] = Helper::taxTypes();

        return view( 'admin.main' )->with( $this->data );
    }

    public function allPurchases( Request $request ) {

        return PurchaseService::allPurchases( $request );
    }

    public function onePurchase( Request $request ) {

        return PurchaseService::onePurchase( $request );
    }

    public function createPurchase( Request $request ) {

        return PurchaseService::createPurchase( $request );
    }

    public function updatePurchase( Request $request ) {

        return PurchaseService::updatePurchase( $request );
    }

    public function updatePurchaseStatus( Request $request ) {

        return PurchaseService::updatePurchaseStatus( $request );
    }

    public function removePurchaseGalleryImage( Request $request ) {

        return PurchaseService::removePurchaseGalleryImage( $request );
    }

    public function onePurchaseTransaction( Request $request ) {

        return PurchaseService::onePurchaseTransaction( $request );
    }

    public function createPurchaseTransaction( Request $request ) {

        return PurchaseService::createPurchaseTransaction( $request );
    }
    
}

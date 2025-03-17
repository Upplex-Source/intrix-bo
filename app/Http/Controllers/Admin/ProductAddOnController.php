<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ProductAddOnService,
};

use App\Models\{
    ProductAddOn,
    Product,
};

class ProductAddOnController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.product_add_ons' );
        $this->data['content'] = 'admin.product_add_on.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.product_add_ons' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.product_add_ons' ) ) ] );
        $this->data['content'] = 'admin.product_add_on.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.product_add_on.index' ),
                'text' => __( 'template.product_add_ons' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.product_add_ons' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.product_add_ons' ) ) ] );
        $this->data['content'] = 'admin.product_add_on.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.product_add_on.index' ),
                'text' => __( 'template.product_add_ons' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.product_add_ons' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allProductAddOns( Request $request ) {

        return ProductAddOnService::allProductAddOns( $request );
    }

    public function oneProductAddOn( Request $request ) {

        return ProductAddOnService::oneProductAddOn( $request );
    }

    public function createProductAddOn( Request $request ) {

        return ProductAddOnService::createProductAddOn( $request );
    }

    public function updateProductAddOn( Request $request ) {
        return ProductAddOnService::updateProductAddOn( $request );
    }

    public function updateProductAddOnStatus( Request $request ) {

        return ProductAddOnService::updateProductAddOnStatus( $request );
    }

    public function removeProductAddOnGalleryImage( Request $request ) {

        return ProductAddOnService::removeProductAddOnGalleryImage( $request );
    }

}

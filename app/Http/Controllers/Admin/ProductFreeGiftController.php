<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ProductFreeGiftService,
};

use App\Models\{
    ProductFreeGift,
    Product,
};

class ProductFreeGiftController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.product_free_gifts' );
        $this->data['content'] = 'admin.product_free_gift.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.product_free_gifts' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.product_free_gifts' ) ) ] );
        $this->data['content'] = 'admin.product_free_gift.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.product_free_gift.index' ),
                'text' => __( 'template.product_free_gifts' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.product_free_gifts' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.product_free_gifts' ) ) ] );
        $this->data['content'] = 'admin.product_free_gift.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.product_free_gift.index' ),
                'text' => __( 'template.product_free_gifts' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.product_free_gifts' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allProductFreeGifts( Request $request ) {

        return ProductFreeGiftService::allProductFreeGifts( $request );
    }

    public function oneProductFreeGift( Request $request ) {

        return ProductFreeGiftService::oneProductFreeGift( $request );
    }

    public function createProductFreeGift( Request $request ) {

        return ProductFreeGiftService::createProductFreeGift( $request );
    }

    public function updateProductFreeGift( Request $request ) {
        return ProductFreeGiftService::updateProductFreeGift( $request );
    }

    public function updateProductFreeGiftStatus( Request $request ) {

        return ProductFreeGiftService::updateProductFreeGiftStatus( $request );
    }

    public function removeProductFreeGiftGalleryImage( Request $request ) {

        return ProductFreeGiftService::removeProductFreeGiftGalleryImage( $request );
    }

}

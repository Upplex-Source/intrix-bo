<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ProductBundleService,
    WarehouseService,
};

use App\Models\{
    ProductBundle,
};

class ProductBundleController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.product_bundles' );
        $this->data['content'] = 'admin.product_bundle.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.product_bundles' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.product_bundles' ) ) ] );
        $this->data['content'] = 'admin.product_bundle.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.product_bundle.index' ),
                'text' => __( 'template.product_bundles' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.product_bundles' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.product_bundles' ) ) ] );
        $this->data['content'] = 'admin.product_bundle.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.product_bundle.index' ),
                'text' => __( 'template.product_bundles' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.product_bundles' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allProductBundles( Request $request ) {

        return ProductBundleService::allProductBundles( $request );
    }

    public function oneProductBundle( Request $request ) {

        return ProductBundleService::oneProductBundle( $request );
    }

    public function createProductBundle( Request $request ) {

        return ProductBundleService::createProductBundle( $request );
    }

    public function updateProductBundle( Request $request ) {
        return ProductBundleService::updateProductBundle( $request );
    }

    public function updateProductBundleStatus( Request $request ) {

        return ProductBundleService::updateProductBundleStatus( $request );
    }

    public function removeProductBundleGalleryImage( Request $request ) {

        return ProductBundleService::removeProductBundleGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return ProductBundleService::ckeUpload( $request );
    }
}

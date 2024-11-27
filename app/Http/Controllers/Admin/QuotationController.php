<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    BundleService,
    WarehouseService,
};

use App\Models\{
    Bundle,
};

class BundleController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.bundles' );
        $this->data['content'] = 'admin.bundle.index';
        $this->data['breadcrumb'] = [
            [
            'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.bundles' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.bundles' ) ) ] );
        $this->data['content'] = 'admin.bundle.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.bundle.index' ),
                'text' => __( 'template.bundles' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.bundles' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.bundles' ) ) ] );
        $this->data['content'] = 'admin.bundle.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.bundle.index' ),
                'text' => __( 'template.bundles' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.bundles' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allBundles( Request $request ) {

        return BundleService::allBundles( $request );
    }

    public function oneBundle( Request $request ) {

        return BundleService::oneBundle( $request );
    }

    public function createBundle( Request $request ) {

        return BundleService::createBundle( $request );
    }

    public function updateBundle( Request $request ) {
        return BundleService::updateBundle( $request );
    }

    public function updateBundleStatus( Request $request ) {

        return BundleService::updateBundleStatus( $request );
    }

    public function removeBundleGalleryImage( Request $request ) {

        return BundleService::removeBundleGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return BundleService::ckeUpload( $request );
    }

    public function generateBundleCode( Request $request ) {

        return BundleService::generateBundleCode( $request );
    }
}

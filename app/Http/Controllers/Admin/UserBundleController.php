<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    UserBundleService,
    WarehouseService,
};

use App\Models\{
    UserBundle,
};

class UserBundleController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.user_bundles' );
        $this->data['content'] = 'admin.user_bundle.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.user_bundles' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.user_bundles' ) ) ] );
        $this->data['content'] = 'admin.user_bundle.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.user_bundle.index' ),
                'text' => __( 'template.user_bundles' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.user_bundles' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.user_bundles' ) ) ] );
        $this->data['content'] = 'admin.user_bundle.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.user_bundle.index' ),
                'text' => __( 'template.user_bundles' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.user_bundles' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allUserBundles( Request $request ) {

        return UserBundleService::allUserBundles( $request );
    }

    public function oneUserBundle( Request $request ) {

        return UserBundleService::oneUserBundle( $request );
    }

    public function createUserBundle( Request $request ) {

        return UserBundleService::createUserBundle( $request );
    }

    public function updateUserBundle( Request $request ) {
        return UserBundleService::updateUserBundle( $request );
    }

    public function updateUserBundleStatus( Request $request ) {

        return UserBundleService::updateUserBundleStatus( $request );
    }

    public function removeUserBundleGalleryImage( Request $request ) {

        return UserBundleService::removeUserBundleGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return UserBundleService::ckeUpload( $request );
    }
}

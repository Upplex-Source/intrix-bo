<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    OutletService,
};

class OutletController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.outlets' );
        $this->data['content'] = 'admin.outlet.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.outlets' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.outlets' ) ) ] );
        $this->data['content'] = 'admin.outlet.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.outlet.index' ),
                'text' => __( 'template.outlets' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.outlets' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.outlets' ) ) ] );
        $this->data['content'] = 'admin.outlet.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.outlet.index' ),
                'text' => __( 'template.outlets' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.outlets' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allOutlets( Request $request ) {

        return OutletService::allOutlets( $request );
    }

    public function oneOutlet( Request $request ) {

        return OutletService::oneOutlet( $request );
    }

    public function createOutlet( Request $request ) {

        return OutletService::createOutlet( $request );
    }

    public function updateOutlet( Request $request ) {

        return OutletService::updateOutlet( $request );
    }

    public function updateOutletStatus( Request $request ) {

        return OutletService::updateOutletStatus( $request );
    }

    public function removeOutletGalleryImage( Request $request ) {

        return OutletService::removeOutletGalleryImage( $request );
    }
}

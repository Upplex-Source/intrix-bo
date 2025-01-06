<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    SyrupService,
};

class SyrupController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.syrups' );
        $this->data['content'] = 'admin.syrup.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.syrups' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.syrups' ) ) ] );
        $this->data['content'] = 'admin.syrup.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.syrup.index' ),
                'text' => __( 'template.syrups' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.syrups' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.syrups' ) ) ] );
        $this->data['content'] = 'admin.syrup.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.syrup.index' ),
                'text' => __( 'template.syrups' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.syrups' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allSyrups( Request $request ) {

        return SyrupService::allSyrups( $request );
    }

    public function oneSyrup( Request $request ) {

        return SyrupService::oneSyrup( $request );
    }

    public function createSyrup( Request $request ) {

        return SyrupService::createSyrup( $request );
    }

    public function updateSyrup( Request $request ) {

        return SyrupService::updateSyrup( $request );
    }

    public function updateSyrupStatus( Request $request ) {

        return SyrupService::updateSyrupStatus( $request );
    }

    public function removeSyrupGalleryImage( Request $request ) {

        return SyrupService::removeSyrupGalleryImage( $request );
    }
}

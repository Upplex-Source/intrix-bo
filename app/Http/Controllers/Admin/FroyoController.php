<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    FroyoService,
};

class FroyoController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.froyos' );
        $this->data['content'] = 'admin.froyo.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.froyos' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.froyos' ) ) ] );
        $this->data['content'] = 'admin.froyo.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.froyo.index' ),
                'text' => __( 'template.froyos' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.froyos' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.froyos' ) ) ] );
        $this->data['content'] = 'admin.froyo.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.froyo.index' ),
                'text' => __( 'template.froyos' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.froyos' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allFroyos( Request $request ) {

        return FroyoService::allFroyos( $request );
    }

    public function oneFroyo( Request $request ) {

        return FroyoService::oneFroyo( $request );
    }

    public function createFroyo( Request $request ) {

        return FroyoService::createFroyo( $request );
    }

    public function updateFroyo( Request $request ) {

        return FroyoService::updateFroyo( $request );
    }

    public function updateFroyoStatus( Request $request ) {

        return FroyoService::updateFroyoStatus( $request );
    }

    public function removeFroyoGalleryImage( Request $request ) {

        return FroyoService::removeFroyoGalleryImage( $request );
    }    
    
    public function allFroyosForVendingMachine( Request $request ) {

        return FroyoService::allFroyosForVendingMachine( $request );
    }
    
    public function getFroyoStock( Request $request ) {

        return FroyoService::getFroyoStock( $request );
    }
    
}

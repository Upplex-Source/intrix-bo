<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    TyreService,
};

class TyreController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.tyres' );
        $this->data['content'] = 'admin.tyre.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.tyres' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.tyres' ) ) ] );
        $this->data['content'] = 'admin.tyre.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.tyre.index' ),
                'text' => __( 'template.tyres' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.tyres' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.tyres' ) ) ] );
        $this->data['content'] = 'admin.tyre.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.tyre.index' ),
                'text' => __( 'template.tyres' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.tyres' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allTyres( Request $request ) {

        return TyreService::allTyres( $request );
    }

    public function oneTyre( Request $request ) {

        return TyreService::oneTyre( $request );
    }

    public function createTyre( Request $request ) {

        return TyreService::createTyre( $request );
    }

    public function updateTyre( Request $request ) {

        return TyreService::updateTyre( $request );
    }

    public function updateTyreStatus( Request $request ) {

        return TyreService::updateTyreStatus( $request );
    }
}

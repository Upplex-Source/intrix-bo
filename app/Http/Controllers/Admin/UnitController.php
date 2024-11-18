<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    UnitService,
};

class UnitController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.units' );
        $this->data['content'] = 'admin.unit.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.units' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.units' ) ) ] );
        $this->data['content'] = 'admin.unit.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.unit.index' ),
                'text' => __( 'template.units' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.units' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.units' ) ) ] );
        $this->data['content'] = 'admin.unit.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.unit.index' ),
                'text' => __( 'template.units' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.units' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allUnits( Request $request ) {

        return UnitService::allUnits( $request );
    }

    public function oneUnit( Request $request ) {

        return UnitService::oneUnit( $request );
    }

    public function createUnit( Request $request ) {

        return UnitService::createUnit( $request );
    }

    public function updateUnit( Request $request ) {

        return UnitService::updateUnit( $request );
    }

    public function updateUnitStatus( Request $request ) {

        return UnitService::updateUnitStatus( $request );
    }

    public function removeUnitGalleryImage( Request $request ) {

        return UnitService::removeUnitGalleryImage( $request );
    }
}

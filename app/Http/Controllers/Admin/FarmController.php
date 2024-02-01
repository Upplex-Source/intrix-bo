<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    FarmService,
};

class FarmController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.farms' );
        $this->data['content'] = 'admin.farm.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.farms' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.farms' ) ) ] );
        $this->data['content'] = 'admin.farm.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.farm.index' ),
                'text' => __( 'template.farms' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.farms' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.farms' ) ) ] );
        $this->data['content'] = 'admin.farm.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.farm.index' ),
                'text' => __( 'template.farms' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.farms' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allFarms( Request $request ) {

        return FarmService::allFarms( $request );
    }

    public function oneFarm( Request $request ) {

        return FarmService::oneFarm( $request );
    }

    public function createFarm( Request $request ) {

        return FarmService::createFarm( $request );
    }

    public function updateFarm( Request $request ) {

        return FarmService::updateFarm( $request );
    }

    public function updateFarmStatus( Request $request ) {

        return FarmService::updateFarmStatus( $request );
    }

    public function removeFarmGalleryImage( Request $request ) {

        return FarmService::removeFarmGalleryImage( $request );
    }
}

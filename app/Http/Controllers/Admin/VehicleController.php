<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    VehicleService,
};

class VehicleController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.vehicles' );
        $this->data['content'] = 'admin.vehicle.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.vehicles' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['type'] = [
            '1' => __( 'vehicle.truck' ),
            '2' => __( 'vehicle.timber_jinker' ),
            '3' => __( 'vehicle.curtain_sider' ),
            '4' => __( 'vehicle.open_cargo' ),
        ];
        $this->data['data']['in_service'] = [
            '0' => __( 'datatables.no' ),
            '1' => __( 'datatables.yes' ),
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vehicles' ) ) ] );
        $this->data['content'] = 'admin.vehicle.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.vehicle.index' ),
                'text' => __( 'template.vehicles' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vehicles' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['type'] = [
            '1' => __( 'vehicle.truck' ),
            '2' => __( 'vehicle.timber_jinker' ),
            '3' => __( 'vehicle.curtain_sider' ),
            '4' => __( 'vehicle.open_cargo' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.vehicles' ) ) ] );
        $this->data['content'] = 'admin.vehicle.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.vehicle.index' ),
                'text' => __( 'template.vehicles' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.vehicles' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['type'] = [
            '1' => __( 'vehicle.truck' ),
            '2' => __( 'vehicle.timber_jinker' ),
            '3' => __( 'vehicle.curtain_sider' ),
            '4' => __( 'vehicle.open_cargo' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allVehicles( Request $request ) {

        return VehicleService::allVehicles( $request );
    }

    public function oneVehicle( Request $request ) {

        return VehicleService::oneVehicle( $request );
    }

    public function createVehicle( Request $request ) {

        return VehicleService::createVehicle( $request );
    }

    public function updateVehicle( Request $request ) {

        return VehicleService::updateVehicle( $request );
    }

    public function updateVehicleStatus( Request $request ) {

        return VehicleService::updateVehicleStatus( $request );
    }
}

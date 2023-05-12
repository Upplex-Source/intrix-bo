<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    DriverService,
};

class DriverController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.drivers' );
        $this->data['content'] = 'admin.driver.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.drivers' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['employment_type'] = [
            '1' => __( 'driver.full_time' ),
            '2' => __( 'driver.part_time' ),
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.drivers' ) ) ] );
        $this->data['content'] = 'admin.driver.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.driver.index' ),
                'text' => __( 'template.drivers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.drivers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.drivers' ) ) ] );
        $this->data['content'] = 'admin.driver.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.driver.index' ),
                'text' => __( 'template.drivers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.drivers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allDrivers( Request $request ) {

        return DriverService::allDrivers( $request );
    }

    public function oneDriver( Request $request ) {

        return DriverService::oneDriver( $request );
    }

    public function createDriver( Request $request ) {

        return DriverService::createDriver( $request );
    }

    public function updateDriver( Request $request ) {

        return DriverService::updateDriver( $request );
    }

    public function updateDriverStatus( Request $request ) {

        return DriverService::updateDriverStatus( $request );
    }
}

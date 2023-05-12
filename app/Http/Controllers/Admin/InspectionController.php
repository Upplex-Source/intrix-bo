<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    InspectionService,
};

class InspectionController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.vehicle_inspections' );
        $this->data['content'] = 'admin.inspection.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.vehicle_inspections' ),
                'class' => 'active',
            ],
        ];
        // $this->data['data']['type'] = [
        //     '1' => 'Van',
        // ];
        // $this->data['data']['in_service'] = [
        //     '0' => __( 'datatables.no' ),
        //     '1' => __( 'datatables.yes' ),
        // ];
        // $this->data['data']['status'] = [
        //     '10' => __( 'datatables.activated' ),
        //     '20' => __( 'datatables.suspended' ),
        // ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vehicle_inspections' ) ) ] );
        $this->data['content'] = 'admin.inspection.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.vehicle_inspection.index' ),
                'text' => __( 'template.vehicle_inspections' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vehicle_inspections' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    MeasurementUnitService,
};

class MeasurementUnitController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.measurement_units' );
        $this->data['content'] = 'admin.measurement_unit.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.measurement_units' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.measurement_units' ) ) ] );
        $this->data['content'] = 'admin.measurement_unit.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.measurement_unit.index' ),
                'text' => __( 'template.measurement_units' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.measurement_units' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.measurement_units' ) ) ] );
        $this->data['content'] = 'admin.measurement_unit.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.measurement_unit.index' ),
                'text' => __( 'template.measurement_units' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.measurement_units' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allMeasurementUnits( Request $request ) {

        return MeasurementUnitService::allMeasurementUnits( $request );
    }

    public function oneMeasurementUnit( Request $request ) {

        return MeasurementUnitService::oneMeasurementUnit( $request );
    }

    public function createMeasurementUnit( Request $request ) {

        return MeasurementUnitService::createMeasurementUnit( $request );
    }

    public function updateMeasurementUnit( Request $request ) {

        return MeasurementUnitService::updateMeasurementUnit( $request );
    }

    public function updateMeasurementUnitStatus( Request $request ) {

        return MeasurementUnitService::updateMeasurementUnitStatus( $request );
    }

    public function removeMeasurementUnitGalleryImage( Request $request ) {

        return MeasurementUnitService::removeMeasurementUnitGalleryImage( $request );
    }
}

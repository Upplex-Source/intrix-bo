<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CompanyService,
    MaintenanceRecordService,
};

class MaintenanceRecordController extends Controller
{
    public function serviceRecords() {

        $this->data['header']['title'] = __( 'template.service_records' );
        $this->data['content'] = 'admin.maintenance_record.service_record';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.service_records' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function addServiceRecord( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.service_records' ) ) ] );
        $this->data['content'] = 'admin.maintenance_record.add_service_record';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.maintenance_record.serviceRecords' ),
                'text' => __( 'template.service_records' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.service_records' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['company'] = CompanyService::get();
        $this->data['data']['service_types'] = [
            '1' => __( 'maintenance_record.engine_oil' ),
            '2' => __( 'maintenance_record.fuel_filter' ),
            '3' => __( 'maintenance_record.water_separator' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function editServiceRecord( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.service_records' ) ) ] );
        $this->data['content'] = 'admin.maintenance_record.edit_service_record';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.maintenance_record.serviceRecords' ),
                'text' => __( 'template.service_records' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.service_records' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['company'] = CompanyService::get();
        $this->data['data']['service_types'] = [
            '1' => __( 'maintenance_record.engine_oil' ),
            '2' => __( 'maintenance_record.fuel_filter' ),
            '3' => __( 'maintenance_record.water_separator' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allServiceRecords( Request $request ) {

        return MaintenanceRecordService::allServiceRecords( $request );
    }

    public function oneServiceRecord( Request $request ) {

        return MaintenanceRecordService::oneServiceRecord( $request );
    }

    public function validateItemServiceRecord( Request $request ) {
        
        return MaintenanceRecordService::validateItemServiceRecord( $request );
    }

    public function createServiceRecord( Request $request ) {

        return MaintenanceRecordService::createServiceRecord( $request );
    }

    public function updateServiceRecord( Request $request ) {

        return MaintenanceRecordService::updateServiceRecord( $request );
    }

    public function tyreRecords() {

        $this->data['header']['title'] = __( 'template.tyre_records' );
        $this->data['content'] = 'admin.maintenance_record.tyre_record';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.tyre_records' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function addTyreRecord( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.tyre_records' ) ) ] );
        $this->data['content'] = 'admin.maintenance_record.add_tyre_record';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.maintenance_record.tyreRecords' ),
                'text' => __( 'template.tyre_records' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.tyre_records' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allTyreRecords( Request $request ) {

        return MaintenanceRecordService::allTyreRecords( $request );
    }

    public function oneTypreRecord( Request $request ) {

        return MaintenanceRecordService::oneTypreRecord( $request );
    }

    public function validateItemTyreRecord( Request $request ) {
        
        return MaintenanceRecordService::validateItemTyreRecord( $request );
    }

    public function createTyreRecord( Request $request ) {

        return MaintenanceRecordService::createTyreRecord( $request );
    }
}

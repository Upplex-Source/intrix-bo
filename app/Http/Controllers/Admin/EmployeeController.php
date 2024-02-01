<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    EmployeeService,
};

class EmployeeController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.workers' );
        $this->data['content'] = 'admin.employee.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.workers' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['designation'] = [
            '1' => __( 'worker.driver' ),
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.workers' ) ) ] );
        $this->data['content'] = 'admin.employee.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.worker.index' ),
                'text' => __( 'template.workers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.workers' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['designation'] = [
            '1' => __( 'worker.driver' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.workers' ) ) ] );
        $this->data['content'] = 'admin.employee.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.worker.index' ),
                'text' => __( 'template.workers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.workers' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['designation'] = [
            '1' => __( 'worker.driver' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allWorkers( Request $request ) {

        return EmployeeService::allWorkers( $request );
    }

    public function oneWorker( Request $request ) {

        return EmployeeService::oneWorker( $request );
    }

    public function createWorker( Request $request ) {

        return EmployeeService::createWorker( $request );
    }

    public function updateWorker( Request $request ) {

        return EmployeeService::updateWorker( $request );
    }

    public function updateWorkerStatus( Request $request ) {

        return EmployeeService::updateWorkerStatus( $request );
    }

    public function calculateBirthday( Request $request ) {

        return EmployeeService::calculateBirthday( $request );
    }
}

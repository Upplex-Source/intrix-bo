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

        $this->data['header']['title'] = __( 'template.employees' );
        $this->data['content'] = 'admin.employee.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.employees' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['designation'] = [
            '1' => __( 'employee.driver' ),
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.employees' ) ) ] );
        $this->data['content'] = 'admin.employee.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.employee.index' ),
                'text' => __( 'template.employees' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.employees' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['designation'] = [
            '1' => __( 'employee.driver' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.employees' ) ) ] );
        $this->data['content'] = 'admin.employee.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.employee.index' ),
                'text' => __( 'template.employees' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.employees' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['designation'] = [
            '1' => __( 'employee.driver' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allEmployees( Request $request ) {

        return EmployeeService::allEmployees( $request );
    }

    public function oneEmployee( Request $request ) {

        return EmployeeService::oneEmployee( $request );
    }

    public function createEmployee( Request $request ) {

        return EmployeeService::createEmployee( $request );
    }

    public function updateEmployee( Request $request ) {

        return EmployeeService::updateEmployee( $request );
    }

    public function updateEmployeeStatus( Request $request ) {

        return EmployeeService::updateEmployeeStatus( $request );
    }
}

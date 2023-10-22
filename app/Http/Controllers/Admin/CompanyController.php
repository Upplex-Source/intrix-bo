<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CompanyService,
};

class CompanyController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.companies' );
        $this->data['content'] = 'admin.company.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.companies' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.companies' ) ) ] );
        $this->data['content'] = 'admin.company.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.company.index' ),
                'text' => __( 'template.companies' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.companies' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.companies' ) ) ] );
        $this->data['content'] = 'admin.company.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.company.index' ),
                'text' => __( 'template.companies' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.companies' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allCompanies( Request $request ) {

        return CompanyService::allCompanies( $request );
    }

    public function oneCompany( Request $request ) {

        return CompanyService::oneCompany( $request );
    }

    public function createCompany( Request $request ) {

        return CompanyService::createCompany( $request );
    }

    public function updateCompany( Request $request ) {

        return CompanyService::updateCompany( $request );
    }

    public function updateCompanyStatus( Request $request ) {

        return CompanyService::updateCompanyStatus( $request );
    }
}

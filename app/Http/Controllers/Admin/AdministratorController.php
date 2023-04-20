<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    AdministratorService,
};

class AdministratorController extends Controller
{
    public function login( Request $request ) {

        $data['basic'] = true;
        $data['content'] = 'admin.auth.login';

        return view( 'admin.main_pre_auth' )->with( $data );
    }

    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.administrators' );
        $this->data['content'] = 'admin.administrator.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.administrators' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.administrators' ) ) ] );
        $this->data['content'] = 'admin.administrator.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.administrator.index' ),
                'text' => __( 'template.administrators' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.administrators' ) ) ] );
        $this->data['content'] = 'admin.administrator.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.administrator.index' ),
                'text' => __( 'template.administrators' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function logout( Request $request ) {

    }

    public function allAdministrators( Request $request ) {
        return AdministratorService::allAdministrators( $request );
    }

    public function oneAdministrator( Request $request ) {
        return AdministratorService::oneAdministrator( $request );
    }

    public function createAdministrator( Request $request ) {
        return AdministratorService::createAdministrator( $request );
    }

    public function updateAdministrator( Request $request ) {
        return AdministratorService::updateAdministrator( $request );
    }
}

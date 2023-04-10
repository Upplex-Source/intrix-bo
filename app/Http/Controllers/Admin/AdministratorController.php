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

    public function logout( Request $request ) {

    }

    public function allAdmins( Request $request ) {

        return AdministratorService::allAdmins( $request );
    }
}

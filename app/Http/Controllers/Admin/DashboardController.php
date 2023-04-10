<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.dashboard' );
        $this->data['content'] = 'admin.dashboard.index';

        return view( 'admin.main' )->with( $this->data );
    }
}

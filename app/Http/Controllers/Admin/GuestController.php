<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    GuestService,
};

class GuestController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.guests' );
        $this->data['content'] = 'admin.guest.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.guests' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.guests' ) ) ] );
        $this->data['content'] = 'admin.guest.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.guest.index' ),
                'text' => __( 'template.guests' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.guests' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.guests' ) ) ] );
        $this->data['content'] = 'admin.guest.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.guest.index' ),
                'text' => __( 'template.guests' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.guests' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allGuests( Request $request ) {

        return GuestService::allGuests( $request );
    }

    public function oneGuest( Request $request ) {

        return GuestService::oneGuest( $request );
    }

    public function createGuest( Request $request ) {

        return GuestService::createGuest( $request );
    }

    public function updateGuest( Request $request ) {

        return GuestService::updateGuest( $request );
    }

    public function updateGuestStatus( Request $request ) {

        return GuestService::updateGuestStatus( $request );
    }
}

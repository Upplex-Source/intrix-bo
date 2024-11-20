<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    WorkmanshipService,
};

class WorkmanshipController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.workmanships' );
        $this->data['content'] = 'admin.workmanship.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.workmanships' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.workmanships' ) ) ] );
        $this->data['content'] = 'admin.workmanship.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.workmanship.index' ),
                'text' => __( 'template.workmanships' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.workmanships' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.workmanships' ) ) ] );
        $this->data['content'] = 'admin.workmanship.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.workmanship.index' ),
                'text' => __( 'template.workmanships' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.workmanships' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allWorkmanships( Request $request ) {

        return WorkmanshipService::allWorkmanships( $request );
    }

    public function oneWorkmanship( Request $request ) {

        return WorkmanshipService::oneWorkmanship( $request );
    }

    public function createWorkmanship( Request $request ) {

        return WorkmanshipService::createWorkmanship( $request );
    }

    public function updateWorkmanship( Request $request ) {

        return WorkmanshipService::updateWorkmanship( $request );
    }

    public function updateWorkmanshipStatus( Request $request ) {

        return WorkmanshipService::updateWorkmanshipStatus( $request );
    }

    public function removeWorkmanshipGalleryImage( Request $request ) {

        return WorkmanshipService::removeWorkmanshipGalleryImage( $request );
    }
}

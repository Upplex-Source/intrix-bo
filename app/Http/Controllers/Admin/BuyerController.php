<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    BuyerService,
};

use Illuminate\Support\Facades\{
    DB,
};

class BuyerController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.buyers' );
        $this->data['content'] = 'admin.buyer.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.buyers' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.buyers' ) ) ] );
        $this->data['content'] = 'admin.buyer.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.buyer.index' ),
                'text' => __( 'template.buyers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.buyers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.buyers' ) ) ] );
        $this->data['content'] = 'admin.buyer.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.buyer.index' ),
                'text' => __( 'template.buyers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.buyers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allBuyers( Request $request ) {
        return BuyerService::allBuyers( $request );
    }

    public function oneBuyer( Request $request ) {
        return BuyerService::oneBuyer( $request );
    }

    public function createBuyer( Request $request ) {
        return BuyerService::createBuyer( $request );
    }

    public function updateBuyer( Request $request ) {
        return BuyerService::updateBuyer( $request );
    }
}

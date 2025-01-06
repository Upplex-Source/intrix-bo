<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ToppingService,
};

class ToppingController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.toppings' );
        $this->data['content'] = 'admin.topping.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.toppings' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.toppings' ) ) ] );
        $this->data['content'] = 'admin.topping.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.topping.index' ),
                'text' => __( 'template.toppings' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.toppings' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.toppings' ) ) ] );
        $this->data['content'] = 'admin.topping.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.topping.index' ),
                'text' => __( 'template.toppings' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.toppings' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allToppings( Request $request ) {

        return ToppingService::allToppings( $request );
    }

    public function oneTopping( Request $request ) {

        return ToppingService::oneTopping( $request );
    }

    public function createTopping( Request $request ) {

        return ToppingService::createTopping( $request );
    }

    public function updateTopping( Request $request ) {

        return ToppingService::updateTopping( $request );
    }

    public function updateToppingStatus( Request $request ) {

        return ToppingService::updateToppingStatus( $request );
    }

    public function removeToppingGalleryImage( Request $request ) {

        return ToppingService::removeToppingGalleryImage( $request );
    }
}

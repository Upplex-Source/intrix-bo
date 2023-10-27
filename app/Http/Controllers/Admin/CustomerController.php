<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CustomerService,
};

class CustomerController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.customers' );
        $this->data['content'] = 'admin.customer.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.customers' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.customers' ) ) ] );
        $this->data['content'] = 'admin.customer.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.customer.index' ),
                'text' => __( 'template.customers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.customers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.customers' ) ) ] );
        $this->data['content'] = 'admin.customer.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.customer.index' ),
                'text' => __( 'template.customers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.customers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allCustomers( Request $request ) {

        return CustomerService::allCustomers( $request );
    }

    public function oneCustomer( Request $request ) {

        return CustomerService::oneCustomer( $request );
    }

    public function createCustomer( Request $request ) {

        return CustomerService::createCustomer( $request );
    }

    public function updateCustomer( Request $request ) {

        return CustomerService::updateCustomer( $request );
    }

    public function updateCustomerStatus( Request $request ) {

        return CustomerService::updateCustomerStatus( $request );
    }
}

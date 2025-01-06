<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    VendingMachineProductService,
};

class VendingMachineProductController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.vending_machine_products' );
        $this->data['content'] = 'admin.vending_machine_product.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.vending_machine_products' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vending_machine_products' ) ) ] );
        $this->data['content'] = 'admin.vending_machine_product.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.vending_machine_product.index' ),
                'text' => __( 'template.vending_machine_products' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vending_machine_products' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.vending_machine_products' ) ) ] );
        $this->data['content'] = 'admin.vending_machine_product.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.vending_machine_product.index' ),
                'text' => __( 'template.vending_machine_products' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.vending_machine_products' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allVendingMachineProducts( Request $request ) {

        return VendingMachineProductService::allVendingMachineProducts( $request );
    }

    public function oneVendingMachineProduct( Request $request ) {

        return VendingMachineProductService::oneVendingMachineProduct( $request );
    }

    public function createVendingMachineProduct( Request $request ) {

        return VendingMachineProductService::createVendingMachineProduct( $request );
    }

    public function updateVendingMachineProduct( Request $request ) {

        return VendingMachineProductService::updateVendingMachineProduct( $request );
    }

    public function updateVendingMachineProductstatus( Request $request ) {

        return VendingMachineProductService::updateVendingMachineProductstatus( $request );
    }

    public function removeVendingMachineProductGalleryImage( Request $request ) {

        return VendingMachineProductService::removeVendingMachineProductGalleryImage( $request );
    }
}

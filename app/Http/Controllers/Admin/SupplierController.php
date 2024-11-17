<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    SupplierService,
};

class SupplierController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.suppliers' );
        $this->data['content'] = 'admin.supplier.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.suppliers' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.suppliers' ) ) ] );
        $this->data['content'] = 'admin.supplier.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.supplier.index' ),
                'text' => __( 'template.suppliers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.suppliers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.suppliers' ) ) ] );
        $this->data['content'] = 'admin.supplier.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.supplier.index' ),
                'text' => __( 'template.suppliers' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.suppliers' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allSuppliers( Request $request ) {

        return SupplierService::allSuppliers( $request );
    }

    public function oneSupplier( Request $request ) {

        return SupplierService::oneSupplier( $request );
    }

    public function createSupplier( Request $request ) {

        return SupplierService::createSupplier( $request );
    }

    public function updateSupplier( Request $request ) {

        return SupplierService::updateSupplier( $request );
    }

    public function updateSupplierStatus( Request $request ) {

        return SupplierService::updateSupplierStatus( $request );
    }

    public function removeSupplierGalleryImage( Request $request ) {

        return SupplierService::removeSupplierGalleryImage( $request );
    }
}

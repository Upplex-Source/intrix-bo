<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    VendorService,
};

class VendorController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.vendors' );
        $this->data['content'] = 'admin.vendor.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.vendors' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['type_mapper'] = [
            '1' => __( 'vendor.parts' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vendors' ) ) ] );
        $this->data['content'] = 'admin.vendor.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.vendor.index' ),
                'text' => __( 'template.vendors' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vendors' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.vendors' ) ) ] );
        $this->data['content'] = 'admin.vendor.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.vendor.index' ),
                'text' => __( 'template.vendors' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.vendors' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allVendors( Request $request ) {

        return VendorService::allVendors( $request );
    }

    public function oneVendor( Request $request ) {

        return VendorService::oneVendor( $request );
    }

    public function createVendor( Request $request ) {

        return VendorService::createVendor( $request );
    }

    public function updateVendor( Request $request ) {

        return VendorService::updateVendor( $request );
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    BrandService,
};

class BrandController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.brands' );
        $this->data['content'] = 'admin.brand.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.brands' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.brands' ) ) ] );
        $this->data['content'] = 'admin.brand.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.brand.index' ),
                'text' => __( 'template.brands' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.brands' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.brands' ) ) ] );
        $this->data['content'] = 'admin.brand.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.brand.index' ),
                'text' => __( 'template.brands' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.brands' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allBrands( Request $request ) {

        return BrandService::allBrands( $request );
    }

    public function oneBrand( Request $request ) {

        return BrandService::oneBrand( $request );
    }

    public function createBrand( Request $request ) {

        return BrandService::createBrand( $request );
    }

    public function updateBrand( Request $request ) {

        return BrandService::updateBrand( $request );
    }

    public function updateBrandStatus( Request $request ) {

        return BrandService::updateBrandStatus( $request );
    }

    public function removeBrandGalleryImage( Request $request ) {

        return BrandService::removeBrandGalleryImage( $request );
    }
}

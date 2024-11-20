<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    TaxMethodService,
    WarehouseService,
};

use App\Models\{
    TaxMethod,
};

class TaxMethodController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.tax_methods' );
        $this->data['content'] = 'admin.tax_method.index';
        $this->data['breadcrumb'] = [
            [
            'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.tax_methods' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.tax_methods' ) ) ] );
        $this->data['content'] = 'admin.tax_method.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.tax_method.index' ),
                'text' => __( 'template.tax_methods' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.tax_methods' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.tax_methods' ) ) ] );
        $this->data['content'] = 'admin.tax_method.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.tax_method.index' ),
                'text' => __( 'template.tax_methods' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.tax_methods' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allTaxMethods( Request $request ) {

        return TaxMethodService::allTaxMethods( $request );
    }

    public function oneTaxMethod( Request $request ) {

        return TaxMethodService::oneTaxMethod( $request );
    }

    public function createTaxMethod( Request $request ) {

        return TaxMethodService::createTaxMethod( $request );
    }

    public function updateTaxMethod( Request $request ) {
        return TaxMethodService::updateTaxMethod( $request );
    }

    public function updateTaxMethodStatus( Request $request ) {

        return TaxMethodService::updateTaxMethodStatus( $request );
    }

    public function removeTaxMethodGalleryImage( Request $request ) {

        return TaxMethodService::removeTaxMethodGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return TaxMethodService::ckeUpload( $request );
    }

    public function generateTaxMethodCode( Request $request ) {

        return TaxMethodService::generateTaxMethodCode( $request );
    }
}

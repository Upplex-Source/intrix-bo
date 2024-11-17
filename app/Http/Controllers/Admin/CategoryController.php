<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CategoryService,
};

class CategoryController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.categories' );
        $this->data['content'] = 'admin.category.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.categories' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.categories' ) ) ] );
        $this->data['content'] = 'admin.category.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.category.index' ),
                'text' => __( 'template.categories' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.categories' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.categories' ) ) ] );
        $this->data['content'] = 'admin.category.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.category.index' ),
                'text' => __( 'template.categories' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.categories' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allCategories( Request $request ) {

        return CategoryService::allCategories( $request );
    }

    public function oneCategory( Request $request ) {

        return CategoryService::oneCategory( $request );
    }

    public function createCategory( Request $request ) {

        return CategoryService::createCategory( $request );
    }

    public function updateCategory( Request $request ) {

        return CategoryService::updateCategory( $request );
    }

    public function updateCategoryStatus( Request $request ) {

        return CategoryService::updateCategoryStatus( $request );
    }

    public function removeCategoryGalleryImage( Request $request ) {

        return CategoryService::removeCategoryGalleryImage( $request );
    }
}

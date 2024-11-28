<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ExpenseCategoryService,
    WarehouseService,
};

use App\Models\{
    ExpenseCategory,
};

class ExpenseCategoryController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.expenses_categories' );
        $this->data['content'] = 'admin.expense_category.index';
        $this->data['breadcrumb'] = [
            [
            'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.expenses_categories' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.expenses_categories' ) ) ] );
        $this->data['content'] = 'admin.expense_category.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.expense_category.index' ),
                'text' => __( 'template.expenses_categories' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.expenses_categories' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.expenses_categories' ) ) ] );
        $this->data['content'] = 'admin.expense_category.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.expense_category.index' ),
                'text' => __( 'template.expenses_categories' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.expenses_categories' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allExpenseCategories( Request $request ) {

        return ExpenseCategoryService::allExpenseCategories( $request );
    }

    public function oneExpenseCategory( Request $request ) {

        return ExpenseCategoryService::oneExpenseCategory( $request );
    }

    public function createExpenseCategory( Request $request ) {

        return ExpenseCategoryService::createExpenseCategory( $request );
    }

    public function updateExpenseCategory( Request $request ) {
        return ExpenseCategoryService::updateExpenseCategory( $request );
    }

    public function updateExpenseCategoryStatus( Request $request ) {

        return ExpenseCategoryService::updateExpenseCategoryStatus( $request );
    }

    public function removeExpenseCategoryGalleryImage( Request $request ) {

        return ExpenseCategoryService::removeExpenseCategoryGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return ExpenseCategoryService::ckeUpload( $request );
    }

    public function generateExpenseCategoryCode( Request $request ) {

        return ExpenseCategoryService::generateExpenseCategoryCode( $request );
    }
}

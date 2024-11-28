<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ExpenseService,
    WarehouseService,
};

use App\Models\{
    Expense,
};

class ExpenseController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.expenses' );
        $this->data['content'] = 'admin.expense.index';
        $this->data['breadcrumb'] = [
            [
            'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.expenses' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.expenses' ) ) ] );
        $this->data['content'] = 'admin.expense.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.expense.index' ),
                'text' => __( 'template.expenses' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.expenses' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.expenses' ) ) ] );
        $this->data['content'] = 'admin.expense.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.expense.index' ),
                'text' => __( 'template.expenses' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.expenses' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allExpenses( Request $request ) {

        return ExpenseService::allExpenses( $request );
    }

    public function oneExpense( Request $request ) {

        return ExpenseService::oneExpense( $request );
    }

    public function createExpense( Request $request ) {

        return ExpenseService::createExpense( $request );
    }

    public function updateExpense( Request $request ) {
        return ExpenseService::updateExpense( $request );
    }

    public function updateExpenseStatus( Request $request ) {

        return ExpenseService::updateExpenseStatus( $request );
    }

    public function removeExpenseAttachment ( Request $request ) {

        return ExpenseService::removeExpenseAttachment ( $request );
    }

    public function ckeUpload( Request $request ) {

        return ExpenseService::ckeUpload( $request );
    }

    public function generateExpenseCode( Request $request ) {

        return ExpenseService::generateExpenseCode( $request );
    }
}

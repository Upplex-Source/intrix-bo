<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ExpenseAccountService,
    WarehouseService,
};

use App\Models\{
    ExpenseAccount,
};

class ExpenseAccountController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.expenses' );
        $this->data['content'] = 'admin.expense_account.index';
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
        $this->data['content'] = 'admin.expense_account.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.expense_account.index' ),
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
        $this->data['content'] = 'admin.expense_account.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.expense_account.index' ),
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

    public function allExpenseAccounts( Request $request ) {

        return ExpenseAccountService::allExpenseAccounts( $request );
    }

    public function oneExpenseAccount( Request $request ) {

        return ExpenseAccountService::oneExpenseAccount( $request );
    }

    public function createExpenseAccount( Request $request ) {

        return ExpenseAccountService::createExpenseAccount( $request );
    }

    public function updateExpenseAccount( Request $request ) {
        return ExpenseAccountService::updateExpenseAccount( $request );
    }

    public function updateExpenseAccountStatus( Request $request ) {

        return ExpenseAccountService::updateExpenseAccountStatus( $request );
    }

    public function removeExpenseAccountGalleryImage( Request $request ) {

        return ExpenseAccountService::removeExpenseAccountGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return ExpenseAccountService::ckeUpload( $request );
    }

    public function generateExpenseAccountCode( Request $request ) {

        return ExpenseAccountService::generateExpenseAccountCode( $request );
    }
}

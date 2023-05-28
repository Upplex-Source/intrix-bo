<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CompanyService,
    TollExpenseService,
};

class TollExpenseController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.toll_expenses' );
        $this->data['content'] = 'admin.toll_expense.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.toll_expenses' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['transaction_type'] = [
            '1' => __( 'expenses.toll_usage' ),
            '2' => __( 'expenses.reload' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.toll_expenses' ) ) ] );
        $this->data['content'] = 'admin.toll_expense.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.toll_expense.index' ),
                'text' => __( 'template.toll_expenses' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.toll_expenses' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['transaction_type'] = [
            '1' => __( 'expenses.toll_usage' ),
            '2' => __( 'expenses.reload' ),
        ];
        $this->data['data']['class'] = [
            '0' => 0,
            '1' => 1,
            '2' => 2,
            '3' => 3,
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.toll_expenses' ) ) ] );
        $this->data['content'] = 'admin.toll_expense.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.toll_expense.index' ),
                'text' => __( 'template.toll_expenses' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.toll_expenses' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['transaction_type'] = [
            '1' => __( 'expenses.toll_usage' ),
            '2' => __( 'expenses.reload' ),
        ];
        $this->data['data']['class'] = [
            '0' => 0,
            '1' => 1,
            '2' => 2,
            '3' => 3,
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allTollExpenses( Request $request ) {

        return TollExpenseService::allTollExpenses( $request );
    }

    public function oneTollExpense( Request $request ) {

        return TollExpenseService::oneTollExpense( $request );
    }

    public function createTollExpense( Request $request ) {

        return TollExpenseService::createTollExpense( $request );
    }

    public function updateTollExpense( Request $request ) {

        return TollExpenseService::updateTollExpense( $request );
    }
}

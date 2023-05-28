<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CompanyService,
    FuelExpenseService,
};

class FuelExpenseController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.fuel_expenses' );
        $this->data['content'] = 'admin.fuel_expense.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.fuel_expenses' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['company'] = [];
        $company = CompanyService::get();
        foreach ( $company as $c ) {
            $this->data['data']['company'][$c['id']] = $c['name'];
        }
        $this->data['data']['station'] = [
            '1' => 'BHP',
            '2' => 'Petron',
            '3' => 'Petronas',
            '4' => 'Shell',
            '5' => 'SURABAYA',
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.fuel_expenses' ) ) ] );
        $this->data['content'] = 'admin.fuel_expense.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.fuel_expense.index' ),
                'text' => __( 'template.fuel_expenses' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.fuel_expenses' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['company'] = CompanyService::get();
        $this->data['data']['station'] = [
            '1' => 'BHP',
            '2' => 'Petron',
            '3' => 'Petronas',
            '4' => 'Shell',
            '5' => 'SURABAYA',
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.fuel_expenses' ) ) ] );
        $this->data['content'] = 'admin.fuel_expense.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.fuel_expense.index' ),
                'text' => __( 'template.fuel_expenses' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.fuel_expenses' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['company'] = CompanyService::get();
        $this->data['data']['station'] = [
            '1' => 'BHP',
            '2' => 'Petron',
            '3' => 'Petronas',
            '4' => 'Shell',
            '5' => 'SURABAYA',
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allFuelExpenses( Request $request ) {

        return FuelExpenseService::allFuelExpenses( $request );
    }

    public function oneFuelExpense( Request $request ) {

        return FuelExpenseService::oneFuelExpense( $request );
    }

    public function createFuelExpense( Request $request ) {

        return FuelExpenseService::createFuelExpense( $request );
    }

    public function updateFuelExpense( Request $request ) {

        return FuelExpenseService::updateFuelExpense( $request );
    }
}

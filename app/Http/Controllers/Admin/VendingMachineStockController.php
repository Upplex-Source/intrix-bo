<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    VendingMachineStockService,
};

use Helper;

class VendingMachineStockController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.vending_machine_stocks' );
        $this->data['content'] = 'admin.vending_machine_stock.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.vending_machine_stocks' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $maxStock = Helper::maxStocks();
        
        $this->data['data']['max_froyo'] = $maxStock['froyo'];
        $this->data['data']['max_topping'] = $maxStock['topping'];
        $this->data['data']['max_syrup'] = $maxStock['syrup'];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vending_machine_stocks' ) ) ] );
        $this->data['content'] = 'admin.vending_machine_stock.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.vending_machine_stock.index' ),
                'text' => __( 'template.vending_machine_stocks' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vending_machine_stocks' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.vending_machine_stocks' ) ) ] );
        $this->data['content'] = 'admin.vending_machine_stock.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.vending_machine_stock.index' ),
                'text' => __( 'template.vending_machine_stocks' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.vending_machine_stocks' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allVendingMachineStocks( Request $request ) {

        return VendingMachineStockService::allVendingMachineStocks( $request );
    }

    public function oneVendingMachineStock( Request $request ) {

        return VendingMachineStockService::oneVendingMachineStock( $request );
    }

    public function createVendingMachineStock( Request $request ) {

        return VendingMachineStockService::createVendingMachineStock( $request );
    }

    public function updateVendingMachineStock( Request $request ) {

        return VendingMachineStockService::updateVendingMachineStock( $request );
    }

    public function updateVendingMachineStockstatus( Request $request ) {

        return VendingMachineStockService::updateVendingMachineStockstatus( $request );
    }

    public function removeVendingMachineStockGalleryImage( Request $request ) {

        return VendingMachineStockService::removeVendingMachineStockGalleryImage( $request );
    }
}

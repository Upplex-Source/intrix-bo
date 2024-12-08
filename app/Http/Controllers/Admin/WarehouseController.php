<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    WarehouseService,
};

class WarehouseController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.warehouses' );
        $this->data['content'] = 'admin.warehouse.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.warehouses' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.warehouses' ) ) ] );
        $this->data['content'] = 'admin.warehouse.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.warehouse.index' ),
                'text' => __( 'template.warehouses' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.warehouses' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.warehouses' ) ) ] );
        $this->data['content'] = 'admin.warehouse.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.warehouse.index' ),
                'text' => __( 'template.warehouses' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.warehouses' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }
    public function warehouseStock( Request $request ) {

        $this->data['header']['title'] = __( 'warehouse.warehouse_stocks' );
        $this->data['content'] = 'admin.warehouse.warehouse_stock';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'warehouse.warehouse_stocks' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        
        $this->data['data']['inventory_type'] = [
            '1' => __( 'warehouse.products' ),
            '2' => __( 'warehouse.bundles' ),
            '3' => __( 'warehouse.variants' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allWarehouses( Request $request ) {

        return WarehouseService::allWarehouses( $request );
    }

    public function oneWarehouse( Request $request ) {

        return WarehouseService::oneWarehouse( $request );
    }

    public function createWarehouse( Request $request ) {

        return WarehouseService::createWarehouse( $request );
    }

    public function updateWarehouse( Request $request ) {

        return WarehouseService::updateWarehouse( $request );
    }

    public function updateWarehouseStatus( Request $request ) {

        return WarehouseService::updateWarehouseStatus( $request );
    }

    public function removeWarehouseGalleryImage( Request $request ) {

        return WarehouseService::removeWarehouseGalleryImage( $request );
    }

    public function oneWarehouseStock( Request $request ) {

        return WarehouseService::oneWarehouseStock( $request );
    }
    
}

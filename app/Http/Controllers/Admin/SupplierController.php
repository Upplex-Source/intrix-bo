<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    SupplierService,
};

class SupplierController extends Controller
{
    public function allSuppliers( Request $request ) {

        return SupplierService::allSuppliers( $request );
    }
}

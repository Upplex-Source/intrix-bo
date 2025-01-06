<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    VendingMachineService,
};

class VendingMachineController extends Controller
{
    /**
     * 1. Get vending machines
     * 
     * @group Vending Machine API
     * 
     * @authenticated
     * 
     * @queryParam title string Vending Machine Title to be filter Example: KL ECOCITY
     * 
     */   
    public function getVendingMachines( Request $request ) {

        return VendingMachineService::getVendingMachines( $request );
    }
}

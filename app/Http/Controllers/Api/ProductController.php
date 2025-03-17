<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ProductAddOnService,
    ProductFreeGiftService,
};

class ProductController extends Controller
{
    /**
     * 1. Get Add Ons 
     * 
     * <aside class="notice">Get all Add on that is in BO</aside>
     * 
     * @authenticated
     * 
     * @group Product API
     * 
     * @bodyParam length integer required The length of the table. Example: 10
     * @bodyParam start integer required The start of the record of the table. Example: 0
     * @bodyParam created_date string The date of the filter. Example: 2024-09-25 to 2024-09-27
     * 
     */
    public function getAddOns( Request $request ) {

        return ProductAddOnService::getAddOns( $request );
    }

    /**
     * 1. Get Free Gifts
     * 
     * <aside class="notice">Get all Add on that is in BO</aside>
     * 
     * @authenticated
     * 
     * @group Product API
     * 
     * @bodyParam length integer required The length of the table. Example: 10
     * @bodyParam start integer required The start of the record of the table. Example: 0
     * @bodyParam created_date string The date of the filter. Example: 2024-09-25 to 2024-09-27
     * 
     */
    public function getFreeGifts( Request $request ) {

        return ProductFreeGiftService::getFreeGifts( $request );
    }
}

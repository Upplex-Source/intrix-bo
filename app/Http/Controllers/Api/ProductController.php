<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ProductService,
    ProductAddOnService,
    ProductFreeGiftService,
};

class ProductController extends Controller
{

    /**
     * 1. Get Products
     * 
     * <aside class="notice">Get all Add on that is in BO</aside>
     * 
     * @group Product API
     * 
     * @bodyParam length integer required The length of the table. Example: 10
     * @bodyParam start integer required The start of the record of the table. Example: 0
     * @bodyParam product_code string required The product_code of the product. Example: 5-IN-1
     * @bodyParam color required strong The color of the product. Example: CHROME
     * 
     * 
     */
    public function getProducts( Request $request ) {

        return ProductService::getProducts( $request );
    }

    /**
     * 2. Get Add Ons 
     * 
     * <aside class="notice">Get all Add on that is in BO</aside>
     * 
     * @group Product API
     * 
     * @bodyParam length integer required The length of the table. Example: 10
     * @bodyParam start integer required The start of the record of the table. Example: 0
     * @bodyParam created_date string The date of the filter. Example: 2024-09-25 to 2024-09-27
     * @bodyParam session_key string required The session_key of the cart. Example: kn1i23onlas1
     * @bodyParam product_code string required The product_code of the product. Example: 5-IN-1
     * 
     * 
     */
    public function getAddOns( Request $request ) {

        return ProductAddOnService::getAddOns( $request );
    }

    /**
     * 3. Get Free Gifts
     * 
     * <aside class="notice">Get all Add on that is in BO</aside>
     * 
     * 
     * @group Product API
     * 
     * @bodyParam length integer required The length of the table. Example: 10
     * @bodyParam start integer required The start of the record of the table. Example: 0
     * @bodyParam created_date string The date of the filter. Example: 2024-09-25 to 2024-09-27
     * @bodyParam session_key string required The session_key of the cart. Example: kn1i23onlas1
     * @bodyParam product_code string required The product_code of the product. Example: 5-IN-1
     * 
     */
    public function getFreeGifts( Request $request ) {

        return ProductFreeGiftService::getFreeGifts( $request );
    }
}

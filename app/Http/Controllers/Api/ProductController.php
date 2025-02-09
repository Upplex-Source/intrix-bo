<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ProductService
};

class ProductController extends Controller
{
    /**
     * 1. Get Products 
     * 
     * <aside class="notice">Get all Product that is in BO</aside>
     * 
     * @authenticated
     * 
     * @group Product API
     * 
     * @queryParam per_page integer Retrieve how many product in a page, default is 10. Example: 10
     * @queryParam promo_code string The Product code to be filter. Example: XBMSD22
     * @queryParam user_Product integer Retrieve all user's Product only Example: 1
     * @queryParam Product_type integer The Product type to be filter Example: 1
     * @queryParam discount_type integer The Product discount type to be filter Example: 2
     * 
     */
    public function getProducts( Request $request ) {

        return ProductService::getProducts( $request );
    }
}

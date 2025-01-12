<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    OrderService,
    DiscountRuleService,
};

class OrderController extends Controller
{
    /**
     * 1. checkout
     * 
     * <strong>payment_method</strong><br>
     * 1: yobe wallet<br>
     * 2: payment gateway<br>
     * 
     * @authenticated
     * 
     * @group Order API
     * 
     * @bodyParam cart integer required The ID of the cart. Example: 1
     * @bodyParam promo_code integer The ID of the promotion to apply. Example: 1
     * @bodyParam payment_method integer The payment Method. Example: 1
     * 
     */
    public function checkout( Request $request ) {

        return OrderService::checkout( $request );
    }

    /**
     * 2. Retrieve user order
     * 
     * <aside class="notice">id and reference can be used to filter out the order</aside>
     * 
     * <strong>status</strong><br>
     * 1: placed order<br>
     * 2: pending payment<br>
     * 3: paid<br>
     * 10: completed<br>
     * 20: canceled<br>
     * 
     * @authenticated
     * 
     * @group Order API
     * 
     * @queryParam reference string The unique reference for the order. Example: abcd-1234
     * @queryParam id string The ID of the order. . Example: abcd-1234
     * @queryParam status integer The Status of the order. . Example: 1
     * @queryParam per_page integer Retrieve how many insurance quote in a page, default is 10. Example: 10
     */
    public function getOrder( Request $request ) {

        return OrderService::getOrder( $request );
    }


}

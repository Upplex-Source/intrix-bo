<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CartService,
    DiscountRuleService,
};

class CartController extends Controller
{
    /**
     * 1. Add to cart
     * 
     * @group Cart API
     * 
     * <strong>payment_plan</strong></br>
     * 1: upfront<br>
     * 2: monthly<br>
     * 3: outright<br>
     * 
     * @bodyParam product_code string required The product_code of the prodcut. Example: 5-IN-1
     * @bodyParam color required strong The color of the product. Example: CHROME
     * @bodyParam quantity required integer The quantity of the product. Example: 1
     * @bodyParam session_key string The session_key of the cart. Required only on second time Example: kn1i23onlas1
     * @bodyParam promo_code integer The ID of the promotion/voucher to apply. Example: BUY1FREE1
     * @bodyParam payment_plan integer nullable The payment_plan integer for the order. Example: 1
     * 
     */
    public function addToCart( Request $request ) {

        return CartService::addToCart( $request );
    }

    /**
     * 2. Retrieve cart 
     * 
     * <aside class="notice">id and session_key can be used to filter out the cart</aside>
     * 
     * 
     * 
     * @group Cart API
     * 
     * @queryParam session_key string The unique identifier for the cart. Example: abcd-1234
     * @queryParam id string The ID of the cart. . Example: abcd-1234
     * @queryParam per_page integer Retrieve how many insurance quote in a page, default is 10. Example: 10
     * 
     */
    public function getCart( Request $request ) {

        return CartService::getCart( $request );
    }

    /**
     * 3. Update cart
     * 
     * <aside class="notice">session_key or cart id can be used to update the cart</aside>
     * 
     * <strong>payment_plan</strong></br>
     * 1: upfront<br>
     * 2: monthly<br>
     * 3: outright<br>
     * 
     * @group Cart API
     * 
     * @bodyParam product_code string required The product_code of the prodcut. Example: 5-IN-1
     * @bodyParam color required strong The color of the product. Example: CHROME
     * @bodyParam quantity required integer The quantity of the product. Example: 1
     * @bodyParam session_key string The session_key of the cart. Required only on second time Example: kn1i23onlas1
     * @bodyParam id integer The ID of the cart. Example: 1
     * @bodyParam cart_item integer The ID of the cart item. Example: 1
     * @bodyParam promo_code integer The ID of the promotion/voucher to apply. Example: BUY1FREE1
     * @bodyParam payment_plan integer nullable The payment_plan integer for the order. Example: 1
     * 
     */
    public function updateCart( Request $request ) {

        return CartService::updateCart( $request );
    }


    /**
     * 4. Delete Cart
     * 
     * <aside class="notice">session_key or cart id can be used to delete the cart</aside>
     * 
     * 
     * @group Cart API
     * 
     * @bodyParam session_key string The session_key of the cart. Required only on second time Example: kn1i23onlas1
     * @bodyParam id integer The ID of the cart. Example: 1
     * @bodyParam session_key string The unique identifier for the cart. Example: abcd-1234
     * 
     */
    public function deleteCart( Request $request ) {

        return CartService::deleteCart( $request );
    }

    /**
     * 4. Delete Cart Item
     * 
     * <aside class="notice">session_key or cart id can be used to delete the cart</aside>
     * 
     * 
     * 
     * @group Cart API
     * 
     * @bodyParam session_key string The session_key of the cart. Required only on second time Example: kn1i23onlas1
     * @bodyParam id integer The ID of the cart. Example: 1
     * @bodyParam cart_item integer The ID of the cart item. Example: 1
     * 
     */
    public function deleteCartItem( Request $request ) {

        return CartService::deleteCartItem( $request );
    }
}

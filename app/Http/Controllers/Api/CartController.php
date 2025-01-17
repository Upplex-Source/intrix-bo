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
     * @authenticated
     * 
     * @group Cart API
     * 
     * @bodyParam vending_machine integer required The ID of the vending_machine. Example: 2
     * @bodyParam items array required The list of products with their ingredients. Example: [{"productId": 1, "froyo": [1, 2], "syrup": [3], "topping": [4, 5]}]
     * @bodyParam items.*.product integer The ID of the product. Pass `null` if no product is selected. Example: 1
     * @bodyParam items.*.froyo array An array of froyo IDs. Pass an empty array if no froyo is selected. Example: [1, 2]
     * @bodyParam items.*.froyo.* integer A froyo ID. Example: 1
     * @bodyParam items.*.syrup array An array of syrup IDs. Pass an empty array if no syrup is selected. Example: [3]
     * @bodyParam items.*.syrup.* integer A syrup ID. Example: 3
     * @bodyParam items.*.topping array An array of topping IDs. Pass an empty array if no topping is selected. Example: [4, 5]
     * @bodyParam items.*.topping.* integer A topping ID. Example: 4
     * @bodyParam promo_code string The ID of the promotion/voucher to apply. Example: BUY1FREE1
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
     * @authenticated
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
     * @authenticated
     * 
     * 
     * @group Cart API
     * 
     * @bodyParam vending_machine integer required The ID of the vending_machine. Example: 2
     * @bodyParam items array required The list of products with their ingredients. Example: [{"productId": 1, "froyo": [1, 2], "syrup": [3], "topping": [4, 5]}]
     * @bodyParam items.*.product integer The ID of the product. Pass `null` if no product is selected. Example: 1
     * @bodyParam items.*.froyo array An array of froyo IDs. Pass an empty array if no froyo is selected. Example: [1, 2]
     * @bodyParam items.*.froyo.* integer A froyo ID. Example: 1
     * @bodyParam items.*.syrup array An array of syrup IDs. Pass an empty array if no syrup is selected. Example: [3]
     * @bodyParam items.*.syrup.* integer A syrup ID. Example: 3
     * @bodyParam items.*.topping array An array of topping IDs. Pass an empty array if no topping is selected. Example: [4, 5]
     * @bodyParam items.*.topping.* integer A topping ID. Example: 4
     * @bodyParam session_key string The unique identifier for the cart. Example: abcd-1234
     * @bodyParam id integer The ID of the cart. Example: 1
     * @bodyParam cart_item integer The ID of the cart item. Example: 1
     * @bodyParam promo_code integer The ID of the promotion/voucher to apply. Example: BUY1FREE1
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
     * @authenticated
     * 
     * @group Cart API
     * 
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
     * @authenticated
     * 
     * 
     * @group Cart API
     * 
     * @bodyParam id integer The ID of the cart. Example: 1
     * @bodyParam cart_item integer The ID of the cart item. Example: 1
     * 
     */
    public function deleteCartItem( Request $request ) {

        return CartService::deleteCartItem( $request );
    }
}

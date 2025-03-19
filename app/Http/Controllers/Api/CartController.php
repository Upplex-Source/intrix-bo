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
     * <strong>color</strong></br>
     * 1: CHROME<br>
     * 2: MATTE BLACK<br>
     * 3: SATIN GOLD<br>
     * 4: GUNMETAL GREY<br>
     * 
     * @queryParam session_key string optional The unique identifier for the cart. Used to add more product to the same cart Example: abcd-1234
     * @bodyParam product_code string required The product_code of the product. Example: 5-IN-1
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
     * <strong>color</strong></br>
     * 1: CHROME<br>
     * 2: MATTE BLACK<br>
     * 3: SATIN GOLD<br>
     * 4: GUNMETAL GREY<br>
     * 
     * @group Cart API
     * 
     * @queryParam session_key string required The unique identifier for the cart. Used to add more product to the update the cart Example: abcd-1234
     * @bodyParam product_code string required The product_code of the product. Example: 5-IN-1
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
     * 5. Delete Cart Item
     * 
     * <aside class="notice">session_key or cart id can be used to delete the cart</aside>
     * 
     * 
     * 
     * @group Cart API
     * 
     * @bodyParam session_key string The session_key of the cart.  kn1i23onlas1
     * @bodyParam id integer The ID of the cart. Example: 1
     * @bodyParam cart_item integer The ID of the cart item. Example: 1
     * 
     */
    public function deleteCartItem( Request $request ) {

        return CartService::deleteCartItem( $request );
    }

    /**
     * 6. Update Cart Addon/FreeGift
     * 
     * <aside class="notice">session_key or cart id can be used to update the cart</aside>
     * 
     * <strong>type</strong></br>
     * 1: add on<br>
     * 2: free gift<br>
     * 
     * @group Cart API
     * 
     * @queryParam session_key string required The unique identifier for the cart. Used to add addon or free gift to the cart Example: abcd-1234
     * @bodyParam add_on string nullable The add_on code get via get add on api. Either `add_on` or `free_gift` must be provided. Example: ADD-ON
     * @bodyParam free_gift string nullable The free_gift code get via get free gift api. Either `add_on` or `free_gift` must be provided. Example: FREE-GIFT
     * @bodyParam quantity required integer The quantity of the addon/quantity. Example: 1, -1 to deduct
     * @bodyParam type required integer The type to be change. Example: 1
     * 
     */
    public function updateCartAddon( Request $request ) {

        return CartService::updateCartAddon( $request );
    }

    /**
     * 7. Update Cart Shipment Details
     * 
     * <aside class="notice">session_key or cart id can be used to update the cart shipment</aside>
     * 
     * <strong>payment_plan</strong></br>
     * 1: upfront<br>
     * 2: monthly<br>
     * 3: outright<br>
     * 
     * 
     * @group Cart API
     * 
     * @queryParam session_key string required The unique identifier for the cart. Used to update shipment details to the cart Example: abcd-1234
     * @bodyParam fullname string nullable The fullname of the guest. Example: Johnny
     * @bodyParam company_name string nullable The company_name of the guest. Example: Johnny holdings
     * @bodyParam email string nullable The email of the guest. Example: johnny@gmail.com
     * @bodyParam phone_number string nullable The phone_number of the guest. Example: 1231231223
     * @bodyParam address_1 string nullable The address_1 of the guest. Example: Lot 4, 1
     * @bodyParam address_2 string nullable The address_2 of the guest. Example: Bandar Baru Bangi
     * @bodyParam city string nullable The city of the guest. Example: Kajang
     * @bodyParam state string nullable The state of the guest. Example: Selangor
     * @bodyParam postcode string nullable The postcode of the guest. Example: 43000
     * @bodyParam country string nullable The country of the guest. Example: Malaysia
     * @bodyParam remarks string nullable The remarks for the order. Example: Be careful
     * @bodyParam payment_plan integer nullable The payment_plan integer for the order. Example: 1
     * 
     */
    public function updateCartAddress( Request $request ) {

        return CartService::updateCartAddress( $request );
    }
}

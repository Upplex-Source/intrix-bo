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
     * <strong>payment_plan</strong></br>
     * 1: upfront<br>
     * 2: monthly<br>
     * 3: outright<br>
     * 
     * <strong>payment_plan</strong></br>
     * 1: CHROME<br>
     * 2: MATTE BLACK<br>
     * 3: SATIN GOLD<br>
     * 3: GUNMETAL GREY<br>
     * 
     * @group Order API
     * 
     * @bodyParam session_key string The session_key of the cart. Example: kn1i23onlas1
     * @bodyParam cart_item integer required The ID of the cart item. Example: 12
     * @bodyParam promo_code integer The ID of the promotion/voucher to apply. Example: BUY1FREE1
     * @bodyParam fullname string required The fullname of the guest. Example: Johnny
     * @bodyParam company_name string nullable The company_name of the guest. Example: Johnny holdings
     * @bodyParam email string required The email of the guest. Example: johnny@gmail.com
     * @bodyParam phone_number string nullable The phone_number of the guest. Example: 1231231223
     * @bodyParam address_1 string required The address_1 of the guest. Example: Lot 4, 1
     * @bodyParam address_2 string nullable The address_2 of the guest. Example: Bandar Baru Bangi
     * @bodyParam city string required The city of the guest. Example: Kajang
     * @bodyParam state string required The state of the guest. Example: Selangor
     * @bodyParam postcode string required The postcode of the guest. Example: 43000
     * @bodyParam country string required The country of the guest. Example: Malaysia
     * @bodyParam remarks string nullable The remarks for the order. Example: Be careful
     * @bodyParam payment_plan integer nullable The payment_plan integer for the order. Example: 1
     * 
     */
    public function cartCheckout( Request $request ) {

        return OrderService::checkout( $request );
    }

    /**
     * 2. direct checkout
     * 
     * <strong>payment_plan</strong></br>
     * 1: upfront<br>
     * 2: monthly<br>
     * 3: outright<br>
     * 
     * <strong>payment_plan</strong></br>
     * 1: CHROME<br>
     * 2: MATTE BLACK<br>
     * 3: SATIN GOLD<br>
     * 3: GUNMETAL GREY<br>
     * 
     * @group Order API
     * 
     * @bodyParam product_code string required The product_code of the prodcut. Example: 5-IN-1
     * @bodyParam color required strong The color of the product. Example: CHROME
     * @bodyParam quantity required integer The quantity of the product. Example: 1
     * @bodyParam promo_code integer The ID of the promotion/voucher to apply. Example: BUY1FREE1
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
    public function directCheckout( Request $request ) {

        return OrderService::directCheckout( $request );
    }

    /**
     * 3. Retrieve user order
     * 
     * <aside class="notice">id and reference can be used to filter out the order</aside>
     * 
     * <strong>status</strong><br>
     * 1: placed order / pending payment<br>
     * 3: paid / unclaimed<br>
     * 10: completed / claimed<br>
     * 20: canceled<br>
     * 
     * 
     * @group Order API
     * 
     * @queryParam reference string The unique reference for the order. Example: abcd-1234
     * @queryParam id integer The ID of the order. . Example: 1
     * @queryParam status integer The Status of the order. . Example: 1
     * @queryParam per_page integer Retrieve how many insurance quote in a page, default is 10. Example: 10
     * 
     */
    public function getOrder( Request $request ) {

        return OrderService::getOrder( $request );
    }

    /**
     * 4. Retry Payment
     * 
     * <aside class="notice">retry payment for online payment</aside>
     * 
     * 
     * @group Order API
     * 
     * @queryParam order_id integer The ID of the order. Example: 1
     */
    public function retryPayment( Request $request ) {

        return OrderService::retryPayment( $request );
    }
    
}

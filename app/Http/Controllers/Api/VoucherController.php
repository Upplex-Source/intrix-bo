<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    VoucherService,
    OrderService
};

class VoucherController extends Controller
{
    /**
     * 1. Get promo code 
     * 
     * <aside class="notice">Get all voucher that is claimable</aside>
     * 
     * <strong>voucher discount type</strong></br>
     * 1: percentage<br>
     * 2: fixed amount<br>
     * 3: buy x free y<br>
     * 
     * @authenticated
     * 
     * @group Promo Code API
     * 
     * @queryParam per_page integer Retrieve how many product in a page, default is 10. Example: 10
     * @queryParam promo_code string The promo code to be filter. Example: XBMSD22
     * @queryParam discount_type integer The promo discount type to be filter Example: 2
     * 
     */
    public function getPromoCode( Request $request ) {

        $request->merge(['voucher_type' => 1, 'user_voucher' => null]);
        return VoucherService::getVouchers( $request );
    }

    /**
     * 2. Validate vouchers
     * 
     * @authenticated
     * 
     * @group Voucher API
     * 
     * @bodyParam cart integer required The cart id Example: 1
     * @bodyParam promo_code string The voucher code to be validate. Example: XBMSD22
     * 
     */
    public function validateVoucher( Request $request ) {

        return VoucherService::validateVoucher( $request );
    }

    /**
     * 3. validate voucher ( Apply Voucher )
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
     * <strong>type</strong></br>
     * 1: BUY NOW<br>
     * 2: CART<br>
     * 
     * @group Promo Code API
     * 
     * @queryParam session_key string optional The unique identifier for the cart. Must be provided if type is 2 Example: abcd-1234
     * @bodyParam type required integer To verify cart or buy now product . Example: 1
     * @bodyParam product_code string required The product_code of the product. Example: 5-IN-1
     * @bodyParam color required strong The color of the product. Example: CHROME
     * @bodyParam quantity required integer The quantity of the product. Example: 1
     * @bodyParam promo_code integer The ID of the promotion/voucher to apply. Example: BUY1FREE1
     * @bodyParam payment_plan integer nullable The payment_plan integer for the order. Example: 1
     * 
     */
    public function validatePromoCode( Request $request ) {

        if( $request->type == 1 ){
            return OrderService::validatePromoCode( $request );
        } else {
            return VoucherService::validateVoucher( $request );
        }
    }
}

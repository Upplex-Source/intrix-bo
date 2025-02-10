<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    VoucherService
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
     * 3. Validate vouchers
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
}

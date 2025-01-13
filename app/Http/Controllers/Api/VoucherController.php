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
     * 1. Get vouchers 
     * <aside class="notice">Get all voucher that is claimable</aside>
     * 
     * <strong>voucher discount type</strong></br>
     * 1: percentage<br>
     * 2: fixed amount<br>
     * 3: buy x free y<br>
     * 
     * <strong>voucher type</strong></br>
     * 1: public voucher<br>
     * 2: points redeemable<br>
     * 3: register reward<br>
     *
     * <strong>user_voucher (retrieve user's voucher)</strong></br>
     * 1: true<br>
     * 2: false<br>
     * 
     * @authenticated
     * 
     * @group Voucher API
     * 
     * @queryParam per_page integer Retrieve how many product in a page, default is 10. Example: 10
     * @bodyParam promo_code string The voucher code to be filter. Example: XBMSD22
     * @bodyParam user_voucher integer Retrieve all user's voucher only Example: 1
     * 
     */
    public function getVouchers( Request $request ) {

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
     * 3. Claim Vouchers
     * 
     * @authenticated
     * 
     * @group Voucher API
     * 
     * @bodyParam voucher_id required string The voucher_id to be claim. Example: 1
     * 
     */
    public function claimVoucher( Request $request ) {

        return VoucherService::claimVoucher( $request );
    }
}

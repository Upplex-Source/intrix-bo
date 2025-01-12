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
     * 
     * <strong>voucher discount type</strong></br>
     * 1: percentage<br>
     * 2: fixed amoun<br>
     * 3: buy x free y<br>
     * 
     * <strong>voucher type</strong></br>
     * 1: public voucher<br>
     * 2: user specific<br>
     * 3: register reward<br>
     * 
     * @authenticated
     * 
     * @group Voucher API
     * 
     * @queryParam per_page integer Retrieve how many product in a page, default is 10. Example: 10
     * @bodyParam promo_code string The voucher code to be filter. Example: XBMSD22
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
}

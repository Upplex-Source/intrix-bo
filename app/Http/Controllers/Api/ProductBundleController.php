<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ProductBundleService,
};

class ProductBundleController extends Controller
{
    /**
     * 1. Get bundles
     * 
     * <aside class="notice">Get all bundles that is buyable</aside>
     * 
     *
     * <strong>user_bundle (retrieve user's bundle)</strong></br>
     * 1: true<br>
     * 2: false<br>
     * 
     * @authenticated
     * 
     * @group Voucher API
     * 
     * @queryParam per_page integer Retrieve how many product in a page, default is 10. Example: 10
     * @queryParam bundle_id integer The bundle id to be filter. Example: 1
     * @queryParam title string The bundle title to be filter. Example: PROMO
     * @queryParam user_bundle integer Retrieve all user's bundle only Example: 1
     * 
     */
    public function getBundles( Request $request ) {

        return ProductBundleService::getBundles( $request );
    }

    /**
     * 2. Buy Bundle
     * 
     * <strong>payment_method</strong><br>
     * 1: yobe wallet<br>
     * 2: payment gateway<br>
     * 
     * @authenticated
     * 
     * @group Voucher API
     * 
     * @bodyParam bundle_id required integer The bundle_id to be claim. Example: 1
     * @bodyParam payment_method integer The payment Method. Example: 1
     * 
     */
    public function buyBundle( Request $request ) {

        return ProductBundleService::buyBundle( $request );
    }
}

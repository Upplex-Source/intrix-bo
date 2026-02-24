<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    OrderService
};

class ContactController extends Controller
{
    /**
     * 1. Contact Us Form Submission
     * 
     * @group Contact-us API
     * 
     * @queryParam full_name string required Fullname of requestor. Example: Adam
     * @queryParam email string required email of requestor. Example: adameve@mail.com
     * @queryParam phone_number string phone number of requestor. Example: 0123878123
     * @queryParam location string location of requestor. Example: Selangor
     * @queryParam model string Model selected by requestor. Example Intrix 5-In-1
     * 
     */
    public function contactUs( Request $request ) {

        return OrderService::contactUs( $request );
    }
}

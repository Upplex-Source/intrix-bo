<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Crypt,
    Hash,
    Http,
    Storage
};

use App\Services\{
    UserService,
};

use App\Models\{
    User,
};

use Helper;

class UserController extends Controller {

    public function __construct() {}

    /**
     * 1. Create an user
     * 
     * 
     * 
     * @group User API
     * 
     * @bodyParam tmp_user string required The temporary user ID during request OTP. Example: eyJpdiI...
     * @bodyParam fullname string required The fullname for register. Example: John Wick
     * @bodyParam email string required The email for register. Example: johnwick@gmail.com
     * @bodyParam otp_code string required The otp for register. Example: 123456
     * @bodyParam password string required The password for register. Example: abcd1234
     * @bodyParam password_confirmation string required The confirmation password. Example: abcd1234
     * 
     */
    public function registerUser( Request $request ) {

        return UserService::registerUser( $request );
    }

    /**
     * 2. Login an user - Email
     * 
     * 
     * @group User API
     * 
     * @bodyParam email string required The email for login. Example: johnwick@mail.com
     * @bodyParam password string required The password for login. Example: abcd1234
     * 
     */
    public function loginUser( Request $request ) {

        return UserService::loginUser( $request );
    }

    /**
     * 3. Request an OTP
     * 
     * <strong>request_type</strong><br>
     * 1: Register<br>
     * 
     * @group User API
     * 
     * @bodyParam email string required The email for login. Example: johnwick@mail.com
     * @bodyParam request_type integer required The request type for OTP. Example: 1
     * 
     */
    public function requestOtp( Request $request ) {

        return UserService::requestOtp( $request );
    }

    /**
     * 4. Resend an OTP
     * 
     * <strong>request_type</strong><br>
     * 2: Resend<br>
     * 
     * @group User API
     * 
     * @bodyParam tmp_user string required The temporary user ID during request OTP. Example: eyJpdiI...
     * @bodyParam email string required The email for login. Example: johnwick@mail.com
     * @bodyParam request_type integer required The request type for OTP. Example: 2
     * 
     */
    public function resendOtp( Request $request ) {

        return UserService::requestOtp( $request );
    }

    /**
     * 5. Get user
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * 
     */ 
    public function getUser( Request $request ) {
        
        return UserService::getUser( $request, 0 );
    }

    /**
     * 6. Update user
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * @bodyParam fullname string required The fullname to update. Example: John Wick
     * @bodyParam fullname string required The fullname to update. Example: John Wick
     * @bodyParam email string required The email to update. Example: jphnwick@gmail.com
     * @bodyParam date_of_birth string required The date of birth to update. Example: 2022-01-01
     * 
     */
    public function updateUser( Request $request ) {

        return UserService::updateUserApi( $request );
    }

    /**
     * 7. Update user password
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * @bodyParam old_password string required The old password of current user. Example: 1234abcd
     * @bodyParam password string required The new password to change. Example: abcd1234
     * @bodyParam password_confirmation string required The confirm password of new password to change. Example: abcd1234
     * 
     */    
    public function updateUserPassword( Request $request ) {

        return UserService::updateUserPassword( $request );
    }

 /**
     * 6. Forgot Password
     * 
     * Request an unique identifier to reset password.
     * 
     * @group User API
     * 
     * @bodyParam email string required Can be email, to perform password reset. Example: johnwick@gmail.com
     * 
     */
    public function forgotPasswordOtp( Request $request ) {

        return UserService::forgotPasswordOtp( $request );
    }

    /**
     * 7. Reset Password
     * 
     * There are 2 steps here,<br>
     * 1. Enter <strong>identifier</strong>, <strong>password</strong> and <strong>password_confirmation</strong> to verify
     * 2. Enter <strong>identifier</strong>, <strong>password</strong>, <strong>password_confirmation</strong> and <strong>otp_code</strong> to reset password
     * 
     * @group User API
     * 
     * @bodyParam email string required Can be email or phone number, to perform password reset. Example: johnwick@gmail.com
     * @bodyParam identifier string required The unique_identifier from forgot password. Example: WLnvrJw6YYK
     * @bodyParam otp_code string The otp code to verify password reset. Example: 123456 
     * @bodyParam password string required The new password to perform password reset. Example: abcd1234
     * @bodyParam password_confirmation string required The new password confirmation to perform password reset. Example: abcd1234
     * 
     */
    public function resetPassword( Request $request ) {

        return UserService::resetPassword( $request );
    }

}
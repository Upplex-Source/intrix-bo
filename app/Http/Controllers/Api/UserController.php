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
     * @sort 1
     * 
     * 
     * 
     * @group User API
     * 
     * @bodyParam identifier string required The temporary user ID during request OTP. Example: eyJpdiI...
     * @bodyParam phone_number string required The phone_number for register. Example: 0123982334
     * @bodyParam otp_code string required The otp for register. Example: 123456
     * @bodyParam password string required The password for register. Example: abcd1234
     * @bodyParam password_confirmation string required The confirmation password. Example: abcd1234
     * @bodyParam invitation_code string The invitation code of referral. Example: AASSCC
     * 
     */
    public function registerUser( Request $request ) {

        return UserService::registerUser( $request );
    }

    /**
     * 2. Login an user - Email
     * @sort 2
     * 
     * 
     * @group User API
     * 
     * @bodyParam phone_number string required The phone_number for login. Example: 0123982334
     * @bodyParam password string required The password for login. Example: abcd1234
     * 
     */
    public function loginUser( Request $request ) {

        return UserService::loginUser( $request );
    }

    /**
     * 3. Request an OTP
     * @sort 3
     * 
     * <strong>request_type</strong><br>
     * 1: Register<br>
     * 
     * @group User API
     * 
     * @bodyParam phone_number string required The phone_number for login. Example: 0123982334
     * @bodyParam request_type integer required The request type for OTP. Example: 1
     * 
     */
    public function requestOtp( Request $request ) {

        return UserService::requestOtp( $request );
    }

    /**
     * 4. Resend an OTP
     * @sort 4
     * 
     * <strong>request_type</strong><br>
     * 2: Resend<br>
     * 
     * @group User API
     * 
     * @bodyParam identifier string required The temporary user ID during request OTP. Example: eyJpdiI...
     * @bodyParam phone_number string required The phone_number for login. Example: 0123982334
     * @bodyParam request_type integer required The request type for OTP. Example: 2
     * 
     */
    public function resendOtp( Request $request ) {

        return UserService::requestOtp( $request );
    }

    /**
     * 5. Get user
     * @sort 5
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
     * @sort 6
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * @bodyParam username string The username to update. Example: John
     * @bodyParam email string The email to update. Example: john@email.com
     * @bodyParam date_of_birth string The date of birth to update. Example: 2022-01-01
     * 
     */
    public function updateUserApi( Request $request ) {

        return UserService::updateUserApi( $request );
    }

    /**
     * 7. Update user password
     * @sort 7
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
     * 9. Forgot Password (Request Otp)
     * @sort 9
     * 
     * Request an unique identifier to reset password.
     * 
     * @group User API
     * 
     * @bodyParam phone_number string required The phone_number for login. Example: 0123982334
     * 
     * 
     */
    public function forgotPasswordOtp( Request $request ) {

        return UserService::forgotPasswordOtp( $request );
    }

    /**
     * 10. Reset Password
     * @sort 10
     * @group User API
     * 
     * @bodyParam phone_number string required The phone_number for login. Example: 0123982334
     * @bodyParam identifier string required The unique_identifier from forgot password. Example: WLnvrJw6YYK
     * @bodyParam otp_code string The otp code to verify password reset. Example: 123456 
     * @bodyParam password string required The new password to perform password reset. Example: abcd1234
     * @bodyParam password_confirmation string required The new password confirmation to perform password reset. Example: abcd1234
     * 
     */
    public function resetPassword( Request $request ) {

        return UserService::resetPassword( $request );
    }

    /**
     * 11. Delete Verification
     * @sort 511
     * 
     * @group User API
     * 
     * @authenticated
     * @bodyParam password string required The password to perform account delete checking. Example: abcd1234
     * 
     * 
     */ 
    public function deleteVerification( Request $request ) {
        
        return UserService::deleteVerification( $request );
    }

    /**
     * 12. Delete Confirm
     * @sort 511
     * 
     * @group User API
     * 
     * @authenticated
     * @bodyParam password string required The password to perform account delete checking. Example: abcd1234
     * 
     * 
     */ 
    public function deleteConfirm( Request $request ) {
        
        return UserService::deleteConfirm( $request );
    }

     /**
     * 13. Get notifications
     * 
     * <strong>is_read</strong><br>
     * 0: New<br>
     * 1: Read<br>
     * 
     * @group User API
     * 
     * @authenticated
     * 
     * @queryParam is_read integer Leave empty for all. Example: 1
     * @queryParam per_page integer Show how many record in a page. Leave blank for default (100). Example: 5
     * @queryParam notification integer required The notification ID of notification. Example: 5
     * 
     */
    public function getNotifications( Request $request ) {

        return UserService::getNotifications( $request );
    }

    /**
     * 14. Update notification seen
     * 
     * @group User API
     * 
     * @bodyParam notification integer required The notification ID of notification. Example: 5
     * 
     */ 
    public function updateNotificationSeen( Request $request ) {

        return UserService::updateNotificationSeen( $request );
    }

}
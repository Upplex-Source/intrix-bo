<?php

use App\Http\Controllers\Api\{
    UserController,
    MailContentController,
    WalletController
};

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* Start Public route */

Route::prefix( 'contact-us' )->group( function() {
    Route::post('/', [MailContentController::class, 'createEnquiryMail']);
} );

Route::post( 'otp', [ UserController::class, 'requestOtp' ] );
Route::post( 'otp/resend', [ UserController::class, 'resendOtp' ] );

Route::prefix( 'users' )->group( function() {
    Route::post( '/', [ UserController::class, 'registerUser' ] );
    Route::post( 'login', [ UserController::class, 'loginUser' ] );
    Route::post( 'check-phone-number', [ UserController::class, 'checkPhoneNumber' ] );
    Route::post( 'forgot-password', [ UserController::class, 'forgotPasswordOtp' ] );
    Route::post( 'reset-password', [ UserController::class, 'resetPassword' ] );

} );

/* End Public route */

/* Start Protected route */

Route::middleware( 'auth:user' )->group( function() {

    Route::prefix( 'users' )->group( function() {
        Route::get( '/', [ UserController::class, 'getUser' ] );
        Route::post( 'delete-verification', [ UserController::class, 'deleteVerification' ] );
        Route::post( 'delete-confirm', [ UserController::class, 'deleteConfirm' ] );
        Route::post( '/update', [ UserController::class, 'updateUserApi' ] );

        if( 1 == 2 ){
            Route::get( '/update-password', [ UserController::class, 'updateUserPassword' ] );
        }
    } );
    
    Route::prefix( 'wallets' )->group( function() {

        Route::get( 'transactions', [ WalletController::class, 'getWalletTransactions' ] );
        Route::post( 'topup', [ WalletController::class, 'topup' ] );
    } );
});

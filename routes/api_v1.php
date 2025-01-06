<?php

use App\Http\Controllers\Api\{
    UserController,
    MailContentController,
    WalletController,
    VendingMachineController,
    MenuController,
    CartController,
    OrderController
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
    
    // New API routes
    Route::prefix( 'vending-machines' )->group( function() {
        Route::get( '/', [ VendingMachineController::class, 'getVendingMachines' ] );
    } );
    
    Route::prefix( 'menus' )->group( function() {
        Route::get( '/', [ MenuController::class, 'getMenus' ] );
        Route::get( 'get-selections', [ MenuController::class, 'getSelections' ] );
        Route::get( 'get-froyos', [ MenuController::class, 'getFroyos' ] );
        Route::get( 'get-syrups', [ MenuController::class, 'getSyrups' ] );
        Route::get( 'get-toppings', [ MenuController::class, 'getToppings' ] );
    } );
    
    Route::prefix( 'carts' )->group( function() {
        Route::post( 'add', [ CartController::class, 'addToCart' ] );
        Route::post( 'update', [ CartController::class, 'updateCart' ] );
        Route::get( '/', [ CartController::class, 'getCart' ] );
        Route::post( 'delete', [ CartController::class, 'deleteCart' ] );
    } );
    
    Route::prefix( 'order' )->group( function() {
        Route::get( '/', [ OrderController::class, 'getOrder' ] );
        Route::get( 'checkout', [ OrderController::class, 'checkout' ] );
    } );
});

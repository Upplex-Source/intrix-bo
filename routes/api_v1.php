<?php

use App\Http\Controllers\Api\{
    UserController,
    MailContentController,
    WalletController,
    VendingMachineController,
    MenuController,
    CartController,
    OrderController,
    VoucherController,
    CheckinController,
    ProductBundleController,
    BlogController,
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
if( 1 == 2 ){
    Route::prefix( 'contact-us' )->group( function() {
        Route::post('/', [MailContentController::class, 'createEnquiryMail']);
    } );
}

Route::prefix( 'carts' )->group( function() {
    Route::post( 'add', [ CartController::class, 'addToCart' ] );
    Route::post( 'update', [ CartController::class, 'updateCart' ] );
    Route::get( '/', [ CartController::class, 'getCart' ] );
    Route::post( 'update-add-on', [ CartController::class, 'updateCartAddon' ] );
    Route::post( 'update-shipment', [ CartController::class, 'updateCartAddress' ] );
    Route::post( 'delete', [ CartController::class, 'deleteCart' ] );
    Route::post( 'delete-cart-item', [ CartController::class, 'deleteCartItem' ] );
} );

Route::prefix( 'orders' )->group( function() {
    Route::get( '/', [ OrderController::class, 'getOrder' ] );
    Route::post( 'cart-checkout', [ OrderController::class, 'cartCheckout' ] );
    Route::post( 'direct-checkout', [ OrderController::class, 'directCheckout' ] );
    Route::post( 'retry-payment', [ OrderController::class, 'retryPayment' ] );
} );

// Route::prefix( 'vouchers' )->group( function() {
//     Route::get( '/', [ VoucherController::class, 'getVouchers' ] );
//     Route::post( '/validate', [ VoucherController::class, 'validateVoucher' ] );
// } );

Route::prefix( 'promo-codes' )->group( function() {
    Route::get( '/', [ VoucherController::class, 'getPromoCode' ] );
    Route::post( 'validate', [ VoucherController::class, 'validatePromoCode' ] );
} );

Route::prefix( 'blogs' )->group( function() {
    Route::any( '/', [ BlogController::class, 'allBlogs' ] );
    Route::any( 'categories', [ BlogController::class, 'getBlogCategories' ] );
    Route::any( '/details', [ BlogController::class, 'oneBlog' ] );
    Route::any( '/one-blog-by-slug', [ BlogController::class, 'oneBlogBySlug' ] );
} );

if( 1 == 2 ){
Route::prefix( 'products' )->group( function() {
    Route::get( '/', [ ProductController::class, 'getProducts' ] );
} );
}

if( 1 == 2 ){
    Route::post( 'otp', [ UserController::class, 'requestOtp' ] );
    Route::post( 'otp/resend', [ UserController::class, 'resendOtp' ] );
    
    Route::prefix( 'users' )->group( function() {
        Route::post( '/', [ UserController::class, 'registerUser' ] );
        Route::post( 'login', [ UserController::class, 'loginUser' ] );
        Route::post( 'check-phone-number', [ UserController::class, 'checkPhoneNumber' ] );
        Route::post( 'forgot-password', [ UserController::class, 'forgotPasswordOtp' ] );
        Route::post( 'reset-password', [ UserController::class, 'resetPassword' ] );

    } );
}

/* End Public route */

/* Start Protected route */
if( 1 == 2 ){
    Route::middleware( 'auth:user' )->group( function() {

        Route::prefix( 'users' )->group( function() {
            Route::get( '/', [ UserController::class, 'getUser' ] );
            Route::post( 'delete-verification', [ UserController::class, 'deleteVerification' ] );
            Route::post( 'delete-confirm', [ UserController::class, 'deleteConfirm' ] );
            Route::post( '/update', [ UserController::class, 'updateUserApi' ] );

            Route::get( 'notifications', [ UserController::class, 'getNotifications' ] );
            Route::post( 'notification', [ UserController::class, 'updateNotificationSeen' ] );

        } );
        
        Route::prefix( 'wallets' )->group( function() {
            Route::get( '', [ WalletController::class, 'getWallet' ] );
            Route::get( 'transactions', [ WalletController::class, 'getWalletTransactions' ] );
            Route::post( 'topup', [ WalletController::class, 'topup' ] );
        } );

        Route::prefix( 'points' )->group( function() {
            Route::get( 'histories', [ WalletController::class, 'getPointsHistories' ] );
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
            Route::post( 'delete-cart-item', [ CartController::class, 'deleteCartItem' ] );
        } );
        
        Route::prefix( 'orders' )->group( function() {
            Route::get( '/', [ OrderController::class, 'getOrder' ] );
            Route::post( 'checkout', [ OrderController::class, 'checkout' ] );
            Route::post( 'retry-payment', [ OrderController::class, 'retryPayment' ] );
        } );

        Route::prefix( 'vouchers' )->group( function() {
            Route::get( '/', [ VoucherController::class, 'getVouchers' ] );
            Route::post( 'claim-voucher', [ VoucherController::class, 'claimVoucher' ] );
            Route::post( '/validate', [ VoucherController::class, 'validateVoucher' ] );
        } );

        Route::prefix( 'promo-codes' )->group( function() {
            Route::get( '/', [ VoucherController::class, 'getPromoCode' ] );
        } );

        Route::prefix( 'checkin' )->group( function() {
            Route::get( '/', [ CheckinController::class, 'getCheckinHistory' ] );
            Route::post( '', [ CheckinController::class, 'checkin' ] );
            Route::get( 'rewards', [ CheckinController::class, 'getCheckinRewards' ] );
        } );

        Route::prefix( 'bundles' )->group( function() {
            Route::get( '/', [ ProductBundleController::class, 'getBundles' ] );
            Route::post( 'buy', [ ProductBundleController::class, 'buyBundle' ] );
            Route::post( 'retry-payment', [ ProductBundleController::class, 'retryPayment' ] );

            Route::post( 'get-added-cup', [ ProductBundleController::class, 'getAddedCup' ] );
            Route::post( 'add-cup', [ ProductBundleController::class, 'addCup' ] );
            Route::post( 'edit-cup', [ ProductBundleController::class, 'editCup' ] );
            Route::post( 'checkout', [ ProductBundleController::class, 'checkout' ] );
        } );
        
    });
}

<?php

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Http\Controllers\Admin\{
    AdministratorController,
    RoleController,
    AuditController,
    BookingController,
    CoreController,
    CustomerController,
    DashboardController,
    EmployeeController,
    FileController,
    ModuleController,
    SettingController,
    UserController,
    GuestController,
    WalletController,
    WalletTransactionController,
    OutletController,
    VendingMachineController,
    FroyoController,
    SyrupController,
    ToppingController,
    ProductController,
    VendingMachineStockController,
    VendingMachineProductController,
    OrderController,
    PaymentController,
    VoucherController,
    UserVoucherController,
    VoucherUsageController,
    UserCheckinController,
    CheckinRewardController,
    ProductBundleController,
    UserBundleController,
    BlogController,
};

use App\Models\{
    Order,
    OrderTransaction,
    ApiLog,
};

use App\Helpers\Helper;

use Carbon\Carbon;

Route::prefix( config( 'services.url.admin_path' ) )->group( function() {

    // Protected Route
    Route::group( [ 'middleware' => [ 'auth:admin' ] ], function() {

        Route::get( 'setup', [ SettingController::class, 'firstSetup' ] )->name( 'admin.first_setup' );
        Route::post( 'settings/setup-mfa', [ SettingController::class, 'setupMFA' ] )->name( 'admin.setupMFA' );
        Route::get( 'verify', [ AdministratorController::class, 'verify' ] )->name( 'admin.verify' );
        Route::post( 'verify-code', [ AdministratorController::class, 'verifyCode' ] )->name( 'admin.verifyCode' );

        Route::post( 'signout', [ AdministratorController::class, 'logout' ] )->name( 'admin.signout' );

        Route::group( [ 'middleware' => [ 'checkAdminIsMFA', 'checkMFA' ] ], function() {

            Route::prefix( 'core' )->group( function() {
                Route::post( 'get-notification-list', [ CoreController::class, 'getNotificationList' ] )->name( 'admin.core.getNotificationList' );
                Route::post( 'seen-notification', [ CoreController::class, 'seenNotification' ] )->name( 'admin.core.seenNotification' );
            } );

            Route::get( '/', function() {
                return redirect()->route( 'admin.dashboard' );
            } )->name( 'admin.home' );

            Route::post( 'file/upload', [ FileController::class, 'upload' ] )->withoutMiddleware( [\App\Http\Middleware\VerifyCsrfToken::class] )->name( 'admin.file.upload' );
            Route::post( 'file/blog-image-upload', [ FileController::class, 'blogUpload' ] )->withoutMiddleware( [\App\Http\Middleware\VerifyCsrfToken::class] )->name( 'admin.file.blogUpload' );
            Route::post( 'file/cke-upload', [ FileController::class, 'ckeUpload' ] )->withoutMiddleware( [\App\Http\Middleware\VerifyCsrfToken::class] )->name( 'admin.file.ckeUpload' );

            Route::prefix( 'dashboard' )->group( function() {
                Route::get( '/', [ DashboardController::class, 'index' ] )->name( 'admin.dashboard' );

                Route::post( '/', [ DashboardController::class, 'getDashboardData' ] )->name( 'admin.dashboard.getDashboardData' );

                Route::post( 'total-revenue-statistics', [ DashboardController::class, 'totalRevenueStatistics' ] )->name( 'admin.dashboard.totalRevenueStatistics' );
                Route::post( 'total-reload-statistics', [ DashboardController::class, 'totalReloadStatistics' ] )->name( 'admin.dashboard.totalReloadStatistics' );
                Route::post( 'total-cups-statistics', [ DashboardController::class, 'totalCupsStatistics' ] )->name( 'admin.dashboard.totalCupsStatistics' );
                Route::post( 'total-user-statistics', [ DashboardController::class, 'totalUserStatistics' ] )->name( 'admin.dashboard.totalUserStatistics' );
            } );

            Route::prefix( 'administrators' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view administrators' ] ], function() {
                    Route::get( '/', [ AdministratorController::class, 'index' ] )->name( 'admin.module_parent.administrator.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add administrators' ] ], function() {
                    Route::get( 'add', [ AdministratorController::class, 'add' ] )->name( 'admin.administrator.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit administrators' ] ], function() {
                    Route::get( 'edit', [ AdministratorController::class, 'edit' ] )->name( 'admin.administrator.edit' );
                } );

                Route::post( 'all-administrators', [ AdministratorController::class, 'allAdministrators' ] )->name( 'admin.administrator.allAdministrators' );
                Route::post( 'one-administrator', [ AdministratorController::class, 'oneAdministrator' ] )->name( 'admin.administrator.oneAdministrator' );
                Route::post( 'create-administrator', [ AdministratorController::class, 'createAdministrator' ] )->name( 'admin.administrator.createAdministrator' );
                Route::post( 'update-administrator', [ AdministratorController::class, 'updateAdministrator' ] )->name( 'admin.administrator.updateAdministrator' );
                Route::post( 'remove-profile-pic', [ AdministratorController::class, 'removeProfilePic' ] )->name( 'admin.administrator.removeProfilePic' );
                
                Route::group( [ 'middleware' => [ 'permission:view administrators' ] ], function() {
                    Route::get( 'salesmen', [ AdministratorController::class, 'indexSalesman' ] )->name( 'admin.module_parent.administrator.indexSalesman' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add administrators' ] ], function() {
                    Route::get( 'salesmen/add', [ AdministratorController::class, 'addSalesman' ] )->name( 'admin.administrator.addSalesman' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit administrators' ] ], function() {
                    Route::get( 'salesmen/edit', [ AdministratorController::class, 'editSalesman' ] )->name( 'admin.administrator.editSalesman' );
                } );

                Route::post( 'all-salesmen', [ AdministratorController::class, 'allSalesmen' ] )->name( 'admin.administrator.allSalesmen' );
                Route::post( 'one-salesman', [ AdministratorController::class, 'oneSalesman' ] )->name( 'admin.administrator.oneSalesman' );
                Route::post( 'create-salesman', [ AdministratorController::class, 'createSalesman' ] )->name( 'admin.administrator.createSalesman' );
                Route::post( 'update-salesman', [ AdministratorController::class, 'updateSalesman' ] )->name( 'admin.administrator.updateSalesman' );
            } );

            Route::prefix( 'roles' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view roles' ] ], function() {
                    Route::get( '/', [ RoleController::class, 'index' ] )->name( 'admin.module_parent.role.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add roles' ] ], function() {
                    Route::get( 'add', [ RoleController::class, 'add' ] )->name( 'admin.role.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit roles' ] ], function() {
                    Route::get( 'edit', [ RoleController::class, 'edit' ] )->name( 'admin.role.edit' );
                } );

                Route::post( 'all-roles', [ RoleController::class, 'allRoles' ] )->name( 'admin.role.allRoles' );
                Route::post( 'one-role', [ RoleController::class, 'oneRole' ] )->name( 'admin.role.oneRole' );
                Route::post( 'create-role', [ RoleController::class, 'createRole' ] )->name( 'admin.role.createRole' );
                Route::post( 'update-role', [ RoleController::class, 'updateRole' ] )->name( 'admin.role.updateRole' );
            } );

            Route::prefix( 'modules' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view modules' ] ], function() {
                    Route::get( '/', [ ModuleController::class, 'index' ] )->name( 'admin.module_parent.module.index' );
                } );

                Route::post( 'all-modules', [ ModuleController::class, 'allModules' ] )->name( 'admin.module.allModules' );
            } );

            Route::prefix( 'audits' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view audits' ] ], function() {
                    Route::get( '/', [ AuditController::class, 'index' ] )->name( 'admin.module_parent.audit.index' );
                } );

                Route::post( 'all-audits', [ AuditController::class, 'allAudits' ] )->name( 'admin.audit.allAudits' );
                Route::post( 'one-audit', [ AuditController::class, 'oneAudit' ] )->name( 'admin.audit.oneAudit' );
            } );

            Route::prefix( 'users' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view users' ] ], function() {
                    Route::get( '/', [ UserController::class, 'index' ] )->name( 'admin.module_parent.user.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add users' ] ], function() {
                    Route::get( 'add', [ UserController::class, 'add' ] )->name( 'admin.user.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit users' ] ], function() {
                    Route::get( 'edit', [ UserController::class, 'edit' ] )->name( 'admin.user.edit' );
                } );

                Route::post( 'all-users', [ UserController::class, 'allUsers' ] )->name( 'admin.user.allUsers' );
                Route::post( 'one-user', [ UserController::class, 'oneUser' ] )->name( 'admin.user.oneUser' );
                Route::post( 'create-user', [ UserController::class, 'createUser' ] )->name( 'admin.user.createUser' );
                Route::post( 'update-user', [ UserController::class, 'updateUser' ] )->name( 'admin.user.updateUser' );
                Route::post( 'update-user-status', [ UserController::class, 'updateUserStatus' ] )->name( 'admin.user.updateUserStatus' );
            } );

            Route::prefix( 'guests' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view guests' ] ], function() {
                    Route::get( '/', [ GuestController::class, 'index' ] )->name( 'admin.module_parent.guest.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add guests' ] ], function() {
                    Route::get( 'add', [ GuestController::class, 'add' ] )->name( 'admin.guest.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit guests' ] ], function() {
                    Route::get( 'edit', [ GuestController::class, 'edit' ] )->name( 'admin.guest.edit' );
                } );

                Route::post( 'all-guests', [ GuestController::class, 'allGuests' ] )->name( 'admin.guest.allGuests' );
                Route::post( 'one-guest', [ GuestController::class, 'oneGuest' ] )->name( 'admin.guest.oneGuest' );
                Route::post( 'create-guest', [ GuestController::class, 'createGuest' ] )->name( 'admin.guest.createGuest' );
                Route::post( 'update-guest', [ GuestController::class, 'updateGuest' ] )->name( 'admin.guest.updateGuest' );
                Route::post( 'update-guest-status', [ GuestController::class, 'updateGuestStatus' ] )->name( 'admin.guest.updateGuestStatus' );
            } );
            
            // new routes ( 23/12 ) 

            Route::prefix( 'products' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view products' ] ], function() {
                    Route::get( '/', [ ProductController::class, 'index' ] )->name( 'admin.module_parent.product.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add products' ] ], function() {
                    Route::get( 'add', [ ProductController::class, 'add' ] )->name( 'admin.product.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit products' ] ], function() {
                    Route::get( 'edit', [ ProductController::class, 'edit' ] )->name( 'admin.product.edit' );
                } );
    
                Route::post( 'all-products', [ ProductController::class, 'allProducts' ] )->name( 'admin.product.allProducts' );
                Route::post( 'one-product', [ ProductController::class, 'oneProduct' ] )->name( 'admin.product.oneProduct' );
                Route::post( 'create-product', [ ProductController::class, 'createProduct' ] )->name( 'admin.product.createProduct' );
                Route::post( 'update-product', [ ProductController::class, 'updateProduct' ] )->name( 'admin.product.updateProduct' );
                Route::post( 'update-product-status', [ ProductController::class, 'updateProductStatus' ] )->name( 'admin.product.updateProductStatus' );
                Route::post( 'remove-product-gallery-image', [ ProductController::class, 'removeProductGalleryImage' ] )->name( 'admin.product.removeProductGalleryImage' );
    
            } );

            Route::prefix( 'orders' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view orders' ] ], function() {
                    Route::get( '/', [ OrderController::class, 'index' ] )->name( 'admin.module_parent.order.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add orders' ] ], function() {
                    Route::get( 'add', [ OrderController::class, 'add' ] )->name( 'admin.order.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit orders' ] ], function() {
                    Route::get( 'edit', [ OrderController::class, 'edit' ] )->name( 'admin.order.edit' );
                } );
    
                Route::post( 'all-orders', [ OrderController::class, 'allOrders' ] )->name( 'admin.order.allOrders' );
                Route::post( 'one-order', [ OrderController::class, 'oneOrder' ] )->name( 'admin.order.oneOrder' );
                Route::post( 'create-order', [ OrderController::class, 'createOrder' ] )->name( 'admin.order.createOrder' );
                Route::post( 'update-order', [ OrderController::class, 'updateOrder' ] )->name( 'admin.order.updateOrder' );
                Route::post( 'update-order-status', [ OrderController::class, 'updateOrderStatus' ] )->name( 'admin.order.updateOrderStatus' );
                Route::post( 'update-order-status-view', [ OrderController::class, 'updateOrderStatusView' ] )->name( 'admin.order.updateOrderStatusView' );

                Route::get( 'scanner', [ OrderController::class, 'scanner' ] )->name( 'admin.order.scanner' );
                Route::post( 'scanned-order', [ OrderController::class, 'scannedOrder' ] )->name( 'admin.order.scannedOrder' );

            } );

            Route::prefix( 'vouchers' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vouchers' ] ], function() {
                    Route::get( '/', [ VoucherController::class, 'index' ] )->name( 'admin.module_parent.voucher.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vouchers' ] ], function() {
                    Route::get( 'add', [ VoucherController::class, 'add' ] )->name( 'admin.voucher.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vouchers' ] ], function() {
                    Route::get( 'edit', [ VoucherController::class, 'edit' ] )->name( 'admin.voucher.edit' );
                } );
    
                Route::post( 'all-vouchers', [ VoucherController::class, 'allVouchers' ] )->name( 'admin.voucher.allVouchers' );
                Route::post( 'one-voucher', [ VoucherController::class, 'oneVoucher' ] )->name( 'admin.voucher.oneVoucher' );
                Route::post( 'create-voucher', [ VoucherController::class, 'createVoucher' ] )->name( 'admin.voucher.createVoucher' );
                Route::post( 'update-voucher', [ VoucherController::class, 'updateVoucher' ] )->name( 'admin.voucher.updateVoucher' );
                Route::post( 'update-voucher-status', [ VoucherController::class, 'updateVoucherStatus' ] )->name( 'admin.voucher.updateVoucherStatus' );
                Route::post( 'remove-voucher-gallery-image', [ VoucherController::class, 'removeVoucherGalleryImage' ] )->name( 'admin.voucher.removeVoucherGalleryImage' );
                Route::post( 'ckeUpload', [ VoucherController::class, 'ckeUpload' ] )->name( 'admin.voucher.ckeUpload' );
            } );

            Route::prefix( 'settings' )->group( function() {

                Route::group( [ 'middleware' => [ 'permission:add settings|view settings|edit settings|delete settings' ] ], function() {
                    Route::get( '/', [ SettingController::class, 'index' ] )->name( 'admin.module_parent.setting.index' );
                } );

                Route::post( 'settings', [ SettingController::class, 'settings' ] )->name( 'admin.setting.settings' );
                Route::post( 'bonus-settings', [ SettingController::class, 'bonusSettings' ] )->name( 'admin.setting.bonusSettings' );
                Route::post( 'maintenance-settings', [ SettingController::class, 'maintenanceSettings' ] )->name( 'admin.setting.maintenanceSettings' );
                Route::post( 'update-bonus-setting', [ SettingController::class, 'updateBonusSetting' ] )->name( 'admin.setting.updateBonusSetting' );
                Route::post( 'update-maintenance-setting', [ SettingController::class, 'updateMaintenanceSetting' ] )->name( 'admin.setting.updateMaintenanceSetting' );
            } );

            Route::prefix( 'user-vouchers' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vouchers' ] ], function() {
                    Route::get( '/', [ UserVoucherController::class, 'index' ] )->name( 'admin.module_parent.user_voucher.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vouchers' ] ], function() {
                    Route::get( 'add', [ UserVoucherController::class, 'add' ] )->name( 'admin.user_voucher.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vouchers' ] ], function() {
                    Route::get( 'edit', [ UserVoucherController::class, 'edit' ] )->name( 'admin.user_voucher.edit' );
                } );
    
                Route::post( 'all-user-vouchers', [ UserVoucherController::class, 'allUserVouchers' ] )->name( 'admin.user_voucher.allUserVouchers' );
                Route::post( 'one-user-voucher', [ UserVoucherController::class, 'oneUserVoucher' ] )->name( 'admin.user_voucher.oneUserVoucher' );
                Route::post( 'create-user-voucher', [ UserVoucherController::class, 'createUserVoucher' ] )->name( 'admin.user_voucher.createUserVoucher' );
                Route::post( 'update-user-voucher', [ UserVoucherController::class, 'updateUserVoucher' ] )->name( 'admin.user_voucher.updateUserVoucher' );
                Route::post( 'update-user-user-voucher-status', [ UserVoucherController::class, 'updateUserVoucherStatus' ] )->name( 'admin.user_voucher.updateUserVoucherStatus' );
                Route::post( 'remove-user-user-voucher-gallery-image', [ UserVoucherController::class, 'removeUserVoucherGalleryImage' ] )->name( 'admin.user_voucher.removeUserVoucherGalleryImage' );
                Route::post( 'ckeUpload', [ UserVoucherController::class, 'ckeUpload' ] )->name( 'admin.user_voucher.ckeUpload' );
            } );

            Route::prefix( 'voucher-usages' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vouchers' ] ], function() {
                    Route::get( '/', [ VoucherUsageController::class, 'index' ] )->name( 'admin.module_parent.voucher_usage.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vouchers' ] ], function() {
                    Route::get( 'add', [ VoucherUsageController::class, 'add' ] )->name( 'admin.voucher_usage.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vouchers' ] ], function() {
                    Route::get( 'edit', [ VoucherUsageController::class, 'edit' ] )->name( 'admin.voucher_usage.edit' );
                } );
    
                Route::post( 'all-voucher-usages', [ VoucherUsageController::class, 'allVoucherUsages' ] )->name( 'admin.voucher_usage.allVoucherUsages' );
                Route::post( 'one-voucher-usage', [ VoucherUsageController::class, 'oneVoucherUsage' ] )->name( 'admin.voucher_usage.oneVoucherUsage' );
                Route::post( 'create-voucher-usage', [ VoucherUsageController::class, 'createVoucherUsage' ] )->name( 'admin.voucher_usage.createVoucherUsage' );
                Route::post( 'update-voucher-usage', [ VoucherUsageController::class, 'updateVoucherUsage' ] )->name( 'admin.voucher_usage.updateVoucherUsage' );
                Route::post( 'update-voucher-usage-status', [ VoucherUsageController::class, 'updateVoucherUsageStatus' ] )->name( 'admin.voucher_usage.updateVoucherUsageStatus' );
                Route::post( 'remove-voucher-usage-gallery-image', [ VoucherUsageController::class, 'removeVoucherUsageGalleryImage' ] )->name( 'admin.voucher_usage.removeVoucherUsageGalleryImage' );
                Route::post( 'ckeUpload', [ VoucherUsageController::class, 'ckeUpload' ] )->name( 'admin.voucher_usage.ckeUpload' );
            } );

            Route::prefix( 'blogs' )->group( function() {

                Route::group( [ 'middleware' => [ 'permission:view blogs' ] ], function() {
                    Route::get( '/', [ BlogController::class, 'index' ] )->name( 'admin.module_parent.blog.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add blogs' ] ], function() {
                    Route::get( 'add', [ BlogController::class, 'add' ] )->name( 'admin.blog.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit blogs' ] ], function() {
                    Route::get( 'edit', [ BlogController::class, 'edit' ] )->name( 'admin.blog.edit' );
                } );

                Route::post( 'allBlogs', [ BlogController::class, 'allBlogs' ] )->name( 'admin.blog.allBlogs' );
                Route::post( 'one-blog', [ BlogController::class, 'oneBlog' ] )->name( 'admin.blog.oneBlog' );
                Route::post( 'create-blog', [ BlogController::class, 'createBlog' ] )->name( 'admin.blog.createBlog' );
                Route::post( 'update-blog', [ BlogController::class, 'updateBlog' ] )->name( 'admin.blog.updateBlog' );
                Route::post( 'copy-blog', [ BlogController::class, 'copyBlog' ] )->name( 'admin.blog.copyBlog' );
                Route::post( 'update-blog-status', [ BlogController::class, 'updateBlogStatus' ] )->name( 'admin.blog.updateBlogStatus' );
                
                Route::post( 'create-blog-category-quick', [ BlogController::class, 'createBlogCategoryQuick' ] )->name( 'admin.blog.createBlogCategoryQuick' );
                Route::post( 'all-blog-categories', [ BlogController::class, 'allBlogCategories' ] )->name( 'admin.blog.allBlogCategories' );


            } );

        } );
        
    } );

    // Public Route
    Route::get( 'lang/{lang}', function( $lang ) {

        if ( array_key_exists( $lang, Config::get( 'languages' ) ) ) {
            Session::put( 'appLocale', $lang );
        }
        return Redirect::back();
    } )->name( 'admin.switchLanguage' );

    Route::get( 'login', [ AdministratorController::class, 'login' ] )->middleware( 'guest:admin' )->name( 'admin.signin' );

    $limiter = config( 'fortify.limiters.login' );

    Route::post( 'login', [ AuthenticatedSessionController::class, 'store' ] )->middleware( array_filter( [ 'guest:admin', $limiter ? 'throttle:'.$limiter : null ] ) )->name( 'admin.login' );

    Route::post( 'logout', [ AuthenticatedSessionController::class, 'destroy' ] )->middleware( 'auth:admin' )->name( 'admin.logout' );
} );

Route::prefix( 'ipay88' )->group( function() {
    Route::get( 'initiate', [ PaymentController::class, 'initEghl' ] )->withoutMiddleware( [ \App\Http\Middleware\VerifyCsrfToken::class ] );
    Route::any( 'notify', [ PaymentController::class, 'notifyEghl' ] )->withoutMiddleware( [ \App\Http\Middleware\VerifyCsrfToken::class ] );
    Route::any( 'query', [ PaymentController::class, 'queryEghl' ] )->withoutMiddleware( [ \App\Http\Middleware\VerifyCsrfToken::class ] );
    Route::any( 'callback', [PaymentController::class, 'callback'] )->name( 'payment.callback' )->withoutMiddleware( [ \App\Http\Middleware\VerifyCsrfToken::class ] );
    Route::get( 'start-payment', [PaymentController::class, 'startPayment'] )->name( 'payment.startPayment' )->withoutMiddleware( [ \App\Http\Middleware\VerifyCsrfToken::class ] );
} );


if( 1 == 2 ){
    Route::prefix('eghl-test')->group(function () {
        Route::get('/', function () {
            $order = Order::latest()->first();
    
            $data = [
                'TransactionType' => 'SALE',
                'PymtMethod' => 'ANY',
                'ServiceID' => config('services.eghl.merchant_id'),
                'PaymentID' => $order->reference . '-' . $order->payment_attempt,
                'OrderNumber' => $order->reference,
                'PaymentDesc' => $order->reference,
                'MerchantName' => 'Yobe Froyo',
                'MerchantReturnURL' => config('services.eghl.staging_callabck_url'),
                'Amount' => $order->total_price,
                'CurrencyCode' => 'MYR',
                'CustIP' => request()->ip(),
                'CustName' => $order->user->username ?? 'Yobe Guest',
                'HashValue' => '',
                'CustEmail' => $order->user->email ?? 'yobeguest@gmail.com',
                'CustPhone' => $order->user->phone_number,
                'MerchantTermsURL' => null,
                'LanguageCode' => 'en',
                'PageTimeout' => '780',
            ];
    
            $data['HashValue'] = Helper::generatePaymentHash($data);
            $url2 = config('services.eghl.test_url') . '?' . http_build_query($data);
    
            $orderTransaction = OrderTransaction::create( [
                'order_id' => $order->id,
                'checkout_id' => null,
                'checkout_url' => null,
                'payment_url' => $url2,
                'transaction_id' => null,
                'layout_version' => 'v1',
                'redirect_url' => null,
                'notify_url' => null,
                'order_no' => $order->reference,
                'order_title' => $order->reference,
                'order_detail' => $order->reference,
                'amount' => $order->total_price,
                'currency' => 'MYR',
                'transaction_type' => 1,
                'status' => 10,
            ] );
    
            $order->payment_url = $url2;
            $order->order_transaction_id = $orderTransaction->id;
            $order->save();
    
            return redirect($url2);
        });
    });
}
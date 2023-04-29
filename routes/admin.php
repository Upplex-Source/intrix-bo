<?php

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Http\Controllers\Admin\{
    FileController,
    DashboardController,
    AdministratorController,
    RoleController,
    ModuleController,
    UserController,
    OrderController,
    VendorController,
};

Route::prefix( config( 'services.url.admin_path' ) )->group( function() {

    // Protected Route
    Route::group( [ 'middleware' => [ 'auth:admin' ] ], function() {

        Route::group( [ 'middleware' => [ 'checkAdminIsMFA', 'checkMFA' ] ], function() {

            Route::post( 'file/upload', [ FileController::class, 'upload' ] )->withoutMiddleware( [\App\Http\Middleware\VerifyCsrfToken::class] )->name( 'admin.file.upload' );

            Route::post( 'signout', [ AdministratorController::class, 'logout' ] )->name( 'admin.signout' );

            Route::prefix( 'dashboard' )->group( function() {
                Route::get( '/', [ DashboardController::class, 'index' ] )->name( 'admin.dashboard' );
            } );

            Route::prefix( 'administrators' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:add administrators|view administrators|edit administrators|delete administrators' ] ], function() {
                    Route::get( '/', [ AdministratorController::class, 'index' ] )->name( 'admin.module_parent.administrator.index' );

                    Route::get( 'add', [ AdministratorController::class, 'add' ] )->name( 'admin.administrator.add' );
                    Route::get( 'edit', [ AdministratorController::class, 'edit' ] )->name( 'admin.administrator.edit' );
                } );

                Route::post( 'all-administrators', [ AdministratorController::class, 'allAdministrators' ] )->name( 'admin.administrator.allAdministrators' );
                Route::post( 'one-administrator', [ AdministratorController::class, 'oneAdministrator' ] )->name( 'admin.administrator.oneAdministrator' );
                Route::post( 'create-administrator', [ AdministratorController::class, 'createAdministrator' ] )->name( 'admin.administrator.createAdministrator' );
                Route::post( 'update-administrator', [ AdministratorController::class, 'updateAdministrator' ] )->name( 'admin.administrator.updateAdministrator' );
            } );

            Route::prefix( 'roles' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:add roles|view roles|edit roles|delete roles' ] ], function() {
                    Route::get( '/', [ RoleController::class, 'index' ] )->name( 'admin.module_parent.role.index' );
                    Route::get( 'add', [ RoleController::class, 'add' ] )->name( 'admin.role.add' );
                    Route::get( 'edit', [ RoleController::class, 'edit' ] )->name( 'admin.role.edit' );
                } );

                Route::post( 'all-roles', [ RoleController::class, 'allRoles' ] )->name( 'admin.role.allRoles' );
                Route::post( 'one-role', [ RoleController::class, 'oneRole' ] )->name( 'admin.role.oneRole' );
                Route::post( 'create-role', [ RoleController::class, 'createRole' ] )->name( 'admin.role.createRole' );
                Route::post( 'update-role', [ RoleController::class, 'updateRole' ] )->name( 'admin.role.updateRole' );
            } );

            Route::prefix( 'modules' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:add modules|view modules|edit modules|delete modules' ] ], function() {
                    Route::get( '/', [ ModuleController::class, 'index' ] )->name( 'admin.module_parent.module.index' );
                } );

                Route::post( 'all-modules', [ ModuleController::class, 'allModules' ] )->name( 'admin.module.allModules' );
            } );

            // Route::prefix( 'users' )->group( function() {
            //     Route::group( [ 'middleware' => [ 'permission:add users|view users|edit users|delete users' ] ], function() {
            //         Route::get( '/', [ UserController::class, 'index' ] )->name( 'admin.module_parent.user.index' );
            //     } );
            // } );

            // Route::prefix( 'orders' )->group( function() {
            //     Route::group( [ 'middleware' => [ 'permission:add orders|view orders|edit orders|delete orders' ] ], function() {
            //         Route::get( '/', [ OrderController::class, 'index' ] )->name( 'admin.module_parent.order.index' );
            //     } );
            // } );
            
            Route::prefix( 'vendors' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:add vendors|view vendors|edit vendors|delete vendors' ] ], function() {
                    Route::get( '/', [ VendorController::class, 'index' ] )->name( 'admin.module_parent.vendor.index' );

                    Route::get( 'add', [ VendorController::class, 'add' ] )->name( 'admin.vendor.add' );
                    Route::get( 'edit', [ VendorController::class, 'edit' ] )->name( 'admin.vendor.edit' );
                } );

                Route::post( 'all-vendors', [ VendorController::class, 'allVendors' ] )->name( 'admin.vendor.allVendors' );
                Route::post( 'one-vendor', [ VendorController::class, 'oneVendor' ] )->name( 'admin.vendor.oneVendor' );
                Route::post( 'create-vendor', [ VendorController::class, 'createVendor' ] )->name( 'admin.vendor.createVendor' );
                Route::post( 'update-vendor', [ VendorController::class, 'updateVendor' ] )->name( 'admin.vendor.updateVendor' );
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

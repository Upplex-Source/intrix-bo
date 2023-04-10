<?php

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Http\Controllers\Admin\{
    DashboardController,
    AdministratorController,
    RoleController,
    ModuleController,
    UserController,
    OrderController,
};

Route::prefix( config( 'services.url.admin_path' ) )->group( function() {

    // Protected Route
    Route::group( [ 'middleware' => [ 'auth:admin' ] ], function() {

        Route::group( [ 'middleware' => [ 'checkAdminIsMFA', 'checkMFA' ] ], function() {

            Route::post( 'signout', [ AdministratorController::class, 'logout' ] )->name( 'admin.signout' );

            Route::prefix( 'dashboard' )->group( function() {
                Route::get( '/', [ DashboardController::class, 'index' ] )->name( 'admin.dashboard' );
            } );

            Route::prefix( 'administrators' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:add administrators|view administrators|edit administrators|delete administrators' ] ], function() {
                    Route::get( '/', [ AdministratorController::class, 'index' ] )->name( 'admin.administrator.index' );
                } );
            } );

            Route::prefix( 'roles' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:add roles|view roles|edit roles|delete roles' ] ], function() {
                    Route::get( '/', [ RoleController::class, 'index' ] )->name( 'admin.role.index' );
                } );
            } );

            Route::prefix( 'modules' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:add modules|view modules|edit modules|delete modules' ] ], function() {
                    Route::get( '/', [ ModuleController::class, 'index' ] )->name( 'admin.module.index' );
                } );
            } );

            Route::prefix( 'users' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:add users|view users|edit users|delete users' ] ], function() {
                    Route::get( '/', [ UserController::class, 'index' ] )->name( 'admin.user.index' );
                } );
            } );

            Route::prefix( 'orders' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:add orders|view orders|edit orders|delete orders' ] ], function() {
                    Route::get( '/', [ OrderController::class, 'index' ] )->name( 'admin.order.index' );
                } );
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

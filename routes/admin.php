<?php

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Http\Controllers\Admin\{
    AdministratorController,
    AuditController,
    BookingController,
    CoreController,
    CustomerController,
    DashboardController,
    EmployeeController,
    FileController,
    InvoiceController,
    ModuleController,
    SettingController,
    FarmController,
    UserController,
    OwnerController,
    OrderController,
    OrderItemController,
    BuyerController,
    CategoryController,
    BrandController,
    SupplierController,
    ProductController,
};

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

            Route::prefix( 'dashboard' )->group( function() {
                Route::get( '/', [ DashboardController::class, 'index' ] )->name( 'admin.dashboard' );

                Route::post( '/', [ DashboardController::class, 'getDashboardData' ] )->name( 'admin.dashboard.getDashboardData' );

                Route::post( 'expenses-statistics', [ DashboardController::class, 'getExpensesStatistics' ] )->name( 'admin.dashboard.getExpensesStatistics' );
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

            Route::prefix( 'farms' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view farms' ] ], function() {
                    Route::get( '/', [ FarmController::class, 'index' ] )->name( 'admin.module_parent.farm.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add farms' ] ], function() {
                    Route::get( 'add', [ FarmController::class, 'add' ] )->name( 'admin.farm.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit farms' ] ], function() {
                    Route::get( 'edit', [ FarmController::class, 'edit' ] )->name( 'admin.farm.edit' );
                } );

                Route::post( 'all-farms', [ FarmController::class, 'allFarms' ] )->name( 'admin.farm.allFarms' );
                Route::post( 'one-farm', [ FarmController::class, 'oneFarm' ] )->name( 'admin.farm.oneFarm' );
                Route::post( 'create-farm', [ FarmController::class, 'createFarm' ] )->name( 'admin.farm.createFarm' );
                Route::post( 'update-farm', [ FarmController::class, 'updateFarm' ] )->name( 'admin.farm.updateFarm' );
                Route::post( 'update-farm-status', [ FarmController::class, 'updateFarmStatus' ] )->name( 'admin.farm.updateFarmStatus' );
                Route::post( 'remove-farm-gallery-image', [ FarmController::class, 'removeFarmGalleryImage' ] )->name( 'admin.farm.removeFarmGalleryImage' );
            } );

            Route::prefix( 'workers' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view employees' ] ], function() {
                    Route::get( '/', [ EmployeeController::class, 'index' ] )->name( 'admin.module_parent.worker.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add employees' ] ], function() {
                    Route::get( 'add', [ EmployeeController::class, 'add' ] )->name( 'admin.worker.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit employees' ] ], function() {
                    Route::get( 'edit', [ EmployeeController::class, 'edit' ] )->name( 'admin.worker.edit' );
                } );

                Route::post( 'all-workers', [ EmployeeController::class, 'allWorkers' ] )->name( 'admin.worker.allWorkers' );
                Route::post( 'one-worker', [ EmployeeController::class, 'oneWorker' ] )->name( 'admin.worker.oneWorker' );
                Route::post( 'create-worker', [ EmployeeController::class, 'createWorker' ] )->name( 'admin.worker.createWorker' );
                Route::post( 'update-worker', [ EmployeeController::class, 'updateWorker' ] )->name( 'admin.worker.updateWorker' );
                Route::post( 'update-worker-status', [ EmployeeController::class, 'updateWorkerStatus' ] )->name( 'admin.worker.updateWorkerStatus' );
                Route::post( 'calculate-birthday', [ EmployeeController::class, 'calculateBirthday' ] )->name( 'admin.worker.calculateBirthday' );
            } );

            Route::prefix( 'customers' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view customers' ] ], function() {
                    Route::get( '/', [ CustomerController::class, 'index' ] )->name( 'admin.module_parent.customer.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add customers' ] ], function() {
                    Route::get( 'add', [ CustomerController::class, 'add' ] )->name( 'admin.customer.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit customers' ] ], function() {
                    Route::get( 'edit', [ CustomerController::class, 'edit' ] )->name( 'admin.customer.edit' );
                } );

                Route::post( 'all-customers', [ CustomerController::class, 'allCustomers' ] )->name( 'admin.customer.allCustomers' );
                Route::post( 'one-customer', [ CustomerController::class, 'oneCustomer' ] )->name( 'admin.customer.oneCustomer' );
                Route::post( 'create-customer', [ CustomerController::class, 'createCustomer' ] )->name( 'admin.customer.createCustomer' );
                Route::post( 'update-customer', [ CustomerController::class, 'updateCustomer' ] )->name( 'admin.customer.updateCustomer' );
                Route::post( 'update-customer-status', [ CustomerController::class, 'updateCustomerStatus' ] )->name( 'admin.customer.updateCustomerStatus' );
            } );

            Route::prefix( 'owners' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view owners' ] ], function() {
                    Route::get( '/', [ OwnerController::class, 'index' ] )->name( 'admin.module_parent.owner.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add owners' ] ], function() {
                    Route::get( 'add', [ OwnerController::class, 'add' ] )->name( 'admin.owner.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit owners' ] ], function() {
                    Route::get( 'edit', [ OwnerController::class, 'edit' ] )->name( 'admin.owner.edit' );
                } );
                
                Route::post( 'all-owners', [ OwnerController::class, 'allOwners' ] )->name( 'admin.owner.allOwners' );
                Route::post( 'one-owner', [ OwnerController::class, 'oneOwner' ] )->name( 'admin.owner.oneOwner' );
                Route::post( 'create-owner', [ OwnerController::class, 'createOwner' ] )->name( 'admin.owner.createOwner' );
                Route::post( 'update-owner', [ OwnerController::class, 'updateOwner' ] )->name( 'admin.owner.updateOwner' );
            } );

            Route::prefix( 'buyers' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view buyers' ] ], function() {
                    Route::get( '/', [ BuyerController::class, 'index' ] )->name( 'admin.module_parent.buyer.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add buyers' ] ], function() {
                    Route::get( 'add', [ BuyerController::class, 'add' ] )->name( 'admin.buyer.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit buyers' ] ], function() {
                    Route::get( 'edit', [ BuyerController::class, 'edit' ] )->name( 'admin.buyer.edit' );
                } );
                
                Route::post( 'all-buyers', [ BuyerController::class, 'allBuyers' ] )->name( 'admin.buyer.allBuyers' );
                Route::post( 'one-buyer', [ BuyerController::class, 'oneBuyer' ] )->name( 'admin.buyer.oneBuyer' );
                Route::post( 'create-buyer', [ BuyerController::class, 'createBuyer' ] )->name( 'admin.buyer.createBuyer' );
                Route::post( 'update-buyer', [ BuyerController::class, 'updateBuyer' ] )->name( 'admin.buyer.updateBuyer' );
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

                Route::group( [ 'middleware' => [ 'permission:view orders' ] ], function() {
                    Route::get( 'sales-report', [ OrderController::class, 'salesReport' ] )->name( 'admin.order.salesReport' );
                } );
                
                Route::group( [ 'middleware' => [ 'permission:view orders' ] ], function() {
                    Route::get( 'export', [ OrderController::class, 'export' ] )->name( 'admin.order.export' );
                } );
                
                Route::post( 'all-orders', [ OrderController::class, 'allOrders' ] )->name( 'admin.order.allOrders' );
                Route::post( 'one-order', [ OrderController::class, 'oneOrder' ] )->name( 'admin.order.oneOrder' );
                Route::post( 'create-order', [ OrderController::class, 'createOrder' ] )->name( 'admin.order.createOrder' );
                Route::post( 'update-order', [ OrderController::class, 'updateOrder' ] )->name( 'admin.order.updateOrder' );
                Route::post( 'update-order-status', [ OrderController::class, 'updateOrderStatus' ] )->name( 'admin.order.updateOrderStatus' );
                Route::get( 'all-sales-report', [ OrderController::class, 'allSalesReport' ] )->name( 'admin.order.allSalesReport' );
            } );

            Route::prefix( 'order-items' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view order-items' ] ], function() {
                    Route::get( '/', [ OrderItemController::class, 'index' ] )->name( 'admin.module_parent.order_items.index' );
                } );
                
                Route::post( 'all-order-items', [ OrderItemController::class, 'allOrderItems' ] )->name( 'admin.order.allOrderItems' );
                Route::post( 'one-order-item', [ OrderItemController::class, 'oneOrderItem' ] )->name( 'admin.order.oneOrderItem' );
            } );

            Route::prefix( 'invoices' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view invoices' ] ], function() {
                    Route::get( '/', [ InvoiceController::class, 'index' ] )->name( 'admin.module_parent.invoice.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit invoices' ] ], function() {
                    Route::get( 'edit', [ InvoiceController::class, 'edit' ] )->name( 'admin.invoice.edit' );
                } );
                
                Route::post( 'all-invoices', [ InvoiceController::class, 'allInvoices' ] )->name( 'admin.invoice.allInvoices' );
                Route::post( 'one-invoice', [ InvoiceController::class, 'oneInvoice' ] )->name( 'admin.invoice.oneInvoice' );
                Route::post( 'create-invoice', [ InvoiceController::class, 'createInvoice' ] )->name( 'admin.invoice.createInvoice' );
                Route::post( 'delete-invoice', [ InvoiceController::class, 'deleteInvoice' ] )->name( 'admin.invoice.deleteInvoice' );
                Route::post( 'update-invoice', [ InvoiceController::class, 'updateInvoice' ] )->name( 'admin.invoice.updateInvoice' );
                Route::get( 'download-invoice', [ InvoiceController::class, 'downloadInvoice' ] )->name( 'admin.invoice.downloadInvoice' );
                Route::get( 'preview-invoice', [ InvoiceController::class, 'previewInvoice' ] )->name( 'admin.invoice.previewInvoice' );
            });

            // new routes
            Route::prefix( 'categories' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view categories' ] ], function() {
                    Route::get( '/', [ CategoryController::class, 'index' ] )->name( 'admin.module_parent.category.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add categories' ] ], function() {
                    Route::get( 'add', [ CategoryController::class, 'add' ] )->name( 'admin.category.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit categories' ] ], function() {
                    Route::get( 'edit', [ CategoryController::class, 'edit' ] )->name( 'admin.category.edit' );
                } );

                Route::post( 'all-categories', [ CategoryController::class, 'allCategories' ] )->name( 'admin.category.allCategories' );
                Route::post( 'one-category', [ CategoryController::class, 'oneCategory' ] )->name( 'admin.category.oneCategory' );
                Route::post( 'create-category', [ CategoryController::class, 'createCategory' ] )->name( 'admin.category.createCategory' );
                Route::post( 'update-category', [ CategoryController::class, 'updateCategory' ] )->name( 'admin.category.updateCategory' );
                Route::post( 'update-category-status', [ CategoryController::class, 'updateCategoryStatus' ] )->name( 'admin.category.updateCategoryStatus' );
                Route::post( 'remove-category-gallery-image', [ CategoryController::class, 'removeCategoryGalleryImage' ] )->name( 'admin.category.removeCategoryGalleryImage' );
            } );

            // new routes
            Route::prefix( 'brands' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view brands' ] ], function() {
                    Route::get( '/', [ BrandController::class, 'index' ] )->name( 'admin.module_parent.brand.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add brands' ] ], function() {
                    Route::get( 'add', [ BrandController::class, 'add' ] )->name( 'admin.brand.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit brands' ] ], function() {
                    Route::get( 'edit', [ BrandController::class, 'edit' ] )->name( 'admin.brand.edit' );
                } );

                Route::post( 'all-brands', [ BrandController::class, 'allBrands' ] )->name( 'admin.brand.allBrands' );
                Route::post( 'one-brand', [ BrandController::class, 'oneBrand' ] )->name( 'admin.brand.oneBrand' );
                Route::post( 'create-brand', [ BrandController::class, 'createBrand' ] )->name( 'admin.brand.createBrand' );
                Route::post( 'update-brand', [ BrandController::class, 'updateBrand' ] )->name( 'admin.brand.updateBrand' );
                Route::post( 'update-brand-status', [ BrandController::class, 'updateBrandStatus' ] )->name( 'admin.brand.updateBrandStatus' );
                Route::post( 'remove-brand-gallery-image', [ BrandController::class, 'removeBrandGalleryImage' ] )->name( 'admin.brand.removeBrandGalleryImage' );
            } );

            // new routes
            Route::prefix( 'suppliers' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view suppliers' ] ], function() {
                    Route::get( '/', [ SupplierController::class, 'index' ] )->name( 'admin.module_parent.supplier.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add suppliers' ] ], function() {
                    Route::get( 'add', [ SupplierController::class, 'add' ] )->name( 'admin.supplier.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit suppliers' ] ], function() {
                    Route::get( 'edit', [ SupplierController::class, 'edit' ] )->name( 'admin.supplier.edit' );
                } );

                Route::post( 'all-suppliers', [ SupplierController::class, 'allSuppliers' ] )->name( 'admin.supplier.allSuppliers' );
                Route::post( 'one-supplier', [ SupplierController::class, 'oneSupplier' ] )->name( 'admin.supplier.oneSupplier' );
                Route::post( 'create-supplier', [ SupplierController::class, 'createSupplier' ] )->name( 'admin.supplier.createSupplier' );
                Route::post( 'update-supplier', [ SupplierController::class, 'updateSupplier' ] )->name( 'admin.supplier.updateSupplier' );
                Route::post( 'update-supplier-status', [ SupplierController::class, 'updateSupplierStatus' ] )->name( 'admin.supplier.updateSupplierStatus' );
                Route::post( 'remove-supplier-gallery-image', [ SupplierController::class, 'removeSupplierGalleryImage' ] )->name( 'admin.supplier.removeSupplierGalleryImage' );
            } );

            // new routes
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

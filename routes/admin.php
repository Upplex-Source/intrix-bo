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
    WalletController,
    WalletTransactionController,
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
                Route::group( [ 'middleware' => [ 'permission:edit products' ] ], function() {
                    Route::get( 'print-barcodes', [ ProductController::class, 'printBarcodes' ] )->name( 'admin.product.printBarcodes' );
                } );

                Route::post( 'all-products', [ ProductController::class, 'allProducts' ] )->name( 'admin.product.allProducts' );
                Route::post( 'one-product', [ ProductController::class, 'oneProduct' ] )->name( 'admin.product.oneProduct' );
                Route::post( 'create-product', [ ProductController::class, 'createProduct' ] )->name( 'admin.product.createProduct' );
                Route::post( 'update-product', [ ProductController::class, 'updateProduct' ] )->name( 'admin.product.updateProduct' );
                Route::post( 'update-product-status', [ ProductController::class, 'updateProductStatus' ] )->name( 'admin.product.updateProductStatus' );
                Route::post( 'remove-product-gallery-image', [ ProductController::class, 'removeProductGalleryImage' ] )->name( 'admin.product.removeProductGalleryImage' );

                Route::post( 'ckeUpload', [ ProductController::class, 'ckeUpload' ] )->name( 'admin.product.ckeUpload' );
                Route::post( 'generate-product-code', [ ProductController::class, 'generateProductCode' ] )->name( 'admin.product.generateProductCode' );
                Route::post( 'generate-barcode', [ ProductController::class, 'generateBarcode' ] )->name( 'admin.product.generateBarcode' );
                Route::post( 'generate-barcodes', [ ProductController::class, 'generateBarcodes' ] )->name( 'admin.product.generateBarcodes' );
                Route::post( 'preview-barcode', [ ProductController::class, 'previewBarcode' ] )->name( 'admin.product.previewBarcode' );

                // for select2
                Route::post( 'all-products-bundles', [ ProductController::class, 'allProductsBundles' ] )->name( 'admin.product.allProductsBundles' );

            } );

            // new routes
            Route::prefix( 'adjustments' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view adjustments' ] ], function() {
                    Route::get( '/', [ AdjustmentController::class, 'index' ] )->name( 'admin.module_parent.adjustment.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add adjustments' ] ], function() {
                    Route::get( 'add', [ AdjustmentController::class, 'add' ] )->name( 'admin.adjustment.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit adjustments' ] ], function() {
                    Route::get( 'edit', [ AdjustmentController::class, 'edit' ] )->name( 'admin.adjustment.edit' );
                } );

                Route::post( 'all-adjustments', [ AdjustmentController::class, 'allAdjustments' ] )->name( 'admin.adjustment.allAdjustments' );
                Route::post( 'one-adjustment', [ AdjustmentController::class, 'oneAdjustment' ] )->name( 'admin.adjustment.oneAdjustment' );
                Route::post( 'create-adjustment', [ AdjustmentController::class, 'createAdjustment' ] )->name( 'admin.adjustment.createAdjustment' );
                Route::post( 'update-adjustment', [ AdjustmentController::class, 'updateAdjustment' ] )->name( 'admin.adjustment.updateAdjustment' );
                Route::post( 'update-adjustment-status', [ AdjustmentController::class, 'updateAdjustmentStatus' ] )->name( 'admin.adjustment.updateAdjustmentStatus' );
                Route::post( 'remove-adjustment-attachment', [ AdjustmentController::class, 'removeAdjustmentAttachment' ] )->name( 'admin.adjustment.removeAdjustmentAttachment' );

            } );

             // new routes
             Route::prefix( 'product-inventories' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view product-inventories' ] ], function() {
                    Route::get( '/', [ ProductInventoryController::class, 'index' ] )->name( 'admin.module_parent.product_inventory.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add product-inventories' ] ], function() {
                    Route::get( 'add', [ ProductInventoryController::class, 'add' ] )->name( 'admin.product_inventory.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit product-inventories' ] ], function() {
                    Route::get( 'edit', [ ProductInventoryController::class, 'edit' ] )->name( 'admin.product_inventory.edit' );
                } );

                Route::post( 'all-product-inventories', [ ProductInventoryController::class, 'allProductInventories' ] )->name( 'admin.product_inventory.allProductInventories' );
                Route::post( 'one-product-inventory', [ ProductInventoryController::class, 'oneProductInventory' ] )->name( 'admin.product_inventory.oneProductInventory' );
                Route::post( 'create-product-inventory', [ ProductInventoryController::class, 'createProductInventory' ] )->name( 'admin.product_inventory.createProductInventory' );
                Route::post( 'update-product-inventory', [ ProductInventoryController::class, 'updateProductInventory' ] )->name( 'admin.product_inventory.updateProductInventory' );
                Route::post( 'update-product-inventory-status', [ ProductInventoryController::class, 'updateProductInventoryStatus' ] )->name( 'admin.product_inventory.updateProductInventoryStatus' );

            } );

            // new routes
            Route::prefix( 'warehouses' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view warehouses' ] ], function() {
                    Route::get( '/', [ WarehouseController::class, 'index' ] )->name( 'admin.module_parent.warehouse.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add warehouses' ] ], function() {
                    Route::get( 'add', [ WarehouseController::class, 'add' ] )->name( 'admin.warehouse.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit warehouses' ] ], function() {
                    Route::get( 'edit', [ WarehouseController::class, 'edit' ] )->name( 'admin.warehouse.edit' );
                } );
                Route::group( [ 'middleware' => [ 'permission:view warehouses' ] ], function() {
                    Route::get( 'stock', [ WarehouseController::class, 'warehouseStock' ] )->name( 'admin.warehouse.warehouseStock' );
                } );

                Route::post( 'all-warehouses', [ WarehouseController::class, 'allWarehouses' ] )->name( 'admin.warehouse.allWarehouses' );
                Route::post( 'one-warehouse', [ WarehouseController::class, 'oneWarehouse' ] )->name( 'admin.warehouse.oneWarehouse' );
                Route::post( 'create-warehouse', [ WarehouseController::class, 'createWarehouse' ] )->name( 'admin.warehouse.createWarehouse' );
                Route::post( 'update-warehouse', [ WarehouseController::class, 'updateWarehouse' ] )->name( 'admin.warehouse.updateWarehouse' );
                Route::post( 'update-warehouse-status', [ WarehouseController::class, 'updateWarehouseStatus' ] )->name( 'admin.warehouse.updateWarehouseStatus' );
                Route::post( 'remove-warehouse-gallery-image', [ WarehouseController::class, 'removeWarehouseGalleryImage' ] )->name( 'admin.warehouse.removeWarehouseGalleryImage' );
                Route::post( 'one-warehouse-stock', [ WarehouseController::class, 'oneWarehouseStock' ] )->name( 'admin.warehouse.oneWarehouseStock' );
            } );

            // new routes
            Route::prefix( 'units' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view units' ] ], function() {
                    Route::get( '/', [ UnitController::class, 'index' ] )->name( 'admin.module_parent.unit.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add units' ] ], function() {
                    Route::get( 'add', [ UnitController::class, 'add' ] )->name( 'admin.unit.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit units' ] ], function() {
                    Route::get( 'edit', [ UnitController::class, 'edit' ] )->name( 'admin.unit.edit' );
                } );

                Route::post( 'all-units', [ UnitController::class, 'allUnits' ] )->name( 'admin.unit.allUnits' );
                Route::post( 'one-unit', [ UnitController::class, 'oneUnit' ] )->name( 'admin.unit.oneUnit' );
                Route::post( 'create-unit', [ UnitController::class, 'createUnit' ] )->name( 'admin.unit.createUnit' );
                Route::post( 'update-unit', [ UnitController::class, 'updateUnit' ] )->name( 'admin.unit.updateUnit' );
                Route::post( 'update-unit-status', [ UnitController::class, 'updateUnitStatus' ] )->name( 'admin.unit.updateUnitStatus' );
            } );

            // new routes
            Route::prefix( 'bundles' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view bundles' ] ], function() {
                    Route::get( '/', [ BundleController::class, 'index' ] )->name( 'admin.module_parent.bundle.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add bundles' ] ], function() {
                    Route::get( 'add', [ BundleController::class, 'add' ] )->name( 'admin.bundle.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit bundles' ] ], function() {
                    Route::get( 'edit', [ BundleController::class, 'edit' ] )->name( 'admin.bundle.edit' );
                } );

                Route::post( 'all-bundles', [ BundleController::class, 'allBundles' ] )->name( 'admin.bundle.allBundles' );
                Route::post( 'one-bundle', [ BundleController::class, 'oneBundle' ] )->name( 'admin.bundle.oneBundle' );
                Route::post( 'create-bundle', [ BundleController::class, 'createBundle' ] )->name( 'admin.bundle.createBundle' );
                Route::post( 'update-bundle', [ BundleController::class, 'updateBundle' ] )->name( 'admin.bundle.updateBundle' );
                Route::post( 'update-bundle-status', [ BundleController::class, 'updateBundleStatus' ] )->name( 'admin.bundle.updateBundleStatus' );
                Route::post( 'remove-bundle-gallery-image', [ BundleController::class, 'removeBundleGalleryImage' ] )->name( 'admin.bundle.removeBundleGalleryImage' );
            } );

            // new routes
            Route::prefix( 'tax-methods' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view tax-methods' ] ], function() {
                    Route::get( '/', [ TaxMethodController::class, 'index' ] )->name( 'admin.module_parent.tax_method.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add tax-methods' ] ], function() {
                    Route::get( 'add', [ TaxMethodController::class, 'add' ] )->name( 'admin.tax_method.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit tax-methods' ] ], function() {
                    Route::get( 'edit', [ TaxMethodController::class, 'edit' ] )->name( 'admin.tax_method.edit' );
                } );

                Route::post( 'all-tax-methods', [ TaxMethodController::class, 'allTaxMethods' ] )->name( 'admin.tax_method.allTaxMethods' );
                Route::post( 'one-tax-method', [ TaxMethodController::class, 'oneTaxMethod' ] )->name( 'admin.tax_method.oneTaxMethod' );
                Route::post( 'create-tax-method', [ TaxMethodController::class, 'createTaxMethod' ] )->name( 'admin.tax_method.createTaxMethod' );
                Route::post( 'update-tax-method', [ TaxMethodController::class, 'updateTaxMethod' ] )->name( 'admin.tax_method.updateTaxMethod' );
                Route::post( 'update-tax-method-status', [ TaxMethodController::class, 'updateTaxMethodStatus' ] )->name( 'admin.tax_method.updateTaxMethodStatus' );
                Route::post( 'remove-tax-method-gallery-image', [ TaxMethodController::class, 'removeTaxMethodGalleryImage' ] )->name( 'admin.tax_method.removeTaxMethodGalleryImage' );
            } );

            // new routes
            Route::prefix( 'workmanships' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view workmanships' ] ], function() {
                    Route::get( '/', [ WorkmanshipController::class, 'index' ] )->name( 'admin.module_parent.workmanship.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add workmanships' ] ], function() {
                    Route::get( 'add', [ WorkmanshipController::class, 'add' ] )->name( 'admin.workmanship.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit workmanships' ] ], function() {
                    Route::get( 'edit', [ WorkmanshipController::class, 'edit' ] )->name( 'admin.workmanship.edit' );
                } );

                Route::post( 'all-workmanships', [ WorkmanshipController::class, 'allWorkmanships' ] )->name( 'admin.workmanship.allWorkmanships' );
                Route::post( 'one-workmanship', [ WorkmanshipController::class, 'oneWorkmanship' ] )->name( 'admin.workmanship.oneWorkmanship' );
                Route::post( 'create-workmanship', [ WorkmanshipController::class, 'createWorkmanship' ] )->name( 'admin.workmanship.createWorkmanship' );
                Route::post( 'update-workmanship', [ WorkmanshipController::class, 'updateWorkmanship' ] )->name( 'admin.workmanship.updateWorkmanship' );
                Route::post( 'update-workmanship-status', [ WorkmanshipController::class, 'updateWorkmanshipStatus' ] )->name( 'admin.workmanship.updateWorkmanshipStatus' );
                Route::post( 'remove-workmanship-gallery-image', [ WorkmanshipController::class, 'removeWorkmanshipGalleryImage' ] )->name( 'admin.workmanship.removeWorkmanshipGalleryImage' );
            } );

            // new routes
            Route::prefix( 'measurement-units' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view measurement-units' ] ], function() {
                    Route::get( '/', [ MeasurementUnitController::class, 'index' ] )->name( 'admin.module_parent.measurement_unit.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add measurement-units' ] ], function() {
                    Route::get( 'add', [ MeasurementUnitController::class, 'add' ] )->name( 'admin.measurement_unit.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit measurement-units' ] ], function() {
                    Route::get( 'edit', [ MeasurementUnitController::class, 'edit' ] )->name( 'admin.measurement_unit.edit' );
                } );

                Route::post( 'all-measurement-units', [ MeasurementUnitController::class, 'allMeasurementUnits' ] )->name( 'admin.measurement_unit.allMeasurementUnits' );
                Route::post( 'one-measurement-unit', [ MeasurementUnitController::class, 'oneMeasurementUnit' ] )->name( 'admin.measurement_unit.oneMeasurementUnit' );
                Route::post( 'create-measurement-unit', [ MeasurementUnitController::class, 'createMeasurementUnit' ] )->name( 'admin.measurement_unit.createMeasurementUnit' );
                Route::post( 'update-measurement-unit', [ MeasurementUnitController::class, 'updateMeasurementUnit' ] )->name( 'admin.measurement_unit.updateMeasurementUnit' );
                Route::post( 'update-measurement-unit-status', [ MeasurementUnitController::class, 'updateMeasurementUnit-Status' ] )->name( 'admin.measurement_unit.updateMeasurementUnitStatus' );
                Route::post( 'remove-measurement-unit-gallery-image', [ MeasurementUnitController::class, 'removeMeasurementUnit-GalleryImage' ] )->name( 'admin.measurement_unit.removeMeasurementUnitGalleryImage' );
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

            Route::prefix( 'purchases' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view purchases' ] ], function() {
                    Route::get( '/', [ PurchaseController::class, 'index' ] )->name( 'admin.module_parent.purchase.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add purchases' ] ], function() {
                    Route::get( 'add', [ PurchaseController::class, 'add' ] )->name( 'admin.purchase.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit purchases' ] ], function() {
                    Route::get( 'edit', [ PurchaseController::class, 'edit' ] )->name( 'admin.purchase.edit' );
                } );
        
                Route::post( 'all-purchases', [ PurchaseController::class, 'allPurchases' ] )->name( 'admin.purchase.allPurchases' );
                Route::post( 'one-purchase', [ PurchaseController::class, 'onePurchase' ] )->name( 'admin.purchase.onePurchase' );
                Route::post( 'create-purchase', [ PurchaseController::class, 'createPurchase' ] )->name( 'admin.purchase.createPurchase' );
                Route::post( 'update-purchase', [ PurchaseController::class, 'updatePurchase' ] )->name( 'admin.purchase.updatePurchase' );
                Route::post( 'update-purchase-status', [ PurchaseController::class, 'updatePurchaseStatus' ] )->name( 'admin.purchase.updatePurchaseStatus' );
                Route::post( 'remove-purchase-attachment', [ PurchaseController::class, 'removePurchaseAttachment' ] )->name( 'admin.purchase.removePurchaseAttachment' );

                Route::post( 'one-purchase-transactions', [ PurchaseController::class, 'onePurchaseTransaction' ] )->name( 'admin.purchase.onePurchaseTransaction' );
                Route::post( 'create-purchase-transaction', [ PurchaseController::class, 'createPurchaseTransaction' ] )->name( 'admin.purchase.createPurchaseTransaction' );
                Route::post( 'update-purchase-transaction', [ PurchaseController::class, 'updatePurchaseTransaction' ] )->name( 'admin.purchase.updatePurchaseTransaction' );
            } );
            
            Route::prefix( 'expenses' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view expenses' ] ], function() {
                    Route::get( '/', [ ExpenseController::class, 'index' ] )->name( 'admin.module_parent.expense.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add expenses' ] ], function() {
                    Route::get( 'add', [ ExpenseController::class, 'add' ] )->name( 'admin.expense.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit expenses' ] ], function() {
                    Route::get( 'edit', [ ExpenseController::class, 'edit' ] )->name( 'admin.expense.edit' );
                } );
        
                Route::post( 'all-expenses', [ ExpenseController::class, 'allExpenses' ] )->name( 'admin.expense.allExpenses' );
                Route::post( 'one-expense', [ ExpenseController::class, 'oneExpense' ] )->name( 'admin.expense.oneExpense' );
                Route::post( 'create-expense', [ ExpenseController::class, 'createExpense' ] )->name( 'admin.expense.createExpense' );
                Route::post( 'update-expense', [ ExpenseController::class, 'updateExpense' ] )->name( 'admin.expense.updateExpense' );
                Route::post( 'update-expense-status', [ ExpenseController::class, 'updateExpenseStatus' ] )->name( 'admin.expense.updateExpenseStatus' );
                Route::post( 'remove-expense-attachment', [ ExpenseController::class, 'removeExpenseAttachment' ] )->name( 'admin.expense.removeExpenseAttachment' );
            } );

            Route::prefix( 'expenses-categories' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view expenses-categories' ] ], function() {
                    Route::get( '/', [ ExpenseCategoryController::class, 'index' ] )->name( 'admin.module_parent.expense_category.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add expenses-categories' ] ], function() {
                    Route::get( 'add', [ ExpenseCategoryController::class, 'add' ] )->name( 'admin.expense_category.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit expenses-categories' ] ], function() {
                    Route::get( 'edit', [ ExpenseCategoryController::class, 'edit' ] )->name( 'admin.expense_category.edit' );
                } );

                Route::post( 'all-expenses-categories', [ ExpenseCategoryController::class, 'allExpenseCategories' ] )->name( 'admin.expense_category.allExpenseCategories' );
                Route::post( 'one-expenses-category', [ ExpenseCategoryController::class, 'oneExpenseCategory' ] )->name( 'admin.expense_category.oneExpenseCategory' );
                Route::post( 'create-expenses-category', [ ExpenseCategoryController::class, 'createExpenseCategory' ] )->name( 'admin.expense_category.createExpenseCategory' );
                Route::post( 'update-expenses-category', [ ExpenseCategoryController::class, 'updateExpenseCategory' ] )->name( 'admin.expense_category.updateExpenseCategory' );
                Route::post( 'update-expense-category-status', [ ExpenseCategoryController::class, 'updateExpenseCategoryStatus' ] )->name( 'admin.expense_category.updateExpenseCategoryStatus' );
                Route::post( 'remove-expense-category-image', [ ExpenseCategoryController::class, 'removeExpenseCategoryGalleryImage' ] )->name( 'admin.expense_category.removeExpenseCategoryGalleryImage' );
            } );

            Route::prefix( 'expenses-accounts' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view expenses-accounts' ] ], function() {
                    Route::get( '/', [ ExpenseAccountController::class, 'index' ] )->name( 'admin.module_parent.expense_account.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add expenses-accounts' ] ], function() {
                    Route::get( 'add', [ ExpenseAccountController::class, 'add' ] )->name( 'admin.expense_account.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit expenses-accounts' ] ], function() {
                    Route::get( 'edit', [ ExpenseAccountController::class, 'edit' ] )->name( 'admin.expense_account.edit' );
                } );

                Route::post( 'all-expenses-accounts', [ ExpenseAccountController::class, 'allExpenseAccounts' ] )->name( 'admin.expense_account.allExpenseAccounts' );
                Route::post( 'one-expenses-account', [ ExpenseAccountController::class, 'oneExpenseAccount' ] )->name( 'admin.expense_account.oneExpenseAccount' );
                Route::post( 'create-expenses-account', [ ExpenseAccountController::class, 'createExpenseAccount' ] )->name( 'admin.expense_account.createExpenseAccount' );
                Route::post( 'update-expenses-account', [ ExpenseAccountController::class, 'updateExpenseAccount' ] )->name( 'admin.expense_account.updateExpenseAccount' );
                Route::post( 'update-expense-account-status', [ ExpenseAccountController::class, 'updateExpenseAccountStatus' ] )->name( 'admin.expense_account.updateExpenseAccountStatus' );
                Route::post( 'remove-expense-account-image', [ ExpenseAccountController::class, 'removeExpenseAccountGalleryImage' ] )->name( 'admin.expense_account.removeExpenseAccountGalleryImage' );
            } );

            Route::prefix( 'wallets' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view wallets' ] ], function() {
                    Route::get( '/', [ WalletController::class, 'index' ] )->name( 'admin.module_parent.wallet.index' );
                } );

                Route::post( 'all-wallets', [ WalletController::class, 'allWallets' ] )->name( 'admin.wallet.allWallets' );
                Route::post( 'one-wallet', [ WalletController::class, 'oneWallet' ] )->name( 'admin.wallet.oneWallet' );
                Route::post( 'update-wallet', [ WalletController::class, 'updateWallet' ] )->name( 'admin.wallet.updateWallet' );
                Route::post( 'update-wallet-multiple', [ WalletController::class, 'updateWalletMultiple' ] )->name( 'admin.wallet.updateWalletMultiple' );
            } );

            
            Route::prefix( 'wallet-transactions' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view wallet_transactions' ] ], function() {
                    Route::get( '/', [ WalletTransactionController::class, 'index' ] )->name( 'admin.module_parent.wallet_transaction.index' );
                } );

                Route::post( 'all-wallet-transactions', [ WalletTransactionController::class, 'allWalletTransactions' ] )->name( 'admin.wallet_transaction.allWalletTransactions' );
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

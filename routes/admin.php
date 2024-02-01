<?php

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Http\Controllers\Admin\{
    AdministratorController,
    AuditController,
    BookingController,
    CompanyController,
    CoreController,
    CustomerController,
    DashboardController,
    DriverController,
    EmployeeController,
    ExpenseController,
    FileController,
    FuelExpenseController,
    InspectionController,
    InvoiceController,
    ModuleController,
    MaintenanceRecordController,
    PartController,
    RoleController,
    ServiceController,
    ServiceReminderController,
    SettingController,
    SupplierController,
    TollExpenseController,
    TyreController,
    VehicleController,
    VendorController,
    FarmController,
    UserController,
    OwnerController,
    OrderController,
    BuyerController,
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

            Route::prefix( 'companies' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view companies' ] ], function() {
                    Route::get( '/', [ CompanyController::class, 'index' ] )->name( 'admin.module_parent.company.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add companies' ] ], function() {
                    Route::get( 'add', [ CompanyController::class, 'add' ] )->name( 'admin.company.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit companies' ] ], function() {
                    Route::get( 'edit', [ CompanyController::class, 'edit' ] )->name( 'admin.company.edit' );
                } );

                Route::post( 'all-companies', [ CompanyController::class, 'allCompanies' ] )->name( 'admin.company.allCompanies' );
                Route::post( 'one-company', [ CompanyController::class, 'oneCompany' ] )->name( 'admin.company.oneCompany' );
                Route::post( 'create-company', [ CompanyController::class, 'createCompany' ] )->name( 'admin.company.createCompany' );
                Route::post( 'update-company', [ CompanyController::class, 'updateCompany' ] )->name( 'admin.company.updateCompany' );
                Route::post( 'update-company-status', [ CompanyController::class, 'updateCompanyStatus' ] )->name( 'admin.company.updateCompanyStatus' );
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
                Route::post( 'all-sales-report', [ OrderController::class, 'allSalesReport' ] )->name( 'admin.order.allSalesReport' );
            } );

            Route::prefix( 'vendors' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vendors' ] ], function() {
                    Route::get( '/', [ VendorController::class, 'index' ] )->name( 'admin.module_parent.vendor.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vendors' ] ], function() {
                    Route::get( 'add', [ VendorController::class, 'add' ] )->name( 'admin.vendor.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vendors' ] ], function() {
                    Route::get( 'edit', [ VendorController::class, 'edit' ] )->name( 'admin.vendor.edit' );
                } );

                Route::post( 'all-vendors', [ VendorController::class, 'allVendors' ] )->name( 'admin.vendor.allVendors' );
                Route::post( 'one-vendor', [ VendorController::class, 'oneVendor' ] )->name( 'admin.vendor.oneVendor' );
                Route::post( 'create-vendor', [ VendorController::class, 'createVendor' ] )->name( 'admin.vendor.createVendor' );
                Route::post( 'update-vendor', [ VendorController::class, 'updateVendor' ] )->name( 'admin.vendor.updateVendor' );
                Route::post( 'update-vendor-status', [ VendorController::class, 'updateVendorStatus' ] )->name( 'admin.vendor.updateVendorStatus' );
            } );

            Route::prefix( 'tyres' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view tyres' ] ], function() {
                    Route::get( '/', [ TyreController::class, 'index' ] )->name( 'admin.module_parent.tyre.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add tyres' ] ], function() {
                    Route::get( 'add', [ TyreController::class, 'add' ] )->name( 'admin.tyre.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit tyres' ] ], function() {
                    Route::get( 'edit', [ TyreController::class, 'edit' ] )->name( 'admin.tyre.edit' );
                } );

                Route::post( 'all-tyres', [ TyreController::class, 'allTyres' ] )->name( 'admin.tyre.allTyres' );
                Route::post( 'one-tyre', [ TyreController::class, 'oneTyre' ] )->name( 'admin.tyre.oneTyre' );
                Route::post( 'create-tyre', [ TyreController::class, 'createTyre' ] )->name( 'admin.tyre.createTyre' );
                Route::post( 'update-tyre', [ TyreController::class, 'updateTyre' ] )->name( 'admin.tyre.updateTyre' );
                Route::post( 'update-tyre-status', [ TyreController::class, 'updateTyreStatus' ] )->name( 'admin.tyre.updateTyreStatus' );
            } );

            Route::prefix( 'parts' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view parts' ] ], function() {
                    Route::get( '/', [ PartController::class, 'index' ] )->name( 'admin.module_parent.part.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add parts' ] ], function() {
                    Route::get( 'add', [ PartController::class, 'add' ] )->name( 'admin.part.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit parts' ] ], function() {
                    Route::get( 'edit', [ PartController::class, 'edit' ] )->name( 'admin.part.edit' );
                } );

                Route::post( 'all-parts', [ PartController::class, 'allParts' ] )->name( 'admin.part.allParts' );
                Route::post( 'one-part', [ PartController::class, 'onePart' ] )->name( 'admin.part.onePart' );
                Route::post( 'create-part', [ PartController::class, 'createPart' ] )->name( 'admin.part.createPart' );
                Route::post( 'update-part', [ PartController::class, 'updatePart' ] )->name( 'admin.part.updatePart' );
                Route::post( 'update-part-status', [ PartController::class, 'updatePartStatus' ] )->name( 'admin.part.updatePartStatus' );
            } );

            Route::prefix( 'vehicles' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view vehicles' ] ], function() {
                    Route::get( '/', [ VehicleController::class, 'index' ] )->name( 'admin.module_parent.vehicle.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add vehicles' ] ], function() {
                    Route::get( 'add', [ VehicleController::class, 'add' ] )->name( 'admin.vehicle.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit vehicles' ] ], function() {
                    Route::get( 'edit', [ VehicleController::class, 'edit' ] )->name( 'admin.vehicle.edit' );
                } );
                Route::group( [ 'middleware' => [ 'permission:view vehicles' ] ], function() {
                    Route::get( 'expiry-list', [ VehicleController::class, 'vehicleExpiryList' ] )->name( 'admin.vehicle.vehicleExpiryList' );
                } );

                Route::group( [ 'middleware' => [ 'permission:view vehicles' ] ], function() {
                    Route::get( 'export', [ VehicleController::class, 'export' ] )->name( 'admin.vehicle.export' );
                } );

                Route::post( 'all-vehicles', [ VehicleController::class, 'allVehicles' ] )->name( 'admin.vehicle.allVehicles' );
                Route::post( 'one-vehicle', [ VehicleController::class, 'oneVehicle' ] )->name( 'admin.vehicle.oneVehicle' );
                Route::post( 'create-vehicle', [ VehicleController::class, 'createVehicle' ] )->name( 'admin.vehicle.createVehicle' );
                Route::post( 'update-vehicle', [ VehicleController::class, 'updateVehicle' ] )->name( 'admin.vehicle.updateVehicle' );
                Route::post( 'update-vehicle-status', [ VehicleController::class, 'updateVehicleStatus' ] )->name( 'admin.vehicle.updateVehicleStatus' );
                
            } );

            Route::prefix( 'maintenance-records' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view maintenance_records' ] ], function() {
                    Route::get( 'service-records', [ MaintenanceRecordController::class, 'serviceRecords' ] )->name( 'admin.module_parent.maintenance_record.serviceRecords' );
                    Route::get( 'tyre-records', [ MaintenanceRecordController::class, 'tyreRecords' ] )->name( 'admin.module_parent.maintenance_record.tyreRecords' );
                    Route::get( 'part-records', [ MaintenanceRecordController::class, 'partRecords' ] )->name( 'admin.module_parent.maintenance_record.partRecords' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add maintenance_records' ] ], function() {
                    Route::get( 'service-records/add', [ MaintenanceRecordController::class, 'addServiceRecord' ] )->name( 'admin.maintenance_record.addServiceRecord' );
                    Route::get( 'tyre-records/add', [ MaintenanceRecordController::class, 'addTyreRecord' ] )->name( 'admin.maintenance_record.addTyreRecord' );
                    Route::get( 'part-records/add', [ MaintenanceRecordController::class, 'addPartRecord' ] )->name( 'admin.maintenance_record.addPartRecord' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit maintenance_records' ] ], function() {
                    Route::get( 'service-records/edit', [ MaintenanceRecordController::class, 'editServiceRecord' ] )->name( 'admin.maintenance_record.editServiceRecord' );
                    Route::get( 'tyre-records/edit', [ MaintenanceRecordController::class, 'editTyreRecord' ] )->name( 'admin.maintenance_record.editTyreRecord' );
                    Route::get( 'part-records/edit', [ MaintenanceRecordController::class, 'editPartRecord' ] )->name( 'admin.maintenance_record.editPartRecord' );
                } );

                Route::post( 'all-service-records', [ MaintenanceRecordController::class, 'allServiceRecords' ] )->name( 'admin.maintenance_record.allServiceRecords' );
                Route::post( 'one-service-record', [ MaintenanceRecordController::class, 'oneServiceRecord' ] )->name( 'admin.maintenance_record.oneServiceRecord' );
                Route::post( 'service-record-validate-item', [ MaintenanceRecordController::class, 'validateItemServiceRecord' ] )->name( 'admin.maintenance_record.validateItemServiceRecord' );
                Route::post( 'create-service-record', [ MaintenanceRecordController::class, 'createServiceRecord' ] )->name( 'admin.maintenance_record.createServiceRecord' );
                Route::post( 'update-service-record', [ MaintenanceRecordController::class, 'updateServiceRecord' ] )->name( 'admin.maintenance_record.updateServiceRecord' );

                Route::post( 'all-tyre-records', [ MaintenanceRecordController::class, 'allTyreRecords' ] )->name( 'admin.maintenance_record.allTyreRecords' );
                Route::post( 'one-tyre-record', [ MaintenanceRecordController::class, 'oneTyreRecord' ] )->name( 'admin.maintenance_record.oneTyreRecord' );
                Route::post( 'tyre-record-validate-item', [ MaintenanceRecordController::class, 'validateItemTyreRecord' ] )->name( 'admin.maintenance_record.validateItemTyreRecord' );
                Route::post( 'create-tyre-record', [ MaintenanceRecordController::class, 'createTyreRecord' ] )->name( 'admin.maintenance_record.createTyreRecord' );
                Route::post( 'update-tyre-record', [ MaintenanceRecordController::class, 'updateTyreRecord' ] )->name( 'admin.maintenance_record.updateTyreRecord' );            

                Route::post( 'all-part-records', [ MaintenanceRecordController::class, 'allPartRecords' ] )->name( 'admin.maintenance_record.allPartRecords' );
                Route::post( 'one-part-record', [ MaintenanceRecordController::class, 'onePartRecord' ] )->name( 'admin.maintenance_record.onePartRecord' );
                Route::post( 'create-part-record', [ MaintenanceRecordController::class, 'createPartRecord' ] )->name( 'admin.maintenance_record.createPartRecord' );
                Route::post( 'update-part-record', [ MaintenanceRecordController::class, 'updatePartRecord' ] )->name( 'admin.maintenance_record.updatePartRecord' );            
            } );

            Route::prefix( 'services' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view services' ] ], function() {
                    Route::get( '/', [ ServiceController::class, 'index' ] )->name( 'admin.module_parent.service.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add services' ] ], function() {
                    Route::get( 'add', [ ServiceController::class, 'add' ] )->name( 'admin.service.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit services' ] ], function() {
                    Route::get( 'edit', [ ServiceController::class, 'edit' ] )->name( 'admin.service.edit' );
                } );

                Route::post( 'all-services', [ ServiceController::class, 'allServices' ] )->name( 'admin.service.allServices' );
                Route::post( 'one-service', [ ServiceController::class, 'oneService' ] )->name( 'admin.service.oneService' );
                Route::post( 'create-service', [ ServiceController::class, 'createService' ] )->name( 'admin.service.createService' );
                Route::post( 'update-service', [ ServiceController::class, 'updateService' ] )->name( 'admin.service.updateService' );
            } );

            Route::prefix( 'service-reminders' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view services' ] ], function() {
                    Route::get( '/', [ ServiceReminderController::class, 'index' ] )->name( 'admin.service_reminder.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add services' ] ], function() {
                    Route::get( 'add', [ ServiceReminderController::class, 'add' ] )->name( 'admin.service_reminder.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit services' ] ], function() {
                    Route::get( 'edit', [ ServiceReminderController::class, 'edit' ] )->name( 'admin.service_reminder.edit' );
                } );

                Route::post( 'all-service-reminders', [ ServiceReminderController::class, 'allServiceReminders' ] )->name( 'admin.service_reminder.allServiceReminders' );
                Route::post( 'one-service-reminder', [ ServiceReminderController::class, 'oneServiceReminder' ] )->name( 'admin.service_reminder.oneServiceReminder' );
                Route::post( 'create-service-reminder', [ ServiceReminderController::class, 'createServiceReminder' ] )->name( 'admin.service_reminder.createServiceReminder' );
                Route::post( 'update-service-reminder', [ ServiceReminderController::class, 'updateServiceReminder' ] )->name( 'admin.service_reminder.updateServiceReminder' );
            } );

            Route::prefix( 'bookings' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view bookings' ] ], function() {
                    Route::get( 'calendar', [ BookingController::class, 'calendar' ] )->name( 'admin.booking.calendar' );
                    Route::get( '/', [ BookingController::class, 'index' ] )->name( 'admin.module_parent.booking.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add bookings' ] ], function() {
                    Route::get( 'add', [ BookingController::class, 'add' ] )->name( 'admin.booking.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit bookings' ] ], function() {
                    Route::get( 'edit', [ BookingController::class, 'edit' ] )->name( 'admin.booking.edit' );
                } );
                Route::group( [ 'middleware' => [ 'permission:export bookings' ] ], function() {
                    Route::get( 'export', [ BookingController::class, 'export' ] )->name( 'admin.booking.export' );
                } );

                Route::post( 'calendar-all-bookings', [ BookingController::class, 'calendarAllBookings' ] )->name( 'admin.booking.calendarAllBookings' );

                Route::post( 'all-bookings', [ BookingController::class, 'allBookings' ] )->name( 'admin.booking.allBookings' );
                Route::post( 'one-booking', [ BookingController::class, 'oneBooking' ] )->name( 'admin.booking.oneBooking' );
                Route::post( 'create-booking', [ BookingController::class, 'createBooking' ] )->name( 'admin.booking.createBooking' );
                Route::post( 'update-booking', [ BookingController::class, 'updateBooking' ] )->name( 'admin.booking.updateBooking' );
                Route::post( 'update-booking-status', [ BookingController::class, 'updateBookingStatus' ] )->name( 'admin.booking.updateBookingStatus' );
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

            Route::prefix( 'expenses' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view expenses' ] ], function() {
                    Route::get( '/', [ ExpenseController::class, 'index' ] )->name( 'admin.module_parent.expense.index' );
                } );
            } );

            Route::prefix( 'fuel-expenses' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view expenses' ] ], function() {
                    Route::get( '/', [ FuelExpenseController::class, 'index' ] )->name( 'admin.fuel_expense.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add expenses' ] ], function() {
                    Route::get( 'add', [ FuelExpenseController::class, 'add' ] )->name( 'admin.fuel_expense.add' );
                    Route::get( 'import', [ FuelExpenseController::class, 'import' ] )->name( 'admin.fuel_expense.import' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit expenses' ] ], function() {
                    Route::get( 'edit', [ FuelExpenseController::class, 'edit' ] )->name( 'admin.fuel_expense.edit' );
                } );

                Route::post( 'all-fuel-expenses', [ FuelExpenseController::class, 'allFuelExpenses' ] )->name( 'admin.fuel_expense.allFuelExpenses' );
                Route::post( 'one-fuel-expense', [ FuelExpenseController::class, 'oneFuelExpense' ] )->name( 'admin.fuel_expense.oneFuelExpense' );
                Route::post( 'create-fuel-expense', [ FuelExpenseController::class, 'createFuelExpense' ] )->name( 'admin.fuel_expense.createFuelExpense' );
                Route::post( 'update-fuel-expense', [ FuelExpenseController::class, 'updateFuelExpense' ] )->name( 'admin.fuel_expense.updateFuelExpense' );
                Route::post( 'import-fuel-expense', [ FuelExpenseController::class, 'importFuelExpense' ] )->name( 'admin.fuel_expense.importFuelExpense' );
            } );

            Route::prefix( 'toll-expenses' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view expenses' ] ], function() {
                    Route::get( '/', [ TollExpenseController::class, 'index' ] )->name( 'admin.toll_expense.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add expenses' ] ], function() {
                    Route::get( 'add', [ TollExpenseController::class, 'add' ] )->name( 'admin.toll_expense.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit expenses' ] ], function() {
                    Route::get( 'edit', [ TollExpenseController::class, 'edit' ] )->name( 'admin.toll_expense.edit' );
                } );

                Route::post( 'all-toll-expenses', [ TollExpenseController::class, 'allTollExpenses' ] )->name( 'admin.toll_expense.allTollExpenses' );
                Route::post( 'one-toll-expense', [ TollExpenseController::class, 'oneTollExpense' ] )->name( 'admin.toll_expense.oneTollExpense' );
                Route::post( 'create-toll-expense', [ TollExpenseController::class, 'createTollExpense' ] )->name( 'admin.toll_expense.createTollExpense' );
                Route::post( 'update-toll-expense', [ TollExpenseController::class, 'updateTollExpense' ] )->name( 'admin.toll_expense.updateTollExpense' );
            } );

            Route::prefix( 'suppliers' )->group( function() {

                Route::post( 'all-suppliers', [ SupplierController::class, 'allSuppliers' ] )->name( 'admin.supplier.allSuppliers' );
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

<?php

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Http\Controllers\Admin\{
    AdministratorController,
    AuditController,
    BookingController,
    DashboardController,
    DriverController,
    EmployeeController,
    ExpenseController,
    FileController,
    FuelExpenseController,
    InspectionController,
    ModuleController,
    MaintenanceRecordController,
    RoleController,
    ServiceController,
    ServiceReminderController,
    SettingController,
    TollExpenseController,
    VehicleController,
    VendorController,
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

            Route::prefix( 'employees' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view employees' ] ], function() {
                    Route::get( '/', [ EmployeeController::class, 'index' ] )->name( 'admin.module_parent.employee.index' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add employees' ] ], function() {
                    Route::get( 'add', [ EmployeeController::class, 'add' ] )->name( 'admin.employee.add' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit employees' ] ], function() {
                    Route::get( 'edit', [ EmployeeController::class, 'edit' ] )->name( 'admin.employee.edit' );
                } );

                Route::post( 'all-employees', [ EmployeeController::class, 'allEmployees' ] )->name( 'admin.employee.allEmployees' );
                Route::post( 'one-driver', [ EmployeeController::class, 'oneEmployee' ] )->name( 'admin.employee.oneEmployee' );
                Route::post( 'create-driver', [ EmployeeController::class, 'createEmployee' ] )->name( 'admin.employee.createEmployee' );
                Route::post( 'update-driver', [ EmployeeController::class, 'updateEmployee' ] )->name( 'admin.employee.updateEmployee' );
                Route::post( 'update-driver-status', [ EmployeeController::class, 'updateEmployeeStatus' ] )->name( 'admin.employee.updateEmployeeStatus' );
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

                Route::post( 'all-vehicles', [ VehicleController::class, 'allVehicles' ] )->name( 'admin.vehicle.allVehicles' );
                Route::post( 'one-vehicle', [ VehicleController::class, 'oneVehicle' ] )->name( 'admin.vehicle.oneVehicle' );
                Route::post( 'create-vehicle', [ VehicleController::class, 'createVehicle' ] )->name( 'admin.vehicle.createVehicle' );
                Route::post( 'update-vehicle', [ VehicleController::class, 'updateVehicle' ] )->name( 'admin.vehicle.updateVehicle' );
                Route::post( 'update-vehicle-status', [ VehicleController::class, 'updateVehicleStatus' ] )->name( 'admin.vehicle.updateVehicleStatus' );
            } );

            Route::prefix( 'maintenance-records' )->group( function() {
                Route::group( [ 'middleware' => [ 'permission:view maintenance_records' ] ], function() {
                    Route::get( 'service-records', [ MaintenanceRecordController::class, 'serviceRecords' ] )->name( 'admin.module_parent.maintenance_record.serviceRecord' );
                } );
                Route::group( [ 'middleware' => [ 'permission:add maintenance_records' ] ], function() {
                    Route::get( 'service-records/add', [ MaintenanceRecordController::class, 'addServiceRecord' ] )->name( 'admin.maintenance_record.addServiceRecord' );
                } );
                Route::group( [ 'middleware' => [ 'permission:edit maintenance_records' ] ], function() {
                    Route::get( 'service-records/edit', [ MaintenanceRecordController::class, 'editServiceRecord' ] )->name( 'admin.maintenance_record.editServiceRecord' );
                } );

                Route::post( 'all-service-records', [ MaintenanceRecordController::class, 'allServiceRecords' ] )->name( 'admin.maintenance_record.allServiceRecords' );
                Route::post( 'one-service-record', [ MaintenanceRecordController::class, 'oneServiceRecord' ] )->name( 'admin.maintenance_record.oneServiceRecord' );
                Route::post( 'service-record-validate-item', [ MaintenanceRecordController::class, 'validateItemServiceRecord' ] )->name( 'admin.maintenance_record.validateItemServiceRecord' );
                Route::post( 'create-service-record', [ MaintenanceRecordController::class, 'createServiceRecord' ] )->name( 'admin.maintenance_record.createServiceRecord' );
                Route::post( 'update-service-record', [ MaintenanceRecordController::class, 'updateServiceRecord' ] )->name( 'admin.maintenance_record.updateServiceRecord' );
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

                Route::post( 'all-bookings', [ BookingController::class, 'allBookings' ] )->name( 'admin.booking.allBookings' );
                Route::post( 'one-booking', [ BookingController::class, 'oneBooking' ] )->name( 'admin.booking.oneBooking' );
                Route::post( 'create-booking', [ BookingController::class, 'createBooking' ] )->name( 'admin.booking.createBooking' );
                Route::post( 'update-booking', [ BookingController::class, 'updateBooking' ] )->name( 'admin.booking.updateBooking' );
                Route::post( 'update-booking-status', [ BookingController::class, 'updateBookingStatus' ] )->name( 'admin.booking.updateBookingStatus' );
            } );

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
                } );
                Route::group( [ 'middleware' => [ 'permission:edit expenses' ] ], function() {
                    Route::get( 'edit', [ FuelExpenseController::class, 'edit' ] )->name( 'admin.fuel_expense.edit' );
                } );

                Route::post( 'all-fuel-expenses', [ FuelExpenseController::class, 'allFuelExpenses' ] )->name( 'admin.fuel_expense.allFuelExpenses' );
                Route::post( 'one-fuel-expense', [ FuelExpenseController::class, 'oneFuelExpense' ] )->name( 'admin.fuel_expense.oneFuelExpense' );
                Route::post( 'create-fuel-expense', [ FuelExpenseController::class, 'createFuelExpense' ] )->name( 'admin.fuel_expense.createFuelExpense' );
                Route::post( 'update-fuel-expense', [ FuelExpenseController::class, 'updateFuelExpense' ] )->name( 'admin.fuel_expense.updateFuelExpense' );
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

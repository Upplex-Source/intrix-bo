            <!-- sidebar @s -->
            <div class="nk-sidebar nk-sidebar-fixed is-light " data-content="sidebarMenu">
                <div class="nk-sidebar-element nk-sidebar-head">
                    <div class="nk-sidebar-brand">
                        <a href="{{ route( 'admin.home' ) }}" class="logo-link nk-sidebar-logo">
                            <img class="logo-dark logo-img" src="{{ asset( 'admin/images/logo.png' ) }}" srcset="{{ asset( 'admin/images/logo.png' ) }} 2x" alt="logo-dark">
                            <img class="logo-small logo-img logo-img-small" src="{{ asset( 'admin/images/logo.png' ) }}" srcset="{{ asset( 'admin/images/logo.png' ) }} 2x" alt="logo-small">
                        </a>
                    </div>
                    <div class="nk-menu-trigger me-n2">
                        <a href="#" class="nk-nav-toggle nk-quick-nav-icon d-xl-none" data-target="sidebarMenu"><em class="icon ni ni-arrow-left"></em></a>
                        <a href="#" class="nk-nav-compact nk-quick-nav-icon d-none d-xl-inline-flex" data-target="sidebarMenu"><em class="icon ni ni-menu"></em></a>
                    </div>
                </div><!-- .nk-sidebar-element -->
                <div class="nk-sidebar-element">
                    <div class="nk-sidebar-content">
                        <div class="nk-sidebar-menu" data-simplebar>
                            <ul class="nk-menu">
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\DashboardController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.dashboard' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-growth-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.dashboard' ) }}</span>
                                        @if ( 1 == 2 )<span class="nk-menu-badge">HOT</span>@endif
                                    </a>
                                </li>
                                @can( 'view administrators' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\AdministratorController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.administrator.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user-list-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.administrators' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view roles' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\RoleController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.role.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user-list-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.roles' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view audits' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\AuditController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.audit.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-db-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.audit_logs' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                <li class="nk-menu-heading">
                                    <h6 class="overline-title text-primary-alt">{{ __( 'template.operations' ) }}</h6>
                                </li>
                                {{-- New module starts here --}}
                                @can( 'view Users' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\UserController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.user.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-user-group-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.users' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view Salesmen' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\AdministratorController' && in_array( $action, [ 'indexSalesman', 'editSalesman', 'addSalesman' ] ) ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.administrator.indexSalesman' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-user-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.salesmen' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view Categories' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\CategoryController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.category.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-list-thumb-alt-fill"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.categories' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                @can( 'view Brands' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\BrandController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.brand.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-property"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.brands' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                @can( 'view Suppliers' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\SupplierController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.supplier.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-truck"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.suppliers' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                @can( 'view Warehouse' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\WarehouseController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.warehouse.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-inbox"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.warehouses' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                
                                @can( 'view TaxMethods' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\TaxMethodController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.tax_method.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-coin-alt"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.tax_methods' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                @can( 'view Workmanships' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\WorkmanshipController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.workmanship.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-user-add"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.workmanships' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                @can( 'view MeasurementUnits' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\MeasurementUnitController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.measurement_unit.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-move"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.measurement_units' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view products' )

                                <li class="nk-menu-item has-sub {{ ($controller == 'App\Http\Controllers\Admin\ProductController' || $controller == 'App\Http\Controllers\Admin\BundleController') ? 'active current-page' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-list-round"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.products' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ProductController' && in_array( $action, [ 'index', 'edit' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.product.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.products' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\BundleController' && in_array( $action, [ 'index', 'edit' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.bundle.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.bundles' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ProductController' && in_array( $action, [ 'printBarcodes' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.product.printBarcodes' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.generate_barcodes' ) }}</span></a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan
                                @can( 'view Adjustments' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\AdjustmentController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.adjustment.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-sign-dash-alt"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.adjustments' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view Purchases' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\PurchaseController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.purchase.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-list-check"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.purchases' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can( 'view expenses' )

                                <li class="nk-menu-item has-sub {{ ($controller == 'App\Http\Controllers\Admin\ExpenseController' || $controller == 'App\Http\Controllers\Admin\ExpenseAccountController' || $controller == 'App\Http\Controllers\Admin\ExpenseCategoryController') ? 'active current-page' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-coin-alt"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.expenses' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ExpenseController' && in_array( $action, [ 'index', 'edit' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.expense.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.expenses' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ExpenseAccountController' && in_array( $action, [ 'index', 'edit' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.expense_account.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.expenses_accounts' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ExpenseCategoryController' && in_array( $action, [ 'index', 'edit' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.expense_category.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.expenses_categories' ) }}</span></a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan
                                
                                @can( 'view Quotations' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\QuotationController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.quotation.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-property"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.quotations' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                @can( 'view SalesOrders' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\SalesOrderController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.sales_order.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-property"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.sales_orders' ) }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @if( 1 == 2 )
                                @can( 'view Services' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ServiceController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.product.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-setting-alt"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.settings' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                @endif

                            </ul><!-- .nk-menu -->
                        </div><!-- .nk-sidebar-menu -->
                    </div><!-- .nk-sidebar-content -->
                </div><!-- .nk-sidebar-element -->
            </div>
            <!-- sidebar @e -->
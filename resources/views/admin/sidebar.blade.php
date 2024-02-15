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
                                @if ( 1 == 2 )
                                <li class="nk-menu-item has-sub">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-users-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.administrators' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item">
                                            <a href="#" class="nk-menu-link"><span class="nk-menu-text">List</span></a>
                                        </li>
                                        <li class="nk-menu-item">
                                            <a href="#" class="nk-menu-link"><span class="nk-menu-text">Roles</span></a>
                                        </li>
                                        <li class="nk-menu-item">
                                            <a href="#" class="nk-menu-link"><span class="nk-menu-text">Modules</span></a>
                                        </li>
                                    </ul><!-- .nk-menu-sub -->
                                </li>
                                @endif
                                @can( 'view administrators' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\AdministratorController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.administrator.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user-list-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.administrators' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @if ( 1 == 2 )
                                @can( 'view roles' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\RoleController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.role.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-swap-alt-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.roles' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view modules' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ModuleController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.module.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-puzzle-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.modules' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @endif
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
                                @if ( 1 == 2 )
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.module_parent.user.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user-list-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.users' ) }}</span>
                                    </a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.module_parent.order.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-cc-alt2-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.orders' ) }}</span>
                                    </a>
                                </li>
                                @endif
                                @can( 'view farms' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\FarmController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.farm.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-slack"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.farms' ) }}</span>
                                    </a>
                                </li>
                                @endif
                                @can( 'view employees' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\EmployeeController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.worker.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.workers' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view owners' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\OwnerController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.owner.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user-list-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.owners' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view buyers' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\BuyerController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.buyer.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-sign-dollar"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.buyers' ) }}</span>
                                    </a>
                                </li>
                                @endcan

                                @can( 'view orders' )
                                <li class="nk-menu-item has-sub {{ str_contains($controller, 'App\Http\Controllers\Admin\Order') ? 'active current-page' : '' }}">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-setting-alt"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.orders' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\OrderController' && in_array( $action, [ 'salesReport' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.order.salesReport' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.sales_report' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\OrderController' && in_array( $action, [ 'index', 'add', 'edit' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.order.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.order_list' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\OrderItemController' && in_array( $action, [ 'index', 'add', 'edit' ] ) ? 'active current-page' : '' }}">
                                            <a href="{{ route( 'admin.module_parent.order_items.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.order_item_list' ) }}</span></a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan
                                @if ( 1 == 2 ) 
                                @can( 'view orders' )
                                <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\OrderController' ? 'active current-page' : '' }}">
                                    <a href="{{ route( 'admin.module_parent.order.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-setting-alt"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.orders' ) }}</span>
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
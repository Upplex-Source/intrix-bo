            <!-- sidebar @s -->
            <div class="nk-sidebar nk-sidebar-fixed is-light " data-content="sidebarMenu">
                <div class="nk-sidebar-element nk-sidebar-head">
                    <div class="nk-sidebar-brand">
                        <a href="{{ route( 'admin.home' ) }}" class="logo-link nk-sidebar-logo">
                            <img class="logo-dark logo-img" src="{{ asset( 'admin/images/jjk.png' ) }}" srcset="{{ asset( 'admin/images/jjk.png' ) }} 2x" alt="logo-dark">
                            <img class="logo-small logo-img logo-img-small" src="{{ asset( 'admin/images/jjk-small.png' ) }}" srcset="{{ asset( 'admin/images/jjk-small.png' ) }} 2x" alt="logo-small">
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
                                <li class="nk-menu-item">
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
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.module_parent.administrator.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user-list-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.administrators' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view roles' )
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.module_parent.role.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-swap-alt-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.roles' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view modules' )
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.module_parent.module.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-puzzle-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.modules' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view audits' )
                                <li class="nk-menu-item">
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
                                @can( 'view employees' )
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.module_parent.employee.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.employees' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view vendors' )
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.module_parent.vendor.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-briefcase"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.vendors' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view vehicles' )
                                @if ( 1 == 2 )
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.module_parent.vehicle.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-truck"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.vehicles' ) }}</span>
                                    </a>
                                </li>
                                @endif
                                <li class="nk-menu-item has-sub">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-truck"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.vehicles' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item">
                                            <a href="{{ route( 'admin.module_parent.vehicle.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.vehicle_list' ) }}</span></a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan
                                @can( 'view services' )
                                <li class="nk-menu-item has-sub">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-setting-alt"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.services' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        <li class="nk-menu-item">
                                            <a href="{{ route( 'admin.module_parent.service.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.service_list' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item">
                                            <a href="{{ route( 'admin.service_reminder.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.service_reminder_list' ) }}</span></a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan
                                @can( 'view booking' )
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.module_parent.booking.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-check-c"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.bookings' ) }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can( 'view expenses' )
                                <li class="nk-menu-item has-sub">
                                    <a href="#" class="nk-menu-link nk-menu-toggle">
                                        <span class="nk-menu-icon"><em class="icon ni ni-sign-usd"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.expenses' ) }}</span>
                                    </a>
                                    <ul class="nk-menu-sub">
                                        @if ( 1 == 2 )
                                        <li class="nk-menu-item">
                                            <a href="{{ route( 'admin.module_parent.expense.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.expenses_list' ) }}</span></a>
                                        </li>
                                        @endif
                                        <li class="nk-menu-item">
                                            <a href="{{ route( 'admin.fuel_expense.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.fuel_expenses' ) }}</span></a>
                                        </li>
                                        <li class="nk-menu-item">
                                            <a href="{{ route( 'admin.toll_expense.index' ) }}" class="nk-menu-link"><span class="nk-menu-text">{{ __( 'template.toll_expenses' ) }}</span></a>
                                        </li>
                                    </ul>
                                </li>
                                @endcan
                            </ul><!-- .nk-menu -->
                        </div><!-- .nk-sidebar-menu -->
                    </div><!-- .nk-sidebar-content -->
                </div><!-- .nk-sidebar-element -->
            </div>
            <!-- sidebar @e -->
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
                                @can( 'view Categories' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\CategoryController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.category.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-slack"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.categories' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                @can( 'view Brands' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\BrandController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.brand.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-slack"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.brands' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                @can( 'view Suppliers' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\SupplierController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.supplier.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-slack"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.suppliers' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                @can( 'view Products' )
                                    <li class="nk-menu-item {{ $controller == 'App\Http\Controllers\Admin\ProductController' ? 'active current-page' : '' }}">
                                        <a href="{{ route( 'admin.module_parent.product.index' ) }}" class="nk-menu-link">
                                            <span class="nk-menu-icon"><em class="icon ni ni-slack"></em></span>
                                            <span class="nk-menu-text">{{ __( 'template.products' ) }}</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul><!-- .nk-menu -->
                        </div><!-- .nk-sidebar-menu -->
                    </div><!-- .nk-sidebar-content -->
                </div><!-- .nk-sidebar-element -->
            </div>
            <!-- sidebar @e -->
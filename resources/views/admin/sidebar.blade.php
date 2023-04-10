            <!-- sidebar @s -->
            <div class="nk-sidebar nk-sidebar-fixed is-light " data-content="sidebarMenu">
                <div class="nk-sidebar-element nk-sidebar-head">
                    <div class="nk-sidebar-brand">
                        <a href="html/index.html" class="logo-link nk-sidebar-logo">
                            <img class="logo-dark logo-img" src="{{ asset( 'admin/images/default.png' ) }}" srcset="{{ asset( 'admin/images/default.png' ) }}" alt="logo-dark">
                            <img class="logo-small logo-img logo-img-small" src="{{ asset( 'admin/images/default-small.png' ) }}" srcset="{{ asset( 'admin/images/default-small2x.png' ) }} 2x" alt="logo-small">
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
                                </li><!-- .nk-menu-item -->
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
                                </li><!-- .nk-menu-item -->
                                @endif
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.administrator.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user-list-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.administrators' ) }}</span>
                                    </a>
                                </li><!-- .nk-menu-item -->
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.role.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-swap-alt-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.roles' ) }}</span>
                                    </a>
                                </li><!-- .nk-menu-item -->
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.module.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-puzzle-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.modules' ) }}</span>
                                    </a>
                                </li><!-- .nk-menu-item -->
                                <li class="nk-menu-heading">
                                    <h6 class="overline-title text-primary-alt">{{ __( 'template.operations' ) }}</h6>
                                </li><!-- .nk-menu-item -->
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.user.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-user-list-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.users' ) }}</span>
                                    </a>
                                </li><!-- .nk-menu-item -->
                                <li class="nk-menu-item">
                                    <a href="{{ route( 'admin.order.index' ) }}" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-cc-alt2-fill"></em></span>
                                        <span class="nk-menu-text">{{ __( 'template.orders' ) }}</span>
                                    </a>
                                </li><!-- .nk-menu-item -->
                            </ul><!-- .nk-menu -->
                        </div><!-- .nk-sidebar-menu -->
                    </div><!-- .nk-sidebar-content -->
                </div><!-- .nk-sidebar-element -->
            </div>
            <!-- sidebar @e -->
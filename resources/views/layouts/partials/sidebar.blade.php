<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
    data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <a href="{{ route('drive.index') }}">
            <img alt="{{ config('app.name') }}" src="{{ asset('assets/media/logos/default-dark.svg') }}"
                class="h-25px app-sidebar-logo-default" />
            <img alt="{{ config('app.name') }}" src="{{ asset('assets/media/logos/default-small-dark.svg') }}"
                class="h-20px app-sidebar-logo-minimize" />
        </a>
    </div>

    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
                data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo"
                data-kt-scroll-wrappers="#kt_app_sidebar_menu_wrapper" data-kt-scroll-offset="5px">
                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                    data-kt-menu="true" data-kt-menu-expand="false">
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('drive.*') ? 'active' : '' }}"
                            href="{{ route('drive.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-folder fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">{{ __('ui.nav.drive') }}</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('connections.google.*') ? 'active' : '' }}"
                            href="{{ route('connections.google.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-google fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">{{ __('ui.nav.google_accounts') }}</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                            href="{{ route('profile.edit') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-user fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">{{ __('ui.nav.profile') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@php
    /** @var \App\Models\User|null $authUser */
    $authUser = auth()->user();
    $driveConnections = $authUser
        ? $authUser->driveConnections()->orderByDesc('is_default')->orderBy('google_account_email')->get()
        : collect();
    $activeConn = $activeDriveConnection
        ?? $driveConnections->firstWhere('is_default', true)
        ?? $driveConnections->first();

    $userInitial = $authUser?->name
        ? mb_strtoupper(mb_substr($authUser->name, 0, 1))
        : __('ui.common.unknown_initial');

    $locales = [
        'en' => ['label' => __('ui.locale.en'), 'flag' => 'united-states.svg'],
        'tr' => ['label' => __('ui.locale.tr'), 'flag' => 'turkey.svg'],
    ];
    $activeLocale = app()->getLocale();
    if (! array_key_exists($activeLocale, $locales)) {
        $activeLocale = 'en';
    }
    $orderedLocales = collect($locales)->sortBy(fn (array $meta) => mb_strtolower($meta['label']))->all();
    $resolvedPageTitle = is_string($pageTitle ?? null) && trim((string) $pageTitle) !== ''
        ? (string) $pageTitle
        : config('app.name');
@endphp

<div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}"
    data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}"
    data-kt-sticky-animation="false">
    <div class="app-container container-fluid d-flex align-items-stretch justify-content-between"
        id="kt_app_header_container">
        <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2"
            title="{{ __('ui.nav.toggle_sidebar') }}">
            <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
                <i class="ki-duotone ki-abstract-14 fs-2 fs-md-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
        </div>

        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-1">
            <a href="{{ route('drive.index') }}" class="d-lg-none me-3">
                <img alt="{{ config('app.name') }}" src="{{ asset('assets/media/logos/default-small.svg') }}"
                    class="h-30px" />
            </a>

            <div class="d-none d-lg-flex align-items-center">
                <h1 class="fs-3 fw-bold text-gray-900 mb-0">{{ $resolvedPageTitle }}</h1>
            </div>
        </div>

        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
            <div class="d-flex align-items-center ms-auto">
                <div class="app-navbar-item ms-1 ms-lg-3" id="kt_header_google_accounts_menu">
                    <button type="button"
                        class="btn btn-icon btn-custom btn-active-light btn-active-color-primary w-35px h-35px"
                        data-kt-menu-trigger="click" data-kt-menu-attach="parent"
                        data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-element-11 fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </button>

                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold py-4 fs-6 w-325px"
                        data-kt-menu="true">
                        <div class="menu-item px-3">
                            <div class="menu-content d-flex align-items-center px-3">
                                <div class="d-flex flex-column">
                                    <div class="fw-bold d-flex align-items-center fs-5">
                                        {{ __('ui.nav.google_accounts') }}
                                    </div>
                                    <div class="text-muted fs-7">
                                        {{ $activeConn?->google_account_email ?? __('drive.no_active_account') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="separator my-2"></div>

                        @forelse ($driveConnections as $conn)
                            <div class="menu-item px-3">
                                <a href="{{ route('drive.connection', $conn) }}"
                                    class="menu-link px-5 d-flex justify-content-between">
                                    <span class="text-gray-800">{{ $conn->name }}</span>
                                    @if ($conn->is_default)
                                        <span class="badge badge-light-primary">{{ __('ui.common.default') }}</span>
                                    @endif
                                </a>
                            </div>
                        @empty
                            <div class="menu-item px-3">
                                <div class="menu-content px-5 text-muted">
                                    {{ __('drive.no_connected_accounts') }}
                                </div>
                            </div>
                        @endforelse

                        <div class="separator my-2"></div>

                        <div class="menu-item px-3">
                            <a href="{{ route('connections.google.index') }}" class="menu-link px-5">
                                {{ __('drive.manage_accounts') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="app-navbar-item ms-1 ms-lg-3" id="kt_header_language_menu">
                    <button type="button"
                        class="btn btn-flex align-items-center btn-custom btn-active-light btn-active-color-primary py-2 px-3"
                        data-kt-menu-trigger="click" data-kt-menu-attach="parent"
                        data-kt-menu-placement="bottom-end">
                        <span class="symbol symbol-20px me-2">
                            <img class="rounded-1"
                                src="{{ asset('assets/media/flags/' . $locales[$activeLocale]['flag']) }}"
                                alt="{{ $locales[$activeLocale]['label'] }}" />
                        </span>
                        <span class="fw-semibold fs-7 me-2">{{ strtoupper($activeLocale) }}</span>
                        <i class="ki-duotone ki-black-left-line fs-5 m-0 rotate-270">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </button>

                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold py-4 fs-6 w-200px"
                        data-kt-menu="true">
                        @foreach ($orderedLocales as $locale => $meta)
                            <div class="menu-item px-3">
                                <form method="POST" action="{{ route('locale.update') }}">
                                    @csrf
                                    <input type="hidden" name="locale" value="{{ $locale }}" />
                                    <button type="submit"
                                        class="btn btn-link menu-link d-flex align-items-center px-5 w-100 text-start">
                                        <span class="symbol symbol-20px me-4">
                                            <img class="rounded-1"
                                                src="{{ asset('assets/media/flags/' . $meta['flag']) }}"
                                                alt="{{ $meta['label'] }}" />
                                        </span>
                                        <span class="menu-title">{{ $meta['label'] }}</span>
                                        @if (app()->getLocale() === $locale)
                                            <span class="badge badge-light-success ms-2">{{ __('ui.common.active') }}</span>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="app-navbar-item ms-1 ms-lg-3" id="kt_header_user_menu">
                    <div class="cursor-pointer d-flex align-items-center"
                        data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
                        data-kt-menu-placement="bottom-end">
                        <span class="symbol symbol-35px symbol-circle me-2">
                            <span class="symbol-label bg-light-primary text-primary fw-semibold">
                                {{ $userInitial }}
                            </span>
                        </span>
                        <i class="ki-duotone ki-black-left-line fs-5 m-0 rotate-270">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>

                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold py-4 fs-6 w-300px"
                        data-kt-menu="true">
                        <div class="menu-item px-3">
                            <div class="menu-content d-flex align-items-center px-3">
                                <div class="symbol symbol-50px me-5">
                                    <span class="symbol-label bg-light-primary text-primary fw-semibold">
                                        {{ $userInitial }}
                                    </span>
                                </div>

                                <div class="d-flex flex-column">
                                    <div class="fw-bold d-flex align-items-center fs-5">
                                        {{ $authUser?->name }}
                                    </div>
                                    <div class="fw-semibold text-muted fs-7">{{ $authUser?->email }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="separator my-2"></div>

                        <div class="menu-item px-5">
                            <a href="{{ route('drive.index') }}" class="menu-link px-5">{{ __('ui.nav.drive') }}</a>
                        </div>

                        <div class="menu-item px-5">
                            <a href="{{ route('connections.google.index') }}"
                                class="menu-link px-5">{{ __('ui.nav.google_accounts') }}</a>
                        </div>

                        <div class="menu-item px-5">
                            <a href="{{ route('profile.edit') }}" class="menu-link px-5">{{ __('ui.nav.profile') }}</a>
                        </div>

                        <div class="separator my-2"></div>

                        <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                            data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
                            <a href="#" class="menu-link px-5">
                                <span class="menu-title position-relative">{{ __('ui.theme.title') }}
                                    <span class="ms-5 position-absolute translate-middle-y top-50 end-0">
                                        <i class="ki-duotone ki-night-day theme-light-show fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                            <span class="path5"></span>
                                            <span class="path6"></span>
                                            <span class="path7"></span>
                                            <span class="path8"></span>
                                            <span class="path9"></span>
                                            <span class="path10"></span>
                                        </i>
                                        <i class="ki-duotone ki-moon theme-dark-show fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </span>
                            </a>

                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-175px"
                                data-kt-menu="true" data-kt-element="theme-mode-menu">
                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                        data-kt-value="light">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-duotone ki-night-day fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                                <span class="path5"></span>
                                                <span class="path6"></span>
                                                <span class="path7"></span>
                                                <span class="path8"></span>
                                                <span class="path9"></span>
                                                <span class="path10"></span>
                                            </i>
                                        </span>
                                        <span class="menu-title">{{ __('ui.theme.light') }}</span>
                                    </a>
                                </div>

                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                        data-kt-value="dark">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-duotone ki-moon fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                        <span class="menu-title">{{ __('ui.theme.dark') }}</span>
                                    </a>
                                </div>

                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                        data-kt-value="system">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-duotone ki-screen fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                            </i>
                                        </span>
                                        <span class="menu-title">{{ __('ui.theme.system') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="separator my-2"></div>

                        <div class="menu-item px-5">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-link menu-link px-5 w-100 text-start">
                                    {{ __('ui.auth.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

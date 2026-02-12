<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.partials.head')
</head>

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true"
    data-kt-app-toolbar-enabled="true" class="app-default">
    <script>
        var defaultThemeMode = 'light';
        var themeMode;

        if (document.documentElement) {
            if (document.documentElement.hasAttribute('data-bs-theme-mode')) {
                themeMode = document.documentElement.getAttribute('data-bs-theme-mode');
            } else if (localStorage.getItem('data-bs-theme') !== null) {
                themeMode = localStorage.getItem('data-bs-theme');
            } else {
                themeMode = defaultThemeMode;
            }

            if (themeMode === 'system') {
                themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            document.documentElement.setAttribute('data-bs-theme', themeMode);
        }
    </script>

    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            @include('layouts.partials.header')

            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                @include('layouts.partials.sidebar')

                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        @isset($header)
                            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                                <div id="kt_app_toolbar_container"
                                    class="app-container container-fluid d-flex flex-stack flex-wrap gap-4">
                                    {{ $header }}
                                </div>
                            </div>
                        @endisset

                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div id="kt_app_content_container" class="app-container container-fluid">
                                @php
                                    $status = session('status');
                                    $error = session('error');

                                    if ($status === 'verification-link-sent') {
                                        $status = __('profile.messages.verification_sent');
                                    }
                                @endphp

                                @if (is_string($status) && $status !== '')
                                    <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                                        <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <div class="d-flex flex-column">
                                            <span>{{ $status }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if (is_string($error) && $error !== '')
                                    <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                                        <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <div class="d-flex flex-column">
                                            <span>{{ $error }}</span>
                                        </div>
                                    </div>
                                @endif

                                {{ $slot }}
                            </div>
                        </div>
                    </div>

                    @include('layouts.partials.footer')
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials.scripts')
</body>

</html>

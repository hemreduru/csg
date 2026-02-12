<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.partials.head')
</head>

<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center">
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

    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">
            <div class="d-flex flex-lg-row-fluid">
                <div class="d-flex flex-column flex-center pb-0 pb-lg-10 p-10 w-100">
                    <img class="theme-light-show mx-auto mw-100 w-150px w-lg-300px mb-10 mb-lg-20"
                        src="{{ asset('assets/media/auth/agency.png') }}" alt="" />
                    <img class="theme-dark-show mx-auto mw-100 w-150px w-lg-300px mb-10 mb-lg-20"
                        src="{{ asset('assets/media/auth/agency-dark.png') }}" alt="" />

                    <h1 class="text-gray-800 fs-2qx fw-bold text-center mb-7">{{ __('auth.aside.title') }}</h1>
                    <div class="text-gray-600 fs-base text-center fw-semibold">{!! __('auth.aside.text_html') !!}</div>
                </div>
            </div>

            <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12">
                <div class="bg-body d-flex flex-column flex-center rounded-4 w-md-600px p-10">
                    <div class="d-flex flex-center flex-column align-items-stretch h-lg-100 w-md-400px">
                        <div class="d-flex flex-center flex-column flex-column-fluid pb-15 pb-lg-20">
                            {{ $slot }}
                        </div>

                        <div class="d-flex flex-stack">
                            <div class="me-10">
                                <button
                                    class="btn btn-flex btn-link btn-color-gray-700 btn-active-color-primary rotate fs-base"
                                    data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start"
                                    data-kt-menu-offset="0px, 0px">
                                    <span class="me-1">{{ __('ui.nav.language') }}</span>
                                    <i class="ki-duotone ki-down fs-5 text-muted rotate-180 m-0"></i>
                                </button>

                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-4 fs-7"
                                    data-kt-menu="true">
                                    @foreach (['en' => __('ui.locale.en'), 'tr' => __('ui.locale.tr')] as $locale => $label)
                                        <div class="menu-item px-3">
                                            <form method="POST" action="{{ route('locale.update') }}">
                                                @csrf
                                                <input type="hidden" name="locale" value="{{ $locale }}" />
                                                <button type="submit"
                                                    class="btn btn-link menu-link d-flex px-5 w-100 text-start">
                                                    <span>{{ $label }}</span>
                                                    @if (app()->getLocale() === $locale)
                                                        <span
                                                            class="badge badge-light-success ms-2">{{ __('ui.common.active') }}</span>
                                                    @endif
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="d-flex fw-semibold text-primary fs-base gap-5">
                                <a href="{{ route('drive.index') }}">{{ __('ui.nav.home') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials.scripts')
</body>

</html>


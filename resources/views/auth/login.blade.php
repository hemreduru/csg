<x-guest-layout>
    <form class="form w-100" method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="text-center mb-11">
            <h1 class="text-dark fw-bolder mb-3">{{ __('auth.login.title') }}</h1>
            <div class="text-gray-500 fw-semibold fs-6">{{ __('auth.login.subtitle') }}</div>
        </div>

        <div class="row g-3 mb-9">
            <div class="col-12">
                <a href="{{ route('auth.google.redirect') }}"
                    class="btn btn-flex btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light flex-center text-nowrap w-100">
                    <img alt="Google" src="{{ asset('assets/media/svg/brand-logos/google-icon.svg') }}"
                        class="h-15px me-3" />
                    {{ __('auth.login.google') }}
                </a>
            </div>
        </div>

        <div class="separator separator-content my-14">
            <span class="w-125px text-gray-500 fw-semibold fs-7">{{ __('auth.login.or_email') }}</span>
        </div>

        @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center p-5 mb-8">
                <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <div class="d-flex flex-column">
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-8">
                <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <div class="d-flex flex-column">
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger d-flex align-items-center p-5 mb-8">
                <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <div class="d-flex flex-column">
                    <span class="fw-semibold">{{ __('ui.common.validation_error') }}</span>
                </div>
            </div>
        @endif

        <div class="fv-row mb-8">
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus
                placeholder="{{ __('auth.fields.email') }}"
                class="form-control bg-transparent {{ $errors->has('email') ? 'is-invalid' : '' }}" />
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="fv-row mb-3">
            <input type="password" name="password" autocomplete="current-password" required
                placeholder="{{ __('auth.fields.password') }}"
                class="form-control bg-transparent {{ $errors->has('password') ? 'is-invalid' : '' }}" />
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
            <div class="form-check form-check-custom form-check-solid">
                <input class="form-check-input" type="checkbox" value="1" id="remember_me" name="remember" />
                <label class="form-check-label" for="remember_me">{{ __('auth.fields.remember') }}</label>
            </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="link-primary">{{ __('auth.login.forgot_password') }}</a>
            @endif
        </div>

        <div class="d-grid mb-10">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('auth.login.submit') }}</span>
            </button>
        </div>

        <div class="text-gray-500 text-center fw-semibold fs-6">
            {{ __('auth.login.no_account') }}
            <a href="{{ route('register') }}" class="link-primary">{{ __('auth.login.register_link') }}</a>
        </div>
    </form>
</x-guest-layout>

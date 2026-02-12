<x-guest-layout>
    <form class="form w-100" method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="text-center mb-11">
            <h1 class="text-dark fw-bolder mb-3">{{ __('auth.register.title') }}</h1>
            <div class="text-gray-500 fw-semibold fs-6">{{ __('auth.register.subtitle') }}</div>
        </div>

        <div class="row g-3 mb-9">
            <div class="col-12">
                <a href="{{ route('auth.google.redirect') }}"
                    class="btn btn-flex btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light flex-center text-nowrap w-100">
                    <img alt="Google" src="{{ asset('assets/media/svg/brand-logos/google-icon.svg') }}"
                        class="h-15px me-3" />
                    {{ __('auth.register.google') }}
                </a>
            </div>
        </div>

        <div class="separator separator-content my-14">
            <span class="w-125px text-gray-500 fw-semibold fs-7">{{ __('auth.register.or_email') }}</span>
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
            <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required
                placeholder="{{ __('auth.fields.name') }}"
                class="form-control bg-transparent {{ $errors->has('name') ? 'is-invalid' : '' }}" />
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="fv-row mb-8">
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required
                placeholder="{{ __('auth.fields.email') }}"
                class="form-control bg-transparent {{ $errors->has('email') ? 'is-invalid' : '' }}" />
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="fv-row mb-8" data-kt-password-meter="true">
            <input type="password" name="password" autocomplete="new-password" required
                placeholder="{{ __('auth.fields.password') }}"
                class="form-control bg-transparent {{ $errors->has('password') ? 'is-invalid' : '' }}" />
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="fv-row mb-8">
            <input type="password" name="password_confirmation" autocomplete="new-password" required
                placeholder="{{ __('auth.fields.password_confirmation') }}" class="form-control bg-transparent" />
        </div>

        <div class="d-grid mb-10">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('auth.register.submit') }}</span>
            </button>
        </div>

        <div class="text-gray-500 text-center fw-semibold fs-6">
            {{ __('auth.register.have_account') }}
            <a href="{{ route('login') }}" class="link-primary">{{ __('auth.register.login_link') }}</a>
        </div>
    </form>
</x-guest-layout>

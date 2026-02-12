<x-guest-layout>
    <form class="form w-100" method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="text-center mb-11">
            <h1 class="text-dark fw-bolder mb-3">{{ __('auth.forgot_password.title') }}</h1>
            <div class="text-gray-500 fw-semibold fs-6">{{ __('auth.forgot_password.subtitle') }}</div>
        </div>

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
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required
                placeholder="{{ __('auth.fields.email') }}"
                class="form-control bg-transparent {{ $errors->has('email') ? 'is-invalid' : '' }}" />
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid mb-10">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('auth.forgot_password.submit') }}</span>
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('login') }}" class="link-primary">{{ __('auth.forgot_password.back_to_login') }}</a>
        </div>
    </form>
</x-guest-layout>


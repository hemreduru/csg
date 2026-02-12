<x-guest-layout>
    <form class="form w-100" method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="text-center mb-11">
            <h1 class="text-dark fw-bolder mb-3">{{ __('auth.reset_password.title') }}</h1>
            <div class="text-gray-500 fw-semibold fs-6">{{ __('auth.reset_password.subtitle') }}</div>
        </div>

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
            <input type="email" name="email" value="{{ old('email', $request->email) }}" autocomplete="username" required
                placeholder="{{ __('auth.fields.email') }}"
                class="form-control bg-transparent {{ $errors->has('email') ? 'is-invalid' : '' }}" />
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="fv-row mb-8">
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
                <span class="indicator-label">{{ __('auth.reset_password.submit') }}</span>
            </button>
        </div>
    </form>
</x-guest-layout>


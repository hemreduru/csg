<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between w-100">
            <h1 class="fs-2 fw-bold mb-0">{{ __('profile.title') }}</h1>
            <a href="{{ route('drive.index') }}" class="btn btn-light">
                {{ __('ui.common.back') }}
            </a>
        </div>
    </x-slot>

    <div class="row g-5 g-xl-10">
        <div class="col-xl-8">
            <div class="card mb-5 mb-xl-10">
                <div class="card-header">
                    <div class="card-title flex-column">
                        <h3 class="fw-bold mb-1">{{ __('profile.sections.account.title') }}</h3>
                        <div class="text-muted fw-semibold fs-7">{{ __('profile.sections.account.description') }}</div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="alert alert-warning d-flex align-items-center p-5 mb-8">
                            <i class="ki-duotone ki-information-5 fs-2hx text-warning me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="d-flex flex-column flex-grow-1">
                                <span class="fw-semibold">{{ __('profile.messages.email_unverified') }}</span>
                                <form method="POST" action="{{ route('verification.send') }}" class="mt-3">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-light-warning">
                                        {{ __('profile.actions.resend_verification') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" novalidate>
                        @csrf
                        @method('patch')

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold">{{ __('auth.fields.name') }}</label>
                            <div class="col-lg-8">
                                <input type="text" name="name" required maxlength="255" autocomplete="name"
                                    class="form-control form-control-solid {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    value="{{ old('name', $user->name) }}" placeholder="{{ __('auth.fields.name') }}" />
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold">{{ __('auth.fields.email') }}</label>
                            <div class="col-lg-8">
                                <input type="email" name="email" required maxlength="255" autocomplete="username"
                                    class="form-control form-control-solid {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                    value="{{ old('email', $user->email) }}" placeholder="{{ __('auth.fields.email') }}" />
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                {{ __('profile.actions.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-5 mb-xl-10">
                <div class="card-header">
                    <div class="card-title flex-column">
                        <h3 class="fw-bold mb-1">{{ __('profile.sections.password.title') }}</h3>
                        <div class="text-muted fw-semibold fs-7">{{ __('profile.sections.password.description') }}</div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}" novalidate>
                        @csrf
                        @method('put')

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold">{{ __('profile.fields.current_password') }}</label>
                            <div class="col-lg-8">
                                <input type="password" name="current_password" required autocomplete="current-password"
                                    class="form-control form-control-solid @error('current_password', 'updatePassword') is-invalid @enderror"
                                    placeholder="{{ __('profile.fields.current_password') }}" />
                                @error('current_password', 'updatePassword')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold">{{ __('profile.fields.new_password') }}</label>
                            <div class="col-lg-8">
                                <input type="password" name="password" required autocomplete="new-password"
                                    class="form-control form-control-solid @error('password', 'updatePassword') is-invalid @enderror"
                                    placeholder="{{ __('profile.fields.new_password') }}" />
                                @error('password', 'updatePassword')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold">{{ __('auth.fields.password_confirmation') }}</label>
                            <div class="col-lg-8">
                                <input type="password" name="password_confirmation" required autocomplete="new-password"
                                    class="form-control form-control-solid @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                                    placeholder="{{ __('auth.fields.password_confirmation') }}" />
                                @error('password_confirmation', 'updatePassword')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                {{ __('profile.actions.update_password') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title flex-column">
                        <h3 class="fw-bold mb-1 text-danger">{{ __('profile.sections.delete.title') }}</h3>
                        <div class="text-muted fw-semibold fs-7">{{ __('profile.sections.delete.description') }}</div>
                    </div>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-light-danger w-100" data-bs-toggle="modal"
                        data-bs-target="#kt_profile_delete_modal">
                        {{ __('profile.actions.delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="kt_profile_delete_modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('profile.destroy') }}" novalidate>
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('profile.sections.delete.confirm_title') }}</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ki-duotone ki-cross fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted">{{ __('profile.sections.delete.confirm_text') }}</p>

                        <label class="form-label">{{ __('auth.fields.password') }}</label>
                        <input type="password" name="password" required autocomplete="current-password"
                            class="form-control form-control-solid @error('password', 'userDeletion') is-invalid @enderror"
                            placeholder="{{ __('auth.fields.password') }}" />
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            {{ __('ui.common.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-danger">
                            {{ __('profile.actions.delete') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        @if ($errors->userDeletion->any())
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var el = document.getElementById('kt_profile_delete_modal');
                    if (!el || !window.bootstrap) {
                        return;
                    }

                    new bootstrap.Modal(el).show();
                });
            </script>
        @endif
    @endpush
</x-app-layout>

<x-guest-layout>
    <div class="text-center mb-11">
        <h1 class="text-dark fw-bolder mb-3">{{ __('ui.auth.verify_email_title') }}</h1>
        <div class="text-gray-500 fw-semibold fs-6">{{ __('ui.auth.verify_email_subtitle') }}</div>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success d-flex align-items-center p-5 mb-8">
            <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            <div class="d-flex flex-column">
                <span>{{ __('ui.auth.verify_email_link_sent') }}</span>
            </div>
        </div>
    @endif

    <div class="d-flex flex-stack">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">{{ __('ui.auth.verify_email_resend') }}</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-light">{{ __('ui.auth.logout') }}</button>
        </form>
    </div>
</x-guest-layout>


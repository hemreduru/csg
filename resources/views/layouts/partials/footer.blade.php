<div id="kt_app_footer" class="app-footer">
    <div class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
        <div class="text-gray-600 order-2 order-md-1">
            <span class="text-muted fw-semibold me-1">{{ now()->year }}</span>
            <span class="text-gray-800 text-hover-primary fw-semibold">{{ config('app.name') }}</span>
        </div>
        <ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1">
            <li class="menu-item">
                <a href="{{ route('drive.index') }}" class="menu-link px-2">{{ __('ui.nav.drive') }}</a>
            </li>
            <li class="menu-item">
                <a href="{{ route('connections.google.index') }}" class="menu-link px-2">{{ __('ui.nav.google_accounts') }}</a>
            </li>
        </ul>
    </div>
</div>


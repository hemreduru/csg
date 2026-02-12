<x-app-layout :page-title="__('drive.connections.page_title')">
    @php
        $connectedCount = $connections->where('status', 'connected')->count();
        $defaultConnection = $connections->firstWhere('is_default', true);
    @endphp

    <x-slot name="header">
        <div class="d-flex align-items-center gap-2 gap-lg-3 ms-auto">
            <a href="{{ route('drive.index') }}" class="btn btn-sm fw-bold btn-light">
                {{ __('ui.common.back') }}
            </a>
            <a href="{{ route('connections.google.redirect') }}" class="btn btn-sm fw-bold btn-primary">
                <i class="ki-duotone ki-plus fs-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                {{ __('drive.connections.add') }}
            </a>
        </div>
    </x-slot>

    <div class="card card-flush pb-0 bgi-position-y-center bgi-no-repeat mb-10 position-relative">
        <div class="position-absolute top-0 end-0 d-none d-xl-block me-10 mt-8">
            <img src="{{ asset('assets/media/illustrations/sketchy-1/2.png') }}"
                alt="{{ __('drive.connections.summary.title') }}" class="mw-150px opacity-50" />
        </div>

        <div class="card-header pt-10">
            <div class="d-flex align-items-center">
                <div class="symbol symbol-circle me-5">
                    <div class="symbol-label bg-transparent text-primary border border-secondary border-dashed">
                        <i class="ki-duotone ki-google fs-2x text-primary">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>

                <div class="d-flex flex-column">
                    <h2 class="mb-1">{{ __('drive.connections.summary.title') }}</h2>
                    <div class="text-muted fw-bold">{{ __('drive.connections.summary.subtitle') }}</div>
                </div>
            </div>
        </div>

        <div class="card-body pb-7">
            <div class="d-flex flex-wrap gap-3">
                <span class="badge badge-lg badge-light-primary">
                    {{ trans_choice('drive.connections.summary.connected_count', $connectedCount, ['count' => $connectedCount]) }}
                </span>
                <span class="badge badge-lg badge-primary">
                    {{ $defaultConnection?->google_account_email ?? __('drive.connections.summary.no_default') }}
                </span>
            </div>
        </div>
    </div>

    <div class="card card-flush">
        <div class="card-header pt-8">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input id="kt_google_connections_search" type="text"
                        class="form-control form-control-solid w-250px ps-15"
                        placeholder="{{ __('drive.connections.search_placeholder') }}" />
                </div>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('connections.google.redirect') }}" class="btn btn-sm fw-bold btn-primary">
                    <i class="ki-duotone ki-plus fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ __('drive.connections.add') }}
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <table id="kt_google_connections_table" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-250px">{{ __('drive.connections.name') }}</th>
                        <th class="min-w-250px">{{ __('drive.connections.email') }}</th>
                        <th class="min-w-120px">{{ __('drive.connections.status') }}</th>
                        <th class="w-125px"></th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse ($connections as $conn)
                        @php
                            $renameModalId = 'kt_drive_connection_rename_' . $conn->id;
                            $isRenameActive = old('_modal') === $renameModalId;
                            $statusColor = match ($conn->status) {
                                'connected' => 'success',
                                'revoked' => 'warning',
                                'error' => 'danger',
                                default => 'secondary',
                            };
                        @endphp

                        <tr data-kt-connection-row
                            data-name="{{ \Illuminate\Support\Str::lower($conn->name) }}"
                            data-email="{{ \Illuminate\Support\Str::lower($conn->google_account_email) }}">
                            <td data-order="{{ \Illuminate\Support\Str::lower($conn->name) }}">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-4">
                                        <span class="symbol-label bg-light-primary">
                                            <i class="ki-duotone ki-google fs-2 text-primary">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800">{{ $conn->name }}</span>
                                        @if ($conn->is_default)
                                            <span class="badge badge-light-primary mt-1">
                                                {{ __('drive.connections.default_badge') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $conn->google_account_email }}</td>
                            <td>
                                <span class="badge badge-light-{{ $statusColor }}">
                                    {{ __('drive.connections.statuses.' . $conn->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary"
                                    data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    <i class="ki-duotone ki-dots-square fs-5 m-0">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                </button>

                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-180px py-4"
                                    data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="{{ route('drive.connection', $conn) }}"
                                            class="menu-link px-3">{{ __('drive.connections.open_drive') }}</a>
                                    </div>

                                    @if (! $conn->is_default)
                                        <div class="menu-item px-3">
                                            <form method="POST" action="{{ route('connections.google.default', $conn) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="menu-link px-3 w-100 text-start border-0 bg-transparent">
                                                    {{ __('drive.connections.set_default') }}
                                                </button>
                                            </form>
                                        </div>
                                    @endif

                                    <div class="menu-item px-3">
                                        <button type="button"
                                            class="menu-link px-3 w-100 text-start border-0 bg-transparent"
                                            data-bs-toggle="modal" data-bs-target="#{{ $renameModalId }}">
                                            {{ __('drive.connections.rename') }}
                                        </button>
                                    </div>

                                    <div class="menu-item px-3">
                                        <form method="POST" action="{{ route('connections.google.disconnect', $conn) }}">
                                            @csrf
                                            <button type="submit"
                                                class="menu-link text-danger px-3 w-100 text-start border-0 bg-transparent"
                                                onclick="return confirm(@json(__('ui.common.confirm_disconnect')));">
                                                {{ __('drive.connections.disconnect') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="modal fade" tabindex="-1" id="{{ $renameModalId }}">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('connections.google.rename', $conn) }}"
                                                novalidate>
                                                @csrf
                                                <input type="hidden" name="_modal" value="{{ $renameModalId }}" />

                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('drive.connections.rename') }}</h5>
                                                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2"
                                                        data-bs-dismiss="modal" aria-label="Close">
                                                        <i class="ki-duotone ki-cross fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </div>
                                                </div>

                                                <div class="modal-body">
                                                    <label class="form-label">{{ __('drive.connections.name') }}</label>
                                                    <input type="text" name="name" required maxlength="255"
                                                        class="form-control form-control-solid {{ $isRenameActive && $errors->has('name') ? 'is-invalid' : '' }}"
                                                        value="{{ $isRenameActive ? old('name') : $conn->name }}"
                                                        placeholder="{{ __('drive.connections.name') }}" />
                                                    @if ($isRenameActive)
                                                        @error('name')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    @endif
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light"
                                                        data-bs-dismiss="modal">{{ __('ui.common.cancel') }}</button>
                                                    <button type="submit"
                                                        class="btn btn-primary">{{ __('ui.common.save') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="kt_google_connections_empty_row">
                            <td colspan="4" class="text-center py-10 text-muted">
                                {{ __('drive.no_connected_accounts') }}
                            </td>
                        </tr>
                    @endforelse

                    <tr id="kt_google_connections_no_results_row" class="d-none">
                        <td colspan="4" class="text-center py-10 text-muted">
                            {{ __('drive.connections.no_results') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modalId = @json(old('_modal'));
                if (modalId && window.bootstrap) {
                    var modalElement = document.getElementById(modalId);
                    if (modalElement) {
                        new bootstrap.Modal(modalElement).show();
                    }
                }

                var searchInput = document.getElementById('kt_google_connections_search');
                var rows = Array.prototype.slice.call(document.querySelectorAll('[data-kt-connection-row]'));
                var emptyRow = document.getElementById('kt_google_connections_empty_row');
                var noResultsRow = document.getElementById('kt_google_connections_no_results_row');

                if (!searchInput) {
                    return;
                }

                var filterRows = function() {
                    var term = searchInput.value.trim().toLowerCase();
                    var visibleRows = 0;

                    rows.forEach(function(row) {
                        var name = (row.getAttribute('data-name') || '').toLowerCase();
                        var email = (row.getAttribute('data-email') || '').toLowerCase();
                        var match = term === '' || name.indexOf(term) !== -1 || email.indexOf(term) !== -1;

                        row.classList.toggle('d-none', !match);
                        if (match) {
                            visibleRows += 1;
                        }
                    });

                    if (emptyRow) {
                        emptyRow.classList.toggle('d-none', rows.length > 0);
                    }

                    if (noResultsRow) {
                        noResultsRow.classList.toggle('d-none', !(rows.length > 0 && visibleRows === 0));
                    }
                };

                searchInput.addEventListener('input', filterRows);
                filterRows();
            });
        </script>
    @endpush
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between w-100">
            <h1 class="fs-2 fw-bold mb-0">{{ __('drive.connections.title') }}</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('drive.index') }}" class="btn btn-light">
                    {{ __('ui.common.back') }}
                </a>
                <a href="{{ route('connections.google.redirect') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ __('drive.connections.add') }}
                </a>
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center p-5 mb-5">
            <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            <div class="d-flex flex-column">
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
            <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            <div class="d-flex flex-column">
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-200px">{{ __('drive.connections.name') }}</th>
                            <th class="min-w-200px">{{ __('drive.connections.email') }}</th>
                            <th class="min-w-120px">{{ __('drive.connections.status') }}</th>
                            <th class="text-end min-w-250px">{{ __('drive.browse.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse ($connections as $conn)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="symbol symbol-35px me-3">
                                            <span class="symbol-label bg-light-primary text-primary fw-semibold">
                                                {{ mb_strtoupper(mb_substr($conn->name, 0, 1)) }}
                                            </span>
                                        </span>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800">{{ $conn->name }}</span>
                                            @if ($conn->is_default)
                                                <span class="badge badge-light-primary mt-1">
                                                    {{ __('ui.common.default') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $conn->google_account_email }}</td>
                                <td>
                                    <span class="badge badge-light-{{ $conn->status === 'connected' ? 'success' : 'danger' }}">
                                        {{ __('drive.connections.statuses.' . $conn->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        @if (! $conn->is_default)
                                            <form method="POST" action="{{ route('connections.google.default', $conn) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light">
                                                    {{ __('drive.connections.set_default') }}
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal"
                                            data-bs-target="#renameConnModal{{ $conn->id }}">
                                            {{ __('drive.connections.rename') }}
                                        </button>

                                        <form method="POST" action="{{ route('connections.google.disconnect', $conn) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light-danger"
                                                onclick="return confirm(@json(__('ui.common.confirm_disconnect')));">
                                                {{ __('drive.connections.disconnect') }}
                                            </button>
                                        </form>
                                    </div>

                                    <div class="modal fade" tabindex="-1" id="renameConnModal{{ $conn->id }}">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('connections.google.rename', $conn) }}">
                                                    @csrf
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
                                                        <input type="text" name="name" class="form-control"
                                                            value="{{ $conn->name }}" required maxlength="255" />
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
                            <tr>
                                <td colspan="4" class="text-center py-10 text-muted">
                                    {{ __('drive.no_connected_accounts') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

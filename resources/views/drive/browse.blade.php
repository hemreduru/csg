<x-app-layout>
    <x-slot name="header">
        @php
            $createFolderModalId = 'kt_drive_create_folder_modal';
            $uploadModalId = 'kt_drive_upload_modal';
        @endphp

        <div class="d-flex flex-wrap align-items-center justify-content-between w-100 gap-3">
            <h1 class="fs-2 fw-bold mb-0">{{ __('drive.browse.title') }}</h1>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-light" data-bs-toggle="modal"
                    data-bs-target="#{{ $createFolderModalId }}">
                    {{ __('drive.browse.new_folder') }}
                </button>

                <button type="button" class="btn btn-light" data-bs-toggle="modal"
                    data-bs-target="#{{ $uploadModalId }}">
                    {{ __('drive.browse.upload') }}
                </button>

                <a href="{{ $path !== '' ? route('drive.browse', [$activeDriveConnection, 'path' => $parentPath]) : route('drive.index') }}"
                    class="btn btn-light">
                    {{ __('drive.browse.back') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="modal fade" tabindex="-1" id="kt_drive_create_folder_modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('drive.folders.create', $activeDriveConnection) }}" novalidate>
                    @csrf
                    <input type="hidden" name="path" value="{{ $path }}" />
                    <input type="hidden" name="_modal" value="{{ $createFolderModalId }}" />

                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('drive.browse.create_folder_title') }}</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ki-duotone ki-cross fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>

                    @php
                        $isCreateFolderActive = old('_modal') === $createFolderModalId;
                    @endphp

                    <div class="modal-body">
                        <label class="form-label">{{ __('drive.browse.folder_name') }}</label>
                        <input type="text" name="name" required maxlength="255"
                            class="form-control form-control-solid {{ $isCreateFolderActive && $errors->driveCreateFolder->has('name') ? 'is-invalid' : '' }}"
                            value="{{ $isCreateFolderActive ? old('name') : '' }}"
                            placeholder="{{ __('drive.browse.folder_name') }}" />
                        @if ($isCreateFolderActive)
                            @error('name', 'driveCreateFolder')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            {{ __('ui.common.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('drive.browse.create_folder_submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="kt_drive_upload_modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('drive.upload', $activeDriveConnection) }}" novalidate
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="path" value="{{ $path }}" />
                    <input type="hidden" name="_modal" value="{{ $uploadModalId }}" />

                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('drive.browse.upload_title') }}</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ki-duotone ki-cross fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>

                    @php
                        $isUploadActive = old('_modal') === $uploadModalId;
                    @endphp

                    <div class="modal-body">
                        <label class="form-label">{{ __('drive.browse.choose_file') }}</label>
                        <input type="file" name="file" required
                            class="form-control form-control-solid {{ $isUploadActive && $errors->driveUpload->has('file') ? 'is-invalid' : '' }}" />
                        @if ($isUploadActive)
                            @error('file', 'driveUpload')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        @endif
                        <div class="text-muted fs-7 mt-2">{{ __('drive.browse.upload_hint') }}</div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            {{ __('ui.common.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('drive.browse.upload_submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header align-items-center">
            <div class="card-title">
                <div class="d-flex flex-column">
                    <span class="text-muted">{{ $activeDriveConnection->google_account_email }}</span>
                    <div class="text-gray-800 fw-semibold">
                        <a href="{{ route('drive.browse', [$activeDriveConnection, 'path' => '']) }}"
                            class="text-gray-800 text-hover-primary">/</a>
                        @foreach ($breadcrumbs as $crumb)
                            <span class="text-muted mx-1">/</span>
                            <a href="{{ route('drive.browse', [$activeDriveConnection, 'path' => $crumb['path']]) }}"
                                class="text-gray-800 text-hover-primary">{{ $crumb['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-250px">{{ __('drive.browse.file') }}</th>
                            <th class="min-w-150px">{{ __('drive.browse.modified') }}</th>
                            <th class="min-w-100px">{{ __('drive.browse.size') }}</th>
                            <th class="text-end min-w-100px">{{ __('drive.browse.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse ($items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="symbol symbol-35px me-3">
                                            <span
                                                class="symbol-label bg-light-{{ $item['is_folder'] ? 'primary' : 'secondary' }}">
                                                <i
                                                    class="ki-duotone {{ $item['is_folder'] ? 'ki-folder' : 'ki-file' }} fs-2 text-{{ $item['is_folder'] ? 'primary' : 'secondary' }}">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                        </span>
                                        @if ($item['is_folder'])
                                            <a class="text-gray-800 text-hover-primary"
                                                href="{{ route('drive.browse', [$activeDriveConnection, 'path' => ($path === '' ? $item['name'] : $path.'/'.$item['name'])]) }}">
                                                {{ $item['name'] }}
                                            </a>
                                        @else
                                            <span class="text-gray-800">{{ $item['name'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $item['modified_time'] ?? __('ui.common.na') }}</td>
                                <td>{{ $item['size'] ? number_format($item['size']) : __('ui.common.na') }}</td>
                                <td class="text-end">
                                    @php
                                        $renameModalId = 'kt_drive_rename_item_' . md5($item['id']);
                                        $isRenameActive = old('_modal') === $renameModalId;
                                    @endphp

                                    <div class="d-inline-flex flex-wrap gap-2 justify-content-end">
                                        @if (! $item['is_folder'])
                                            <a class="btn btn-sm btn-light"
                                                href="{{ route('drive.items.download', [$activeDriveConnection, $item['id']]) }}">
                                                {{ __('drive.browse.download') }}
                                            </a>
                                        @else
                                            <a class="btn btn-sm btn-light"
                                                href="{{ route('drive.browse', [$activeDriveConnection, 'path' => ($path === '' ? $item['name'] : $path.'/'.$item['name'])]) }}">
                                                {{ __('drive.browse.open') }}
                                            </a>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal"
                                            data-bs-target="#{{ $renameModalId }}">
                                            {{ __('drive.browse.rename') }}
                                        </button>

                                        <form method="POST"
                                            action="{{ route('drive.items.trash', [$activeDriveConnection, $item['id']]) }}">
                                            @csrf
                                            <input type="hidden" name="path" value="{{ $path }}" />
                                            <button type="submit" class="btn btn-sm btn-light-danger"
                                                onclick="return confirm(@json(__('drive.browse.confirm_delete')));">
                                                {{ __('drive.browse.delete') }}
                                            </button>
                                        </form>
                                    </div>

                                    <div class="modal fade" tabindex="-1" id="{{ $renameModalId }}">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form method="POST"
                                                    action="{{ route('drive.items.rename', [$activeDriveConnection, $item['id']]) }}"
                                                    novalidate>
                                                    @csrf
                                                    <input type="hidden" name="path" value="{{ $path }}" />
                                                    <input type="hidden" name="_modal" value="{{ $renameModalId }}" />

                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ __('drive.browse.rename_title') }}</h5>
                                                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2"
                                                            data-bs-dismiss="modal" aria-label="Close">
                                                            <i class="ki-duotone ki-cross fs-2">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                        </div>
                                                    </div>

                                                    <div class="modal-body">
                                                        <label class="form-label">{{ __('drive.browse.new_name') }}</label>
                                                        <input type="text" name="name" required maxlength="255"
                                                            class="form-control form-control-solid {{ $isRenameActive && $errors->driveRename->has('name') ? 'is-invalid' : '' }}"
                                                            value="{{ $isRenameActive ? old('name') : $item['name'] }}"
                                                            placeholder="{{ __('drive.browse.new_name') }}" />
                                                        @if ($isRenameActive)
                                                            @error('name', 'driveRename')
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
                            <tr>
                                <td colspan="4" class="text-center py-10 text-muted">
                                    {{ __('drive.browse.empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modalId = @json(old('_modal'));
                if (!modalId || !window.bootstrap) {
                    return;
                }

                var el = document.getElementById(modalId);
                if (!el) {
                    return;
                }

                new bootstrap.Modal(el).show();
            });
        </script>
    @endpush
</x-app-layout>

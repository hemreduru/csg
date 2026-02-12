<x-app-layout :page-title="__('drive.browse.page_title')">
    @php
        $createFolderModalId = 'kt_drive_create_folder_modal';
        $uploadModalId = 'kt_drive_upload_modal';
        $renameModalId = 'kt_drive_rename_modal';
        $bulkTrashFormId = 'kt_drive_bulk_trash_form';
        $itemsCount = count($items);
        $displayPath = $path === '' ? '/' : '/' . $path;
        $backPath =
            $path !== '' ? route('drive.browse', [$activeDriveConnection, 'path' => $parentPath]) : route('drive.index');
        $isRenameActive = old('_modal') === $renameModalId;
        $renameItemId = $isRenameActive ? (string) old('item_id', '') : '';
        $renameAction = $renameItemId !== '' ? route('drive.items.rename', [$activeDriveConnection, $renameItemId]) : '';
        $renameActionTemplate = route('drive.items.rename', [$activeDriveConnection, '__ITEM_ID__']);
    @endphp

    <x-slot name="header">
        <div class="d-flex align-items-center gap-2 gap-lg-3 ms-auto">
            <a href="{{ route('connections.google.index') }}" class="btn btn-sm fw-bold btn-light">
                {{ __('drive.browse.tabs.accounts') }}
            </a>
            <a href="{{ $backPath }}" class="btn btn-sm fw-bold btn-primary">{{ __('drive.browse.back') }}</a>
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

    <div class="modal fade" tabindex="-1" id="{{ $renameModalId }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="kt_drive_rename_form" method="POST"
                    action="{{ $renameAction !== '' ? $renameAction : $renameActionTemplate }}" novalidate
                    data-action-template="{{ $renameActionTemplate }}">
                    @csrf
                    <input type="hidden" name="path" value="{{ $path }}" />
                    <input type="hidden" name="_modal" value="{{ $renameModalId }}" />
                    <input type="hidden" id="kt_drive_rename_item_id" name="item_id" value="{{ $renameItemId }}" />

                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('drive.browse.rename_title') }}</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ki-duotone ki-cross fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>

                    <div class="modal-body">
                        <label class="form-label">{{ __('drive.browse.new_name') }}</label>
                        <input id="kt_drive_rename_name_input" type="text" name="name" required maxlength="255"
                            class="form-control form-control-solid {{ $isRenameActive && $errors->driveRename->has('name') ? 'is-invalid' : '' }}"
                            value="{{ $isRenameActive ? old('name') : '' }}"
                            placeholder="{{ __('drive.browse.new_name') }}" />
                        @if ($isRenameActive)
                            @error('name', 'driveRename')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            {{ __('ui.common.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">{{ __('ui.common.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card card-flush pb-0 bgi-position-y-center bgi-no-repeat mb-10 position-relative">
        <div class="position-absolute top-0 end-0 d-none d-xl-block me-10 mt-8">
            <img src="{{ asset('assets/media/illustrations/sketchy-1/4.png') }}"
                alt="{{ __('drive.browse.manager_title') }}" class="mw-150px opacity-50" />
        </div>

        <div class="card-header pt-10">
            <div class="d-flex align-items-center">
                <div class="symbol symbol-circle me-5">
                    <div class="symbol-label bg-transparent text-primary border border-secondary border-dashed">
                        <i class="ki-duotone ki-abstract-47 fs-2x text-primary">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>

                <div class="d-flex flex-column">
                    <h2 class="mb-1">{{ __('drive.browse.manager_title') }}</h2>
                    <div class="text-muted fw-bold">
                        <span>{{ $activeDriveConnection->google_account_email }}</span>
                        <span class="mx-3">|</span>
                        <span>{{ trans_choice('drive.browse.items_count', $itemsCount, ['count' => $itemsCount]) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pb-0">
            <div class="d-flex overflow-auto h-55px">
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-semibold flex-nowrap">
                    <li class="nav-item">
                        <a class="nav-link text-active-primary me-6 active"
                            href="{{ route('drive.connection', $activeDriveConnection) }}">
                            {{ __('drive.browse.tabs.files') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary me-6" href="{{ route('connections.google.index') }}">
                            {{ __('drive.browse.tabs.accounts') }}
                        </a>
                    </li>
                </ul>
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
                    <input id="kt_drive_search" type="text" class="form-control form-control-solid w-250px ps-15"
                        placeholder="{{ __('drive.browse.search_placeholder') }}" />
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-filemanager-table-toolbar="base">
                    <button type="button" class="btn btn-flex btn-light-primary me-3" data-bs-toggle="modal"
                        data-bs-target="#{{ $createFolderModalId }}">
                        <i class="ki-duotone ki-add-folder fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('drive.browse.new_folder') }}
                    </button>

                    <button type="button" class="btn btn-flex btn-primary" data-bs-toggle="modal"
                        data-bs-target="#{{ $uploadModalId }}">
                        <i class="ki-duotone ki-folder-up fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('drive.browse.upload') }}
                    </button>
                </div>

                <div class="d-flex justify-content-end align-items-center d-none"
                    data-kt-filemanager-table-toolbar="selected">
                    <div class="fw-bold me-5">
                        <span class="me-1" data-kt-filemanager-table-select-count>0</span>
                        {{ __('drive.browse.bulk_selected') }}
                    </div>
                    <button type="button" id="kt_drive_bulk_delete_trigger" class="btn btn-sm btn-danger">
                        {{ __('drive.browse.bulk_delete') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="d-flex flex-stack mb-6">
                <div class="badge badge-lg badge-light-primary">
                    <div class="d-flex align-items-center flex-wrap">
                        <i class="ki-duotone ki-abstract-32 fs-2 text-primary me-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <span class="text-gray-700">
                            {{ __('drive.browse.current_path') }}: {{ $displayPath }}
                        </span>
                    </div>
                </div>

                <div class="badge badge-lg badge-primary">
                    <span id="kt_file_manager_items_counter">
                        {{ trans_choice('drive.browse.items_count', $itemsCount, ['count' => $itemsCount]) }}
                    </span>
                </div>
            </div>

            <form id="{{ $bulkTrashFormId }}" method="POST"
                action="{{ route('drive.items.bulk_trash', $activeDriveConnection) }}" class="d-none">
                @csrf
                <input type="hidden" name="path" value="{{ $path }}" />
            </form>

            <table id="kt_file_manager_list" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-40px">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" value="1"
                                    data-kt-check="true" data-kt-check-target=".kt-drive-item-checkbox"
                                    id="kt_drive_bulk_select_all" />
                            </div>
                        </th>
                        <th class="min-w-250px">{{ __('drive.browse.file') }}</th>
                        <th class="min-w-100px">{{ __('drive.browse.size') }}</th>
                        <th class="min-w-150px">{{ __('drive.browse.modified') }}</th>
                        <th class="w-125px"></th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse ($items as $item)
                        @php
                            $itemPath = $path === '' ? $item['name'] : $path . '/' . $item['name'];
                            $trashFormId = 'kt_drive_trash_item_' . md5($item['id']);
                            $size = $item['size'] ? \Illuminate\Support\Number::fileSize($item['size']) : __('drive.browse.na');
                            $modified = $item['modified_time']
                                ? \Illuminate\Support\Carbon::parse($item['modified_time'])->format('d.m.Y H:i')
                                : __('drive.browse.na');
                        @endphp

                        <tr data-kt-filemanager-row data-name="{{ \Illuminate\Support\Str::lower($item['name']) }}">
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input kt-drive-item-checkbox" type="checkbox"
                                        value="{{ $item['id'] }}" data-drive-item-checkbox />
                                </div>
                            </td>
                            <td data-order="{{ \Illuminate\Support\Str::lower($item['name']) }}">
                                <div class="d-flex align-items-center">
                                    <span class="icon-wrapper">
                                        <i
                                            class="ki-duotone {{ $item['is_folder'] ? 'ki-folder' : 'ki-file' }} fs-2x {{ $item['is_folder'] ? 'text-primary' : 'text-gray-700' }} me-4">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>

                                    @if ($item['is_folder'])
                                        <a href="{{ route('drive.browse', [$activeDriveConnection, 'path' => $itemPath]) }}"
                                            class="text-gray-800 text-hover-primary">{{ $item['name'] }}</a>
                                    @else
                                        <span class="text-gray-800">{{ $item['name'] }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $size }}</td>
                            <td>{{ $modified }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end">
                                    <div class="ms-2">
                                        @if ($item['is_folder'])
                                            <a href="{{ route('drive.browse', [$activeDriveConnection, 'path' => $itemPath]) }}"
                                                class="btn btn-sm btn-icon btn-light btn-active-light-primary">
                                                <i class="ki-duotone ki-folder fs-5 m-0">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </a>
                                        @else
                                            <a href="{{ route('drive.items.preview', [$activeDriveConnection, $item['id'], 'path' => $path]) }}"
                                                target="_blank" rel="noopener"
                                                class="btn btn-sm btn-icon btn-light btn-active-light-primary">
                                                <i class="ki-duotone ki-eye fs-5 m-0">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                            </a>
                                        @endif
                                    </div>

                                    <div class="ms-2">
                                        <button type="button"
                                            class="btn btn-sm btn-icon btn-light btn-active-light-primary"
                                            data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                            <i class="ki-duotone ki-dots-square fs-5 m-0">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                            </i>
                                        </button>

                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-150px py-4"
                                            data-kt-menu="true">
                                            <div class="menu-item px-3">
                                                @if ($item['is_folder'])
                                                    <a href="{{ route('drive.browse', [$activeDriveConnection, 'path' => $itemPath]) }}"
                                                        class="menu-link px-3">{{ __('drive.browse.open') }}</a>
                                                @else
                                                    <a href="{{ route('drive.items.preview', [$activeDriveConnection, $item['id'], 'path' => $path]) }}"
                                                        target="_blank" rel="noopener"
                                                        class="menu-link px-3">{{ __('drive.browse.preview') }}</a>
                                                @endif
                                            </div>
                                            @if (! $item['is_folder'])
                                                <div class="menu-item px-3">
                                                    <a href="{{ route('drive.items.download', [$activeDriveConnection, $item['id']]) }}"
                                                        class="menu-link px-3">{{ __('drive.browse.download') }}</a>
                                                </div>
                                            @endif
                                            <div class="menu-item px-3">
                                                <button type="button"
                                                    class="menu-link px-3 w-100 text-start border-0 bg-transparent"
                                                    data-bs-toggle="modal" data-bs-target="#{{ $renameModalId }}"
                                                    data-drive-rename-trigger
                                                    data-drive-item-id="{{ $item['id'] }}"
                                                    data-drive-item-name="{{ $item['name'] }}">
                                                    {{ __('drive.browse.rename') }}
                                                </button>
                                            </div>
                                            <div class="menu-item px-3">
                                                <button type="button"
                                                    class="menu-link text-danger px-3 w-100 text-start border-0 bg-transparent"
                                                    data-drive-trash-trigger="{{ $trashFormId }}">
                                                    {{ __('drive.browse.delete') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <form id="{{ $trashFormId }}" method="POST"
                                    action="{{ route('drive.items.trash', [$activeDriveConnection, $item['id']]) }}"
                                    class="d-none">
                                    @csrf
                                    <input type="hidden" name="path" value="{{ $path }}" />
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr id="kt_drive_empty_row">
                            <td colspan="5" class="text-center py-10 text-muted">
                                {{ __('drive.browse.empty') }}
                            </td>
                        </tr>
                    @endforelse

                    <tr id="kt_drive_no_results_row" class="d-none">
                        <td colspan="5" class="text-center py-10 text-muted">
                            {{ __('drive.browse.no_results') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @push('styles')
        <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
            type="text/css" />
    @endpush

    @push('scripts')
        <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modalId = @json(old('_modal'));
                if (modalId && window.bootstrap) {
                    var modalElement = document.getElementById(modalId);
                    if (modalElement) {
                        new bootstrap.Modal(modalElement).show();
                    }
                }

                var searchInput = document.getElementById('kt_drive_search');
                var rows = Array.prototype.slice.call(document.querySelectorAll('[data-kt-filemanager-row]'));
                var noResultsRow = document.getElementById('kt_drive_no_results_row');
                var emptyRow = document.getElementById('kt_drive_empty_row');
                var renameForm = document.getElementById('kt_drive_rename_form');
                var renameNameInput = document.getElementById('kt_drive_rename_name_input');
                var renameItemIdInput = document.getElementById('kt_drive_rename_item_id');
                var renameActionTemplate = renameForm ? renameForm.getAttribute('data-action-template') : '';
                var selectAllCheckbox = document.getElementById('kt_drive_bulk_select_all');
                var bulkDeleteTrigger = document.getElementById('kt_drive_bulk_delete_trigger');
                var bulkForm = document.getElementById(@json($bulkTrashFormId));
                var bulkToolbarBase = document.querySelector('[data-kt-filemanager-table-toolbar="base"]');
                var bulkToolbarSelected = document.querySelector('[data-kt-filemanager-table-toolbar="selected"]');
                var bulkSelectedCount = document.querySelector('[data-kt-filemanager-table-select-count]');

                var setRenameAction = function(itemId) {
                    if (!renameForm || !renameActionTemplate || !itemId) {
                        return;
                    }

                    renameForm.setAttribute('action', renameActionTemplate.replace('__ITEM_ID__', encodeURIComponent(itemId)));
                };

                if (renameItemIdInput && renameItemIdInput.value) {
                    setRenameAction(renameItemIdInput.value);
                }

                var renameTriggers = Array.prototype.slice.call(document.querySelectorAll('[data-drive-rename-trigger]'));
                renameTriggers.forEach(function(trigger) {
                    trigger.addEventListener('click', function() {
                        var itemId = trigger.getAttribute('data-drive-item-id') || '';
                        var itemName = trigger.getAttribute('data-drive-item-name') || '';

                        if (renameItemIdInput) {
                            renameItemIdInput.value = itemId;
                        }

                        setRenameAction(itemId);

                        if (renameNameInput) {
                            renameNameInput.value = itemName;
                        }
                    });
                });

                var getRowCheckbox = function(row) {
                    return row.querySelector('[data-drive-item-checkbox]');
                };

                var getVisibleRows = function() {
                    return rows.filter(function(row) {
                        return !row.classList.contains('d-none');
                    });
                };

                var syncSelectAllState = function() {
                    if (!selectAllCheckbox) {
                        return;
                    }

                    var visibleRows = getVisibleRows();
                    var visibleCheckboxes = visibleRows
                        .map(getRowCheckbox)
                        .filter(function(checkbox) {
                            return checkbox !== null;
                        });

                    var checkedCount = visibleCheckboxes.filter(function(checkbox) {
                        return checkbox.checked;
                    }).length;

                    var allChecked = visibleCheckboxes.length > 0 && checkedCount === visibleCheckboxes.length;
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = checkedCount > 0 && !allChecked;
                };

                var updateBulkToolbarState = function() {
                    if (!bulkToolbarBase || !bulkToolbarSelected || !bulkSelectedCount) {
                        return;
                    }

                    var checkedCount = rows.filter(function(row) {
                        var checkbox = getRowCheckbox(row);
                        return checkbox && checkbox.checked;
                    }).length;

                    bulkSelectedCount.textContent = String(checkedCount);
                    bulkToolbarBase.classList.toggle('d-none', checkedCount > 0);
                    bulkToolbarSelected.classList.toggle('d-none', checkedCount === 0);
                };

                var rowCheckboxes = Array.prototype.slice.call(document.querySelectorAll('[data-drive-item-checkbox]'));
                rowCheckboxes.forEach(function(checkbox) {
                    checkbox.addEventListener('change', function() {
                        syncSelectAllState();
                        updateBulkToolbarState();
                    });
                });

                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        var checked = selectAllCheckbox.checked;
                        getVisibleRows().forEach(function(row) {
                            var checkbox = getRowCheckbox(row);
                            if (checkbox) {
                                checkbox.checked = checked;
                            }
                        });

                        syncSelectAllState();
                        updateBulkToolbarState();
                    });
                }

                var filterRows = function() {
                    if (!searchInput) {
                        syncSelectAllState();
                        updateBulkToolbarState();
                        return;
                    }

                    var term = searchInput.value.trim().toLowerCase();
                    var visibleRows = 0;

                    rows.forEach(function(row) {
                        var name = (row.getAttribute('data-name') || '').toLowerCase();
                        var match = term === '' || name.indexOf(term) !== -1;
                        row.classList.toggle('d-none', !match);

                        if (!match) {
                            var checkbox = getRowCheckbox(row);
                            if (checkbox) {
                                checkbox.checked = false;
                            }
                        }

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

                    syncSelectAllState();
                    updateBulkToolbarState();
                };

                if (searchInput) {
                    searchInput.addEventListener('input', filterRows);
                }

                var singleDeleteTriggers = Array.prototype.slice.call(document.querySelectorAll('[data-drive-trash-trigger]'));
                singleDeleteTriggers.forEach(function(trigger) {
                    trigger.addEventListener('click', function() {
                        var formId = trigger.getAttribute('data-drive-trash-trigger');
                        if (!formId) {
                            return;
                        }

                        var form = document.getElementById(formId);
                        if (!form) {
                            return;
                        }

                        if (!window.confirm(@json(__('drive.browse.confirm_delete')))) {
                            return;
                        }

                        form.submit();
                    });
                });

                if (bulkDeleteTrigger && bulkForm) {
                    bulkDeleteTrigger.addEventListener('click', function() {
                        var checkedIds = rows
                            .map(getRowCheckbox)
                            .filter(function(checkbox) {
                                return checkbox && checkbox.checked;
                            })
                            .map(function(checkbox) {
                                return checkbox.value;
                            });

                        if (checkedIds.length === 0) {
                            return;
                        }

                        if (!window.confirm(@json(__('drive.browse.confirm_bulk_delete')))) {
                            return;
                        }

                        Array.prototype.slice.call(bulkForm.querySelectorAll('input[name="item_ids[]"]')).forEach(function(input) {
                            input.remove();
                        });

                        checkedIds.forEach(function(itemId) {
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'item_ids[]';
                            input.value = itemId;
                            bulkForm.appendChild(input);
                        });

                        bulkForm.submit();
                    });
                }

                filterRows();
            });
        </script>
    @endpush
</x-app-layout>

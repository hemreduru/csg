<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
use App\Http\Requests\Drive\BulkTrashDriveItemsRequest;
use App\Http\Requests\Drive\CreateDriveFolderRequest;
use App\Http\Requests\Drive\RenameDriveItemRequest;
use App\Http\Requests\Drive\TrashDriveItemRequest;
use App\Http\Requests\Drive\UploadDriveFileRequest;
use App\Models\DriveConnection;
use App\Services\GoogleDrive\DriveGateway;
use App\Support\AppLog;
use App\Support\DriveNameSanitizer;
use App\Support\DriveItemPresentation;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriveController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $conn = $request->user()
            ->driveConnections()
            ->orderByDesc('is_default')
            ->first();

        if (! $conn) {
            return redirect()->route('connections.google.index');
        }

        return redirect()->route('drive.connection', $conn);
    }

    public function connection(Request $request, DriveConnection $connection): RedirectResponse
    {
        $this->authorizeConnection($request, $connection);

        return redirect()->route('drive.browse', [$connection, 'path' => '']);
    }

    public function browse(Request $request, DriveConnection $connection, DriveGateway $gateway): View
    {
        $this->authorizeConnection($request, $connection);

        $path = (string) $request->query('path', '');
        $path = ltrim($path, '/');

        $segments = array_values(array_filter(explode('/', $path), fn (string $s) => $s !== ''));

        $parentPath = '';
        if ($segments !== []) {
            $parentSegments = $segments;
            array_pop($parentSegments);
            $parentPath = implode('/', $parentSegments);
        }

        try {
            $items = $gateway->list($connection, $path);
        } catch (Exception $e) {
            AppLog::warning('Drive file listing failed', [
                'user_id' => $request->user()->id,
                'drive_connection_id' => $connection->id,
                'target_path' => $path,
            ], $e);

            $items = [];
            $request->session()->flash('error', __('drive.errors.list_failed'));
        }

        return view('drive.browse', [
            'activeDriveConnection' => $connection,
            'path' => $path,
            'parentPath' => $parentPath,
            'items' => $items,
        ]);
    }

    public function createFolder(CreateDriveFolderRequest $request, DriveConnection $connection, DriveGateway $gateway): RedirectResponse
    {
        $this->authorizeConnection($request, $connection);

        $path = ltrim((string) $request->validated('path', ''), '/');

        try {
            $name = DriveNameSanitizer::sanitize((string) $request->validated('name'));
        } catch (Exception $e) {
            return redirect()->route('drive.browse', [$connection, 'path' => $path])
                ->with('error', __('drive.errors.invalid_name'));
        }

        try {
            $gateway->createFolder($connection, $path, $name);

            AppLog::info('Drive folder created', [
                'user_id' => $request->user()->id,
                'drive_connection_id' => $connection->id,
                'target_path' => $path,
                'name' => $name,
            ]);
        } catch (Exception $e) {
            AppLog::warning('Drive folder creation failed', [
                'user_id' => $request->user()->id,
                'drive_connection_id' => $connection->id,
                'target_path' => $path,
                'name' => $name,
            ], $e);

            return redirect()->route('drive.browse', [$connection, 'path' => $path])
                ->with('error', __('drive.errors.create_folder_failed'));
        }

        return redirect()->route('drive.browse', [$connection, 'path' => $path])
            ->with('status', __('drive.messages.folder_created'));
    }

    public function upload(UploadDriveFileRequest $request, DriveConnection $connection, DriveGateway $gateway): RedirectResponse
    {
        $this->authorizeConnection($request, $connection);

        $path = ltrim((string) $request->validated('path', ''), '/');

        $file = $request->file('file');
        if (! $file || ! $file->isValid()) {
            return redirect()->route('drive.browse', [$connection, 'path' => $path])
                ->with('error', __('drive.errors.upload_failed'));
        }

        try {
            $safeName = DriveNameSanitizer::sanitize((string) $file->getClientOriginalName());
        } catch (Exception $e) {
            return redirect()->route('drive.browse', [$connection, 'path' => $path])
                ->with('error', __('drive.errors.invalid_name'));
        }

        try {
            $gateway->uploadFile($connection, $path, $file, $safeName);

            AppLog::info('Drive file uploaded', [
                'user_id' => $request->user()->id,
                'drive_connection_id' => $connection->id,
                'target_path' => $path,
                'name' => $safeName,
                'size' => $file->getSize(),
            ]);
        } catch (Exception $e) {
            AppLog::warning('Drive file upload failed', [
                'user_id' => $request->user()->id,
                'drive_connection_id' => $connection->id,
                'target_path' => $path,
                'name' => $safeName,
            ], $e);

            return redirect()->route('drive.browse', [$connection, 'path' => $path])
                ->with('error', __('drive.errors.upload_failed'));
        }

        return redirect()->route('drive.browse', [$connection, 'path' => $path])
            ->with('status', __('drive.messages.file_uploaded'));
    }

    public function renameItem(RenameDriveItemRequest $request, DriveConnection $connection, string $itemId, DriveGateway $gateway): RedirectResponse
    {
        $this->authorizeConnection($request, $connection);

        $path = ltrim((string) $request->validated('path', ''), '/');

        try {
            $name = DriveNameSanitizer::sanitize((string) $request->validated('name'));
        } catch (Exception $e) {
            return redirect()->route('drive.browse', [$connection, 'path' => $path])
                ->with('error', __('drive.errors.invalid_name'));
        }

        try {
            $gateway->renameItem($connection, $itemId, $name);

            AppLog::info('Drive item renamed', [
                'user_id' => $request->user()->id,
                'drive_connection_id' => $connection->id,
                'item_id' => $itemId,
                'target_path' => $path,
                'name' => $name,
            ]);
        } catch (Exception $e) {
            AppLog::warning('Drive item rename failed', [
                'user_id' => $request->user()->id,
                'drive_connection_id' => $connection->id,
                'item_id' => $itemId,
                'target_path' => $path,
                'name' => $name,
            ], $e);

            return redirect()->route('drive.browse', [$connection, 'path' => $path])
                ->with('error', __('drive.errors.rename_failed'));
        }

        return redirect()->route('drive.browse', [$connection, 'path' => $path])
            ->with('status', __('drive.messages.item_renamed'));
    }

    public function trashItem(TrashDriveItemRequest $request, DriveConnection $connection, string $itemId, DriveGateway $gateway): RedirectResponse
    {
        $this->authorizeConnection($request, $connection);

        $path = ltrim((string) $request->validated('path', ''), '/');

        try {
            $gateway->trashItem($connection, $itemId);

            AppLog::info('Drive item moved to trash', [
                'user_id' => $request->user()->id,
                'drive_connection_id' => $connection->id,
                'item_id' => $itemId,
                'target_path' => $path,
            ]);
        } catch (Exception $e) {
            AppLog::warning('Drive item trash operation failed', [
                'user_id' => $request->user()->id,
                'drive_connection_id' => $connection->id,
                'item_id' => $itemId,
                'target_path' => $path,
            ], $e);

            return redirect()->route('drive.browse', [$connection, 'path' => $path])
                ->with('error', __('drive.errors.trash_failed'));
        }

        return redirect()->route('drive.browse', [$connection, 'path' => $path])
            ->with('status', __('drive.messages.item_trashed'));
    }

    public function bulkTrashItems(BulkTrashDriveItemsRequest $request, DriveConnection $connection, DriveGateway $gateway): RedirectResponse
    {
        $this->authorizeConnection($request, $connection);

        $path = ltrim((string) $request->validated('path', ''), '/');
        /** @var array<int, string> $itemIds */
        $itemIds = array_values(array_unique($request->validated('item_ids', [])));

        $trashedCount = 0;
        $failedItemIds = [];

        foreach ($itemIds as $itemId) {
            try {
                $gateway->trashItem($connection, $itemId);
                $trashedCount++;
            } catch (Exception $e) {
                $failedItemIds[] = $itemId;

                AppLog::warning('Drive bulk trash item failed', [
                    'user_id' => $request->user()->id,
                    'drive_connection_id' => $connection->id,
                    'item_id' => $itemId,
                    'target_path' => $path,
                ], $e);
            }
        }

        AppLog::info('Drive bulk trash executed', [
            'user_id' => $request->user()->id,
            'drive_connection_id' => $connection->id,
            'target_path' => $path,
            'selected_count' => count($itemIds),
            'trashed_count' => $trashedCount,
            'failed_count' => count($failedItemIds),
            'failed_item_ids' => $failedItemIds,
        ]);

        if ($trashedCount === 0) {
            return redirect()->route('drive.browse', [$connection, 'path' => $path])
                ->with('error', __('drive.errors.bulk_trash_failed'));
        }

        if ($failedItemIds !== []) {
            return redirect()->route('drive.browse', [$connection, 'path' => $path])
                ->with('status', trans_choice('drive.messages.bulk_items_partially_trashed', $trashedCount, ['count' => $trashedCount]));
        }

        return redirect()->route('drive.browse', [$connection, 'path' => $path])
            ->with('status', trans_choice('drive.messages.bulk_items_trashed', $trashedCount, ['count' => $trashedCount]));
    }

    public function preview(Request $request, DriveConnection $connection, string $itemId, DriveGateway $gateway): StreamedResponse|RedirectResponse
    {
        $this->authorizeConnection($request, $connection);
        $fallbackPath = ltrim((string) $request->query('path', ''), '/');

        try {
            $meta = $gateway->getMetadata($connection, $itemId);
            $mimeType = (string) ($meta['mime_type'] ?? '');
            $webViewLink = is_string($meta['web_view_link'] ?? null) ? (string) $meta['web_view_link'] : null;

            if ($mimeType === DriveGateway::FOLDER_MIME_TYPE) {
                return redirect()->route('drive.browse', [$connection, 'path' => $fallbackPath])
                    ->with('error', __('drive.errors.preview_folder_not_supported'));
            }

            if (! DriveItemPresentation::canPreview($mimeType, $webViewLink)) {
                return redirect()->route('drive.items.download', [$connection, $itemId]);
            }

            $name = $this->sanitizeOutputName((string) ($meta['name'] ?? ''));

            if (str_starts_with($mimeType, DriveGateway::GOOGLE_APPS_MIME_PREFIX)) {
                $exportMimeType = $this->resolvePreviewExportMimeType($meta['export_links'] ?? []);
                $response = null;
                $contentType = null;
                $previewName = null;

                if ($exportMimeType !== null) {
                    try {
                        $response = $gateway->exportFile($connection, $itemId, $exportMimeType);
                        $contentType = $exportMimeType;
                        $previewName = $this->appendExtensionByMimeType($name, $exportMimeType);
                    } catch (Exception $exportException) {
                        AppLog::warning('Drive preview export failed, trying Google viewer fallback', [
                            'user_id' => $request->user()?->id,
                            'drive_connection_id' => $connection->id,
                            'item_id' => $itemId,
                            'mime_type' => $mimeType,
                            'export_mime_type' => $exportMimeType,
                        ], $exportException);
                    }
                }

                if (! $response) {
                    if (is_string($webViewLink) && $webViewLink !== '') {
                        AppLog::info('Drive preview redirected to Google viewer', [
                            'user_id' => $request->user()?->id,
                            'drive_connection_id' => $connection->id,
                            'item_id' => $itemId,
                            'mime_type' => $mimeType,
                            'web_view_link' => $webViewLink,
                        ]);

                        return redirect()->away($webViewLink);
                    }

                    return redirect()->route('drive.items.download', [$connection, $itemId]);
                }
            } else {
                if (DriveItemPresentation::shouldUseGoogleViewer($mimeType, $webViewLink)) {
                    AppLog::info('Drive preview redirected to Google viewer', [
                        'user_id' => $request->user()?->id,
                        'drive_connection_id' => $connection->id,
                        'item_id' => $itemId,
                        'mime_type' => $mimeType,
                        'web_view_link' => $webViewLink,
                    ]);

                    return redirect()->away($webViewLink);
                }

                $response = $gateway->downloadFile($connection, $itemId);
                $contentType = $mimeType !== '' ? $mimeType : 'application/octet-stream';
                $previewName = $name;
            }

            AppLog::info('Drive preview opened', [
                'user_id' => $request->user()?->id,
                'drive_connection_id' => $connection->id,
                'item_id' => $itemId,
                'mime_type' => $mimeType,
                'content_type' => $contentType,
            ]);

            $body = $response->getBody();

            return response()->stream(function () use ($body) {
                while (! $body->eof()) {
                    echo $body->read(8192);
                }
            }, 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => $this->buildInlineContentDisposition($previewName),
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, max-age=0, must-revalidate',
            ]);
        } catch (Exception $e) {
            AppLog::warning('Drive preview failed', [
                'user_id' => $request->user()?->id,
                'drive_connection_id' => $connection->id,
                'item_id' => $itemId,
            ], $e);

            return redirect()->route('drive.browse', [$connection, 'path' => $fallbackPath])
                ->with('error', __('drive.errors.preview_failed'));
        }
    }

    public function download(Request $request, DriveConnection $connection, string $itemId, DriveGateway $gateway): StreamedResponse|RedirectResponse
    {
        $this->authorizeConnection($request, $connection);

        try {
            $meta = $gateway->getMetadata($connection, $itemId);

            $mimeType = $meta['mime_type'];

            try {
                $name = DriveNameSanitizer::sanitize($meta['name']);
            } catch (Exception $e) {
                $name = (string) config('app.name');
            }

            if (str_starts_with($mimeType, DriveGateway::GOOGLE_APPS_MIME_PREFIX)) {
                $response = $gateway->exportFile($connection, $itemId, DriveGateway::EXPORT_MIME_PDF);
                $downloadName = $name.'.pdf';
                $contentType = DriveGateway::EXPORT_MIME_PDF;
            } else {
                $response = $gateway->downloadFile($connection, $itemId);
                $downloadName = $name;
                $contentType = $mimeType ?: 'application/octet-stream';
            }

            AppLog::info('Drive download started', [
                'user_id' => $request->user()?->id,
                'drive_connection_id' => $connection->id,
                'item_id' => $itemId,
                'mime_type' => $mimeType,
            ]);

            $body = $response->getBody();

            return response()->streamDownload(function () use ($body) {
                while (! $body->eof()) {
                    echo $body->read(8192);
                }
            }, $downloadName, [
                'Content-Type' => $contentType,
            ]);
        } catch (Exception $e) {
            AppLog::warning('Drive download failed', [
                'user_id' => $request->user()?->id,
                'drive_connection_id' => $connection->id,
                'item_id' => $itemId,
            ], $e);

            return back()->with('error', __('drive.errors.download_failed'));
        }
    }

    /**
     * @param  array<string, string>  $exportLinks
     */
    private function resolvePreviewExportMimeType(array $exportLinks): ?string
    {
        if ($exportLinks === []) {
            return null;
        }

        $priority = [
            DriveGateway::EXPORT_MIME_PDF,
            'image/png',
            'text/plain',
            'text/csv',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];

        foreach ($priority as $mimeType) {
            if (isset($exportLinks[$mimeType])) {
                return $mimeType;
            }
        }

        $first = array_key_first($exportLinks);

        return is_string($first) ? $first : null;
    }

    private function sanitizeOutputName(string $name): string
    {
        try {
            return DriveNameSanitizer::sanitize($name);
        } catch (Exception $e) {
            return (string) config('app.name');
        }
    }

    private function appendExtensionByMimeType(string $fileName, string $mimeType): string
    {
        $extension = $this->resolveExtensionByMimeType($mimeType);
        if ($extension === null) {
            return $fileName;
        }

        $currentExtension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
        if ($currentExtension === strtolower($extension)) {
            return $fileName;
        }

        return $fileName.'.'.$extension;
    }

    private function resolveExtensionByMimeType(string $mimeType): ?string
    {
        return match ($mimeType) {
            DriveGateway::EXPORT_MIME_PDF => 'pdf',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'image/png' => 'png',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            default => null,
        };
    }

    private function buildInlineContentDisposition(string $fileName): string
    {
        $asciiName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName) ?: 'preview';
        $utf8Name = rawurlencode($fileName);

        return "inline; filename=\"{$asciiName}\"; filename*=UTF-8''{$utf8Name}";
    }

    private function authorizeConnection(Request $request, DriveConnection $connection): void
    {
        if ((int) $connection->user_id !== (int) $request->user()->id) {
            abort(404);
        }
    }
}

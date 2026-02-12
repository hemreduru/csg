<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
use App\Http\Requests\Drive\CreateDriveFolderRequest;
use App\Http\Requests\Drive\RenameDriveItemRequest;
use App\Http\Requests\Drive\TrashDriveItemRequest;
use App\Http\Requests\Drive\UploadDriveFileRequest;
use App\Models\DriveConnection;
use App\Services\GoogleDrive\DriveGateway;
use App\Support\AppLog;
use App\Support\DriveNameSanitizer;
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

        $breadcrumbs = [];
        $segments = array_values(array_filter(explode('/', $path), fn (string $s) => $s !== ''));
        $acc = '';
        foreach ($segments as $seg) {
            $acc = $acc === '' ? $seg : $acc.'/'.$seg;
            $breadcrumbs[] = [
                'label' => $seg,
                'path' => $acc,
            ];
        }

        $parentPath = '';
        if (! empty($segments)) {
            array_pop($segments);
            $parentPath = implode('/', $segments);
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
            'breadcrumbs' => $breadcrumbs,
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

            if (str_starts_with($mimeType, 'application/vnd.google-apps.')) {
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

    private function authorizeConnection(Request $request, DriveConnection $connection): void
    {
        if ((int) $connection->user_id !== (int) $request->user()->id) {
            abort(404);
        }
    }
}

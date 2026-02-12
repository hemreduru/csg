<?php

namespace App\Services\GoogleDrive;

use App\Models\DriveConnection;
use App\Support\AppLog;
use Exception;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Psr\Http\Message\ResponseInterface;

class DriveGateway
{
    public const EXPORT_MIME_PDF = 'application/pdf';
    public const FOLDER_MIME_TYPE = 'application/vnd.google-apps.folder';

    public function __construct(
        private readonly GoogleDriveClientFactory $factory,
    ) {
    }

    /**
     * @return array<int, array{
     *   id: string,
     *   name: string,
     *   mime_type: string,
     *   is_folder: bool,
     *   size: int|null,
     *   modified_time: string|null
     * }>
     */
    public function list(DriveConnection $connection, string $path): array
    {
        $service = $this->factory->makeDriveService($connection);

        $folderId = $this->resolveFolderIdByPath($service, $path);

        $params = [
            'q' => "trashed = false and '{$folderId}' in parents",
            'fields' => 'files(id,name,mimeType,size,modifiedTime)',
            'pageSize' => 100,
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ];

        $results = $service->files->listFiles($params);

        $out = [];

        /** @var DriveFile $file */
        foreach ($results->getFiles() as $file) {
            $mime = (string) $file->getMimeType();
            $modified = $file->getModifiedTime();

            $out[] = [
                'id' => (string) $file->getId(),
                'name' => (string) $file->getName(),
                'mime_type' => $mime,
                'is_folder' => $mime === self::FOLDER_MIME_TYPE,
                'size' => $file->getSize() ? (int) $file->getSize() : null,
                'modified_time' => $modified ? Carbon::parse($modified)->toDateTimeString() : null,
            ];
        }

        $collator = class_exists(\Collator::class) ? new \Collator(app()->getLocale()) : null;

        usort($out, function (array $a, array $b) use ($collator) {
            if ($a['is_folder'] && ! $b['is_folder']) {
                return -1;
            }
            if (! $a['is_folder'] && $b['is_folder']) {
                return 1;
            }

            if ($collator) {
                return $collator->compare($a['name'], $b['name']);
            }

            return strcasecmp($a['name'], $b['name']);
        });

        return $out;
    }

    public function createFolder(DriveConnection $connection, string $path, string $name): DriveFile
    {
        $service = $this->factory->makeDriveService($connection);

        $parentId = $this->resolveFolderIdByPath($service, $path);

        $meta = new DriveFile();
        $meta->setName($name);
        $meta->setMimeType(self::FOLDER_MIME_TYPE);
        $meta->setParents([$parentId]);

        return $service->files->create($meta, [
            'fields' => 'id,name,mimeType,modifiedTime',
            'supportsAllDrives' => true,
        ]);
    }

    public function uploadFile(DriveConnection $connection, string $path, UploadedFile $file, string $name): DriveFile
    {
        $service = $this->factory->makeDriveService($connection);

        $parentId = $this->resolveFolderIdByPath($service, $path);

        $meta = new DriveFile();
        $meta->setName($name);
        $meta->setParents([$parentId]);

        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $data = file_get_contents($file->getRealPath());

        if ($data === false) {
            throw new Exception('Failed to read uploaded file data.');
        }

        return $service->files->create($meta, [
            'data' => $data,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id,name,mimeType,size,modifiedTime',
            'supportsAllDrives' => true,
        ]);
    }

    public function renameItem(DriveConnection $connection, string $itemId, string $name): DriveFile
    {
        $service = $this->factory->makeDriveService($connection);

        $meta = new DriveFile();
        $meta->setName($name);

        return $service->files->update($itemId, $meta, [
            'fields' => 'id,name',
            'supportsAllDrives' => true,
        ]);
    }

    public function trashItem(DriveConnection $connection, string $itemId): DriveFile
    {
        $service = $this->factory->makeDriveService($connection);

        $meta = new DriveFile();
        $meta->setTrashed(true);

        return $service->files->update($itemId, $meta, [
            'fields' => 'id,trashed',
            'supportsAllDrives' => true,
        ]);
    }

    /**
     * @return array{name: string, mime_type: string, size: int|null}
     */
    public function getMetadata(DriveConnection $connection, string $itemId): array
    {
        $service = $this->factory->makeDriveService($connection);

        $file = $service->files->get($itemId, [
            'fields' => 'id,name,mimeType,size',
            'supportsAllDrives' => true,
        ]);

        return [
            'name' => (string) $file->getName(),
            'mime_type' => (string) $file->getMimeType(),
            'size' => $file->getSize() ? (int) $file->getSize() : null,
        ];
    }

    public function downloadFile(DriveConnection $connection, string $itemId): ResponseInterface
    {
        $service = $this->factory->makeDriveService($connection);

        /** @var ResponseInterface $response */
        $response = $service->files->get($itemId, [
            'alt' => 'media',
            'supportsAllDrives' => true,
        ]);

        return $response;
    }

    public function exportFile(DriveConnection $connection, string $itemId, string $mimeType): ResponseInterface
    {
        $service = $this->factory->makeDriveService($connection);

        /** @var ResponseInterface $response */
        $response = $service->files->export($itemId, $mimeType, [
            'supportsAllDrives' => true,
        ]);

        return $response;
    }

    private function resolveFolderIdByPath(Drive $service, string $path): string
    {
        $path = trim($path, '/');

        if ($path === '') {
            return 'root';
        }

        $segments = array_values(array_filter(explode('/', $path), fn (string $s) => $s !== ''));

        $parentId = 'root';

        foreach ($segments as $segment) {
            $escapedName = str_replace(['\\', '\''], ['\\\\', "\\'"], $segment);

            $params = [
                'q' => "trashed = false and mimeType = '" . self::FOLDER_MIME_TYPE . "' and name = '{$escapedName}' and '{$parentId}' in parents",
                'fields' => 'files(id,name)',
                'pageSize' => 10,
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
            ];

            $results = $service->files->listFiles($params);
            $files = $results->getFiles();

            if (empty($files)) {
                throw new Exception("Folder not found: {$path}");
            }

            if (count($files) > 1) {
                AppLog::warning('Duplicate folder names found while resolving path', [
                    'segment' => $segment,
                    'parent_id' => $parentId,
                    'count' => count($files),
                ]);
            }

            /** @var DriveFile $first */
            $first = $files[0];
            $parentId = (string) $first->getId();
        }

        return $parentId;
    }
}

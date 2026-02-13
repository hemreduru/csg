<?php

namespace App\Support;

use App\Services\GoogleDrive\DriveGateway;

class DriveItemPresentation
{
    /**
     * @var array<int, string>
     */
    private const OFFICE_MIME_TYPES = [
        'application/msword',
        'application/vnd.ms-excel',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.oasis.opendocument.presentation',
    ];

    /**
     * @var array<int, string>
     */
    private const ARCHIVE_EXTENSIONS = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'];

    /**
     * @var array<int, string>
     */
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'tif', 'tiff', 'heic', 'heif'];

    /**
     * @var array<int, string>
     */
    private const CODE_EXTENSIONS = ['php', 'js', 'mjs', 'cjs', 'ts', 'tsx', 'jsx', 'json', 'xml', 'html', 'css', 'scss', 'sql', 'txt', 'md', 'log', 'yml', 'yaml', 'ini', 'sh', 'bat'];

    public static function canPreview(string $mimeType, ?string $webViewLink = null): bool
    {
        if ($mimeType === DriveGateway::FOLDER_MIME_TYPE) {
            return false;
        }

        if (str_starts_with($mimeType, DriveGateway::GOOGLE_APPS_MIME_PREFIX)) {
            return true;
        }

        if (self::supportsInlinePreviewMime($mimeType)) {
            return true;
        }

        return self::isOfficeMimeType($mimeType) && is_string($webViewLink) && $webViewLink !== '';
    }

    public static function shouldUseGoogleViewer(string $mimeType, ?string $webViewLink = null): bool
    {
        return self::isOfficeMimeType($mimeType) && is_string($webViewLink) && $webViewLink !== '';
    }

    public static function supportsInlinePreviewMime(string $mimeType): bool
    {
        if ($mimeType === 'application/pdf') {
            return true;
        }

        if (in_array($mimeType, ['application/json', 'application/xml', 'text/csv'], true)) {
            return true;
        }

        foreach (['image/', 'text/', 'audio/', 'video/'] as $prefix) {
            if (str_starts_with($mimeType, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public static function resolveIconClass(string $mimeType, string $name, bool $isFolder): string
    {
        if ($isFolder) {
            return 'ki-duotone ki-folder fs-2x text-primary me-4';
        }

        if ($mimeType === 'application/pdf') {
            return 'la la-file-pdf-o fs-2x text-danger me-4';
        }

        if (self::isWordMimeType($mimeType) || self::hasExtension($name, ['doc', 'docx', 'odt', 'rtf'])) {
            return 'la la-file-word-o fs-2x text-primary me-4';
        }

        if (self::isSpreadsheetMimeType($mimeType) || self::hasExtension($name, ['xls', 'xlsx', 'csv', 'ods'])) {
            return 'la la-file-excel-o fs-2x text-success me-4';
        }

        if (self::isPresentationMimeType($mimeType) || self::hasExtension($name, ['ppt', 'pptx', 'odp'])) {
            return 'la la-file-powerpoint-o fs-2x text-warning me-4';
        }

        if (str_starts_with($mimeType, 'image/') || self::hasExtension($name, self::IMAGE_EXTENSIONS)) {
            return 'la la-file-image-o fs-2x text-info me-4';
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return 'la la-file-audio-o fs-2x text-primary me-4';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'la la-file-video-o fs-2x text-primary me-4';
        }

        if (self::hasExtension($name, self::ARCHIVE_EXTENSIONS)) {
            return 'la la-file-archive-o fs-2x text-warning me-4';
        }

        if (self::hasExtension($name, self::CODE_EXTENSIONS)) {
            return 'la la-file-code-o fs-2x text-gray-700 me-4';
        }

        return 'la la-file-o fs-2x text-gray-700 me-4';
    }

    private static function isOfficeMimeType(string $mimeType): bool
    {
        if (in_array($mimeType, self::OFFICE_MIME_TYPES, true)) {
            return true;
        }

        return self::isGoogleDocumentMimeType($mimeType);
    }

    private static function isGoogleDocumentMimeType(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/vnd.google-apps.document',
            'application/vnd.google-apps.spreadsheet',
            'application/vnd.google-apps.presentation',
            'application/vnd.google-apps.drawing',
            'application/vnd.google-apps.form',
        ], true);
    }

    private static function isWordMimeType(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.google-apps.document',
        ], true);
    }

    private static function isSpreadsheetMimeType(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.google-apps.spreadsheet',
        ], true);
    }

    private static function isPresentationMimeType(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.google-apps.presentation',
        ], true);
    }

    /**
     * @param  array<int, string>  $extensions
     */
    private static function hasExtension(string $name, array $extensions): bool
    {
        $extension = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
        if ($extension === '') {
            return false;
        }

        return in_array($extension, $extensions, true);
    }
}

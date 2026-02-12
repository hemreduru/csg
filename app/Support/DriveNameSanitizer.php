<?php

namespace App\Support;

use InvalidArgumentException;

final class DriveNameSanitizer
{
    public static function sanitize(string $name, int $maxLength = 255): string
    {
        $name = trim($name);

        // Remove control chars.
        $name = preg_replace('/[\\x00-\\x1F\\x7F]/u', '', $name) ?? '';

        // Prevent path traversal style names by stripping separators.
        $name = str_replace(['/', '\\'], '-', $name);

        // Normalize whitespace.
        $name = preg_replace('/\\s+/u', ' ', $name) ?? '';
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException('Name is empty after sanitization.');
        }

        if (function_exists('mb_substr')) {
            return mb_substr($name, 0, $maxLength);
        }

        return substr($name, 0, $maxLength);
    }
}


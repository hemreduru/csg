<?php

namespace App\Http\Requests\Drive;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string|null $path
 * @property \Illuminate\Http\UploadedFile $file
 */
class UploadDriveFileRequest extends FormRequest
{
    protected $errorBag = 'driveUpload';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'path' => ['nullable', 'string', 'max:1024'],
            // Default max size: 2MB (2048 KB).
            'file' => ['required', 'file', 'max:2048'],
        ];
    }
}

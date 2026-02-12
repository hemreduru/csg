<?php

namespace App\Http\Requests\Drive;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string|null $path
 * @property string $name
 */
class CreateDriveFolderRequest extends FormRequest
{
    protected $errorBag = 'driveCreateFolder';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'path' => ['nullable', 'string', 'max:1024'],
            'name' => ['required', 'string', 'max:255', 'not_regex:/[\\/\\\\]/'],
        ];
    }
}

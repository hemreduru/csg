<?php

namespace App\Http\Requests\Drive;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string|null $path
 */
class TrashDriveItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'path' => ['nullable', 'string', 'max:1024'],
        ];
    }
}


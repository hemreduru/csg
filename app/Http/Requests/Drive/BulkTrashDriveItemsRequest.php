<?php

namespace App\Http\Requests\Drive;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string|null $path
 * @property array<int, string> $item_ids
 */
class BulkTrashDriveItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'path' => ['nullable', 'string', 'max:1024'],
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['required', 'string', 'max:255'],
        ];
    }
}


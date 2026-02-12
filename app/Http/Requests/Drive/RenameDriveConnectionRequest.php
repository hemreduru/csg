<?php

namespace App\Http\Requests\Drive;

use App\Models\DriveConnection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string $name
 */
class RenameDriveConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        /** @var DriveConnection|null $connection */
        $connection = $this->route('connection');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('drive_connections', 'name')
                    ->where('user_id', $this->user()?->id)
                    ->ignore($connection?->id),
            ],
        ];
    }
}

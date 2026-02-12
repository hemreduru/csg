<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class DriveConnection extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'google_account_id',
        'google_account_email',
        'refresh_token_enc',
        'access_token_enc',
        'access_token_expires_at',
        'scopes',
        'status',
        'created_via',
        'is_default',
    ];

    protected $casts = [
        'access_token_expires_at' => 'datetime',
        'scopes' => 'array',
        'is_default' => 'bool',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isAccessTokenExpired(?Carbon $now = null): bool
    {
        $now ??= now();

        if (! $this->access_token_expires_at instanceof Carbon) {
            return true;
        }

        return $this->access_token_expires_at->lessThanOrEqualTo($now->addSeconds(30));
    }
}

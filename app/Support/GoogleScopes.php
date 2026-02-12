<?php

namespace App\Support;

final class GoogleScopes
{
    public const DRIVE = 'https://www.googleapis.com/auth/drive';

    public const OPENID = 'openid';

    public const EMAIL = 'email';

    public const PROFILE = 'profile';

    public static function forDriveLogin(): array
    {
        return [
            self::OPENID,
            self::EMAIL,
            self::PROFILE,
            self::DRIVE,
        ];
    }
}


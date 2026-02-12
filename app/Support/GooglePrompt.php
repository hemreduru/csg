<?php

namespace App\Support;

final class GooglePrompt
{
    public const CONSENT = 'consent';

    public const SELECT_ACCOUNT = 'select_account';

    public static function forAddAccount(): string
    {
        return self::SELECT_ACCOUNT.' '.self::CONSENT;
    }
}


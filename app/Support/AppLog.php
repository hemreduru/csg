<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

class AppLog
{
    /**
     * @param  array<string, mixed>  $context
     */
    public static function info(string $event, array $context = []): void
    {
        self::write('info', $event, 'success', $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function warning(string $event, array $context = [], ?Throwable $exception = null): void
    {
        self::write('warning', $event, 'failed', $context, $exception);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private static function write(string $level, string $event, string $status, array $context, ?Throwable $exception = null): void
    {
        $request = request();
        $userId = $context['user_id'] ?? $request?->user()?->id;
        $ip = $context['ip'] ?? $request?->ip();

        $base = [
            'event' => $event,
            'status' => $status,
            'user_id' => $userId,
            'ip' => $ip,
            'method' => $request?->method(),
            'path' => $request?->path(),
        ];

        unset($context['ip']);

        if ($exception instanceof Throwable) {
            $context = array_merge($context, GoogleApiErrorParser::parse($exception));
        }

        $text = self::buildText($event, $status, $userId, $context);

        $payload = array_filter(array_merge($base, [
            'text' => $text,
        ], $context), fn ($value) => $value !== null && $value !== '');

        Log::log($level, $text, $payload);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private static function buildText(string $event, string $status, mixed $userId, array $context): string
    {
        $subject = self::normalizeMessage($event);
        $parts = [
            $subject,
            'Durum: '.$status.'.',
            'Kullanici: '.($userId ?? 'guest').'.',
        ];

        $connectionId = $context['drive_connection_id'] ?? null;
        if ($connectionId) {
            $parts[] = 'Baglanti: '.$connectionId.'.';
        }

        $targetPath = $context['target_path'] ?? null;
        if ($targetPath !== null) {
            $parts[] = 'Path: '.($targetPath === '' ? '/' : $targetPath).'.';
        }

        $error = $context['provider_message']
            ?? $context['exception_message']
            ?? null;
        if ($error) {
            $parts[] = 'Hata: '.$error.'.';
        }

        $reason = $context['provider_reason'] ?? null;
        if ($reason) {
            $parts[] = 'Sebep: '.$reason.'.';
        }

        $actionUrl = $context['provider_action_url'] ?? null;
        if ($actionUrl) {
            $parts[] = 'Aksiyon: '.$actionUrl.'.';
        }

        return implode(' ', $parts);
    }

    private static function normalizeMessage(string $message): string
    {
        $message = trim($message);
        if ($message === '') {
            return 'Application log entry.';
        }

        if (str_ends_with($message, '.')) {
            return $message;
        }

        return $message.'.';
    }
}

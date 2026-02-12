<?php

namespace App\Support;

use Throwable;

class GoogleApiErrorParser
{
    /**
     * @return array<string, mixed>
     */
    public static function parse(Throwable $exception): array
    {
        $parsed = [
            'exception_class' => $exception::class,
            'exception_message' => $exception->getMessage(),
        ];

        $payload = json_decode($exception->getMessage(), true);
        if (! is_array($payload)) {
            return $parsed;
        }

        $error = $payload['error'] ?? null;
        if (! is_array($error)) {
            return $parsed;
        }

        $parsed['provider'] = 'google';
        $parsed['provider_http_code'] = $error['code'] ?? null;
        $parsed['provider_status'] = $error['status'] ?? null;
        $parsed['provider_message'] = $error['message'] ?? null;

        $firstError = $error['errors'][0] ?? null;
        if (is_array($firstError)) {
            $parsed['provider_reason'] = $firstError['reason'] ?? null;
            $parsed['provider_domain'] = $firstError['domain'] ?? null;
            $parsed['provider_help_url'] = $firstError['extendedHelp'] ?? null;
        }

        $details = $error['details'] ?? [];
        if (is_array($details)) {
            foreach ($details as $detail) {
                if (! is_array($detail)) {
                    continue;
                }

                if (($detail['@type'] ?? null) === 'type.googleapis.com/google.rpc.ErrorInfo') {
                    $parsed['provider_reason'] = $detail['reason'] ?? ($parsed['provider_reason'] ?? null);
                    $metadata = $detail['metadata'] ?? [];
                    if (is_array($metadata)) {
                        $parsed['provider_service'] = $metadata['service'] ?? null;
                        $parsed['provider_consumer'] = $metadata['consumer'] ?? null;
                        $parsed['provider_action_url'] = $metadata['activationUrl'] ?? null;
                    }
                }
            }
        }

        return array_filter($parsed, fn ($value) => $value !== null && $value !== '');
    }
}

<?php

namespace App\Services\GoogleDrive;

use App\Models\DriveConnection;
use App\Support\AppLog;
use Exception;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GoogleDriveClientFactory
{
    public function makeClient(DriveConnection $connection): Client
    {
        $client = new Client();

        $client->setApplicationName(config('app.name'));
        $client->setScopes($connection->scopes);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirect = config('services.google.redirect');

        if (! $clientId || ! $clientSecret || ! $redirect) {
            throw new Exception('Google OAuth client config is missing (services.google.*).');
        }

        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirect);

        if ($connection->access_token_enc) {
            /** @var array|string $token */
            $token = decrypt($connection->access_token_enc);

            if (is_string($token) && $token !== '') {
                // Backward-compatible for previously stored raw access tokens.
                $token = [
                    'access_token' => $token,
                    'created' => time(),
                    'expires_in' => 0,
                ];
            }

            $client->setAccessToken($token);
        }

        if ($client->isAccessTokenExpired()) {
            $refreshToken = decrypt($connection->refresh_token_enc);
            $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);

            if (isset($newToken['error'])) {
                AppLog::warning('Google Drive token refresh failed', [
                    'drive_connection_id' => $connection->id,
                    'provider' => 'google',
                    'provider_reason' => $newToken['error'],
                    'provider_message' => $newToken['error_description'] ?? null,
                ]);

                try {
                    DB::beginTransaction();
                    $connection->fill(['status' => 'error'])->save();
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();

                    AppLog::warning('Failed to mark Google Drive connection as error', [
                        'drive_connection_id' => $connection->id,
                    ], $e);
                }

                throw new Exception('Failed to refresh Google access token.');
            }

            $expiresAt = null;
            if (is_numeric($newToken['expires_in'] ?? null)) {
                $created = is_numeric($newToken['created'] ?? null)
                    ? Carbon::createFromTimestamp((int) $newToken['created'])
                    : now();

                $expiresAt = $created->copy()->addSeconds((int) $newToken['expires_in']);
            }

            try {
                DB::beginTransaction();

                $connection->fill([
                    'access_token_enc' => encrypt($client->getAccessToken()),
                    'access_token_expires_at' => $expiresAt,
                    'status' => 'connected',
                ])->save();

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();

                AppLog::warning('Failed to persist refreshed Google Drive token', [
                    'drive_connection_id' => $connection->id,
                ], $e);

                throw new Exception('Failed to persist refreshed Google access token.');
            }
        }

        return $client;
    }

    public function makeDriveService(DriveConnection $connection): Drive
    {
        return new Drive($this->makeClient($connection));
    }
}

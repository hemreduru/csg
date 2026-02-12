<?php

namespace App\Http\Controllers\Drive;

use App\Http\Controllers\Controller;
use App\Http\Requests\Drive\DisconnectDriveConnectionRequest;
use App\Http\Requests\Drive\RenameDriveConnectionRequest;
use App\Http\Requests\Drive\SetDefaultDriveConnectionRequest;
use App\Models\DriveConnection;
use App\Support\AppLog;
use App\Support\GooglePrompt;
use App\Support\GoogleScopes;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class GoogleConnectionsController extends Controller
{
    public function index(Request $request): View
    {
        $connections = $request->user()
            ->driveConnections()
            ->orderByDesc('is_default')
            ->orderBy('google_account_email')
            ->get();

        return view('connections.google.index', [
            'connections' => $connections,
        ]);
    }

    public function redirect(Request $request): RedirectResponse
    {
        AppLog::info('Google account connect redirect started', [
            'user_id' => $request->user()->id,
        ]);

        return Socialite::driver('google')
            ->redirectUrl(route('connections.google.callback'))
            ->scopes(GoogleScopes::forDriveLogin())
            ->with([
                'access_type' => 'offline',
                'prompt' => GooglePrompt::forAddAccount(),
            ])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('connections.google.callback'))
                ->user();
        } catch (Exception $e) {
            AppLog::warning('Google account connect callback failed', [
                'user_id' => $request->user()->id,
            ], $e);

            return redirect()->route('connections.google.index')
                ->with('error', __('drive.connections.errors.connect_failed'));
        }

        $user = $request->user();

        try {
            DB::beginTransaction();

            $connection = DriveConnection::query()
                ->where('user_id', $user->id)
                ->where('google_account_id', (string) $googleUser->getId())
                ->first();

            $oauthToken = $googleUser->token;
            $oauthRefreshToken = $googleUser->refreshToken;
            $oauthExpiresIn = $googleUser->expiresIn;

            if (! $oauthRefreshToken && ! $connection) {
                throw new Exception('Google did not return a refresh token.');
            }

            $accessTokenPayload = null;
            if (is_string($oauthToken) && $oauthToken !== '') {
                $accessTokenPayload = [
                    'access_token' => $oauthToken,
                    'expires_in' => is_numeric($oauthExpiresIn) ? (int) $oauthExpiresIn : null,
                    'created' => time(),
                ];
                $accessTokenPayload = array_filter($accessTokenPayload, fn ($v) => $v !== null);
            }

            $encryptedAccessToken = $accessTokenPayload ? encrypt($accessTokenPayload) : null;
            $expiresAt = is_numeric($oauthExpiresIn) ? now()->addSeconds((int) $oauthExpiresIn) : null;

            if ($connection) {
                $update = [
                    'google_account_email' => (string) $googleUser->getEmail(),
                    'access_token_enc' => $encryptedAccessToken,
                    'access_token_expires_at' => $expiresAt,
                    'scopes' => GoogleScopes::forDriveLogin(),
                    'status' => 'connected',
                ];

                if ($oauthRefreshToken) {
                    $update['refresh_token_enc'] = encrypt($oauthRefreshToken);
                }

                $connection->fill($update)->save();
            } else {
                $isDefault = ! DriveConnection::query()->where('user_id', $user->id)->exists();

                $connection = DriveConnection::query()->create([
                    'user_id' => $user->id,
                    'name' => (string) $googleUser->getEmail(),
                    'google_account_id' => (string) $googleUser->getId(),
                    'google_account_email' => (string) $googleUser->getEmail(),
                    'refresh_token_enc' => encrypt($oauthRefreshToken),
                    'access_token_enc' => $encryptedAccessToken,
                    'access_token_expires_at' => $expiresAt,
                    'scopes' => GoogleScopes::forDriveLogin(),
                    'status' => 'connected',
                    'created_via' => 'manual_connect',
                    'is_default' => $isDefault,
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            AppLog::warning('Google account connect callback processing failed', [
                'user_id' => $user->id,
            ], $e);

            return redirect()->route('connections.google.index')
                ->with('error', __('drive.connections.errors.connect_failed'));
        }

        AppLog::info('Google account connected', [
            'user_id' => $user->id,
            'drive_connection_id' => $connection?->id,
            'google_account_id' => (string) $googleUser->getId(),
            'google_account_email' => (string) $googleUser->getEmail(),
        ]);

        return redirect()->route('connections.google.index')
            ->with('status', __('drive.connections.connected'));
    }

    public function setDefault(SetDefaultDriveConnectionRequest $request, DriveConnection $connection): RedirectResponse
    {
        $this->authorizeConnection($request, $connection);

        try {
            DB::beginTransaction();

            DriveConnection::query()
                ->where('user_id', $request->user()->id)
                ->update(['is_default' => false]);

            $connection->fill(['is_default' => true])->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            AppLog::warning('Set default Google connection failed', [
                'user_id' => $request->user()->id,
                'drive_connection_id' => $connection->id,
            ], $e);

            return back()->with('error', __('drive.connections.errors.update_failed'));
        }

        AppLog::info('Default Google connection updated', [
            'user_id' => $request->user()->id,
            'drive_connection_id' => $connection->id,
        ]);

        return back()->with('status', __('drive.connections.default_set'));
    }

    public function rename(RenameDriveConnectionRequest $request, DriveConnection $connection): RedirectResponse
    {
        $this->authorizeConnection($request, $connection);

        try {
            DB::beginTransaction();
            $connection->fill(['name' => $request->validated('name')])->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            AppLog::warning('Google connection rename failed', [
                'user_id' => $request->user()->id,
                'drive_connection_id' => $connection->id,
            ], $e);

            return back()->with('error', __('drive.connections.errors.update_failed'));
        }

        AppLog::info('Google connection renamed', [
            'user_id' => $request->user()->id,
            'drive_connection_id' => $connection->id,
        ]);

        return back()->with('status', __('drive.connections.renamed'));
    }

    public function disconnect(DisconnectDriveConnectionRequest $request, DriveConnection $connection): RedirectResponse
    {
        $this->authorizeConnection($request, $connection);

        try {
            DB::beginTransaction();

            $wasDefault = (bool) $connection->is_default;

            $connection->delete();

            $newDefaultId = null;

            if ($wasDefault) {
                // Ensure there is always 1 default connection per user (when any exist).
                DriveConnection::query()
                    ->where('user_id', $request->user()->id)
                    ->update(['is_default' => false]);

                $next = DriveConnection::query()
                    ->where('user_id', $request->user()->id)
                    ->orderBy('google_account_email')
                    ->first();

                if ($next) {
                    $next->fill(['is_default' => true])->save();
                    $newDefaultId = $next->id;
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            AppLog::warning('Google connection disconnect failed', [
                'user_id' => $request->user()->id,
                'drive_connection_id' => $connection->id,
            ], $e);

            return back()->with('error', __('drive.connections.errors.update_failed'));
        }

        AppLog::info('Google connection disconnected', [
            'user_id' => $request->user()->id,
            'drive_connection_id' => $connection->id,
            'was_default' => $wasDefault ?? null,
            'new_default_id' => $newDefaultId ?? null,
        ]);

        return back()->with('status', __('drive.connections.disconnected'));
    }

    private function authorizeConnection(Request $request, DriveConnection $connection): void
    {
        if ((int) $connection->user_id !== (int) $request->user()->id) {
            abort(404);
        }
    }
}

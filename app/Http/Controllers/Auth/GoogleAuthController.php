<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DriveConnection;
use App\Models\User;
use App\Models\UserSocialAccount;
use App\Support\AppLog;
use App\Support\GooglePrompt;
use App\Support\GoogleScopes;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $driver = Socialite::driver('google')
            ->redirectUrl(route('auth.google.callback'))
            ->scopes(GoogleScopes::forDriveLogin())
            ->with([
                'access_type' => 'offline',
                'prompt' => GooglePrompt::CONSENT,
            ]);

        AppLog::info('Google login redirect started', [
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
        ]);

        return $driver->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('auth.google.callback'))
                ->user();
        } catch (Exception $e) {
            AppLog::warning('Google login callback failed', [], $e);

            return redirect()->route('login')
                ->with('error', __('ui.auth.google_login_failed'));
        }

        try {
            DB::beginTransaction();

            $social = UserSocialAccount::query()
                ->where('provider', 'google')
                ->where('provider_user_id', (string) $googleUser->getId())
                ->first();

            $user = null;

            if ($social) {
                $user = $social->user;
            } else {
                $email = (string) $googleUser->getEmail();

                $user = User::query()->where('email', $email)->first();

                if (! $user) {
                    $user = User::query()->create([
                        'name' => (string) ($googleUser->getName() ?: $email),
                        'email' => $email,
                        'password' => Str::password(40),
                    ]);
                }

                $social = UserSocialAccount::query()->create([
                    'user_id' => $user->id,
                    'provider' => 'google',
                    'provider_user_id' => (string) $googleUser->getId(),
                    'provider_email' => (string) $googleUser->getEmail(),
                ]);
            }

            $oauthToken = $googleUser->token;
            $oauthRefreshToken = $googleUser->refreshToken;
            $oauthExpiresIn = $googleUser->expiresIn;

            $refreshToken = $oauthRefreshToken ?: null;

            $connection = DriveConnection::query()
                ->where('user_id', $user->id)
                ->where('google_account_id', (string) $googleUser->getId())
                ->first();

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

            $expiresAt = null;
            if (is_numeric($oauthExpiresIn)) {
                $expiresAt = now()->addSeconds((int) $oauthExpiresIn);
            }

            if ($connection) {
                $update = [
                    'google_account_email' => (string) $googleUser->getEmail(),
                    'access_token_enc' => $encryptedAccessToken,
                    'access_token_expires_at' => $expiresAt,
                    'scopes' => GoogleScopes::forDriveLogin(),
                    'status' => 'connected',
                ];

                // Keep the old refresh token if Google didn't return one this time.
                if ($refreshToken) {
                    $update['refresh_token_enc'] = encrypt($refreshToken);
                }

                $connection->fill($update)->save();
            } else {
                if (! $refreshToken) {
                    // Without a refresh token we can't reliably keep the connection alive.
                    throw new Exception('Google did not return a refresh token.');
                }

                $isDefault = ! DriveConnection::query()->where('user_id', $user->id)->exists();

                $connection = DriveConnection::query()->create([
                    'user_id' => $user->id,
                    'name' => (string) $googleUser->getEmail(),
                    'google_account_id' => (string) $googleUser->getId(),
                    'google_account_email' => (string) $googleUser->getEmail(),
                    'refresh_token_enc' => encrypt($refreshToken),
                    'access_token_enc' => $encryptedAccessToken,
                    'access_token_expires_at' => $expiresAt,
                    'scopes' => GoogleScopes::forDriveLogin(),
                    'status' => 'connected',
                    'created_via' => 'google_login',
                    'is_default' => $isDefault,
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            AppLog::warning('Google login callback processing failed', [], $e);

            return redirect()->route('login')
                ->with('error', __('ui.auth.google_login_failed'));
        }

        Auth::login($user, remember: true);

        AppLog::info('User logged in with Google', [
            'user_id' => $user->id,
            'drive_connection_id' => $connection->id ?? null,
        ]);

        return redirect()->route('drive.index');
    }
}

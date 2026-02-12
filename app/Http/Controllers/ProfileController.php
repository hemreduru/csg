<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\DeleteUserRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Support\AppLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $request->user()->fill($validated);

            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = null;
            }

            $request->user()->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            AppLog::warning('Profile update failed', [
                'user_id' => $request->user()?->id,
            ], $e);

            return Redirect::route('profile.edit')->with('error', __('profile.messages.update_failed'));
        }

        AppLog::info('Profile updated', [
            'user_id' => $request->user()?->id,
        ]);

        return Redirect::route('profile.edit')->with('status', __('profile.messages.updated'));
    }

    /**
     * Delete the user's account.
     */
    public function destroy(DeleteUserRequest $request): RedirectResponse
    {
        $user = $request->user();

        try {
            DB::beginTransaction();

            // Logout first because it may update remember_token via EloquentUserProvider::updateRememberToken()
            // which calls $user->save(). Deleting first can lead to a re-insert.
            Auth::logout();

            $user->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            AppLog::warning('Profile deletion failed', [
                'user_id' => $user?->id,
            ], $e);

            return back()->with('error', __('profile.messages.delete_failed'));
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        AppLog::info('Profile deleted', [
            'user_id' => $user?->id,
        ]);

        return Redirect::to('/');
    }
}

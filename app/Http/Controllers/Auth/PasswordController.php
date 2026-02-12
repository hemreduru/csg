<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Support\AppLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            AppLog::warning('Password update failed', [
                'user_id' => $request->user()?->id,
            ], $e);

            return back()->with('error', __('profile.messages.password_update_failed'));
        }

        AppLog::info('Password updated', [
            'user_id' => $request->user()?->id,
        ]);

        return back()->with('status', __('profile.messages.password_updated'));
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Support\AppLog;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            AppLog::warning('User registration failed', [
                'email' => $validated['email'] ?? null,
            ], $e);

            return back()->with('error', __('auth.register.messages.failed'));
        }

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        AppLog::info('User registered', [
            'user_id' => $user->id,
        ]);

        return redirect()->intended(route('drive.index', absolute: false));
    }
}

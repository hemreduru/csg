<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLocaleRequest;
use App\Support\AppLog;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function update(UpdateLocaleRequest $request): RedirectResponse
    {
        $locale = $request->validated('locale');

        $request->session()->put('locale', $locale);

        AppLog::info('Locale updated', [
            'user_id' => $request->user()?->id,
            'locale' => $locale,
        ]);

        return back();
    }
}

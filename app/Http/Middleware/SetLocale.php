<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale');

        if (in_array($locale, ['en', 'tr'], true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}


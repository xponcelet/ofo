<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = null;

        // 1. User connecté → priorité à sa préférence en DB
        if (Auth::check() && Auth::user()->locale) {
            $locale = Auth::user()->locale;

            // 2. Sinon session
        } elseif (session()->has('locale')) {
            $locale = session('locale');

            // 3. Sinon cookie
        } elseif ($request->hasCookie('locale')) {
            $locale = $request->cookie('locale');

            // 4. Sinon fallback config
        } else {
            $locale = config('app.locale');
        }

        //  Sécurité : ne pas accepter de locale hors whitelist
        if (! in_array($locale, ['fr', 'en', 'nl'])) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        // 1. Si l'utilisateur est connecté et a une locale en DB
        if (auth()->check() && auth()->user()->locale) {
            App::setLocale(auth()->user()->locale);

            // 2. Sinon on regarde la session
        } elseif (session()->has('locale')) {
            App::setLocale(session('locale'));

            // 3. Sinon on regarde le cookie
        } elseif ($request->cookie('locale')) {
            App::setLocale($request->cookie('locale'));

            // 4. Sinon fallback vers config/app.php
        } else {
            App::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}

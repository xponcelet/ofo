<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        if ($request->user()) {
            // si l'utilisateur est connecté, on prend sa langue
            $locale = $request->user()->locale;
        } else {
            // sinon on regarde en session
            $locale = Session::get('locale', config('app.locale'));
        }

        App::setLocale($locale);

        return $next($request);
    }
}

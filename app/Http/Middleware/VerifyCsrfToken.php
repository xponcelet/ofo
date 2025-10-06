<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;

class VerifyCsrfToken extends Middleware
{
    /**
     * Les URIs qui doivent être exclues de la vérification CSRF.
     *
     * @var array<int, string>
     */
    protected $except = [
        // tu peux exclure certaines routes ici si besoin, ex :
        // 'webhook/*',
    ];

    /**
     * Surcharge pour loguer des infos de debug quand on a une erreur 419.
     */
    protected function tokensMatch($request)
    {
        $result = parent::tokensMatch($request);

        if (!$result) {
            \Log::warning('⚠️ CSRF TOKEN MISMATCH', [
                'path' => $request->path(),
                'session_token' => $request->session()->token(),
                'header_token' => $request->header('X-CSRF-TOKEN'),
                'xsrf_cookie' => $request->cookie('XSRF-TOKEN'),
                'session_cookie' => $request->cookie(config('session.cookie')),
            ]);
        }

        return $result;
    }
}


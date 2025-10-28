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
        // Pour tester temporairement
        '/register',
        '/login',
    ];

    public function handle($request, \Closure $next)
    {
        \Log::info('🔍 CSRF Middleware - Début', [
            'path' => $request->path(),
            'method' => $request->method(),
            'has_session' => $request->hasSession(),
            'session_id' => $request->session()->getId(),
            'csrf_from_session' => $request->session()->token(),
            'csrf_from_header' => $request->header('X-CSRF-TOKEN'),
            'xsrf_from_header' => $request->header('X-XSRF-TOKEN'),
        ]);

        return parent::handle($request, $next);
    }

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


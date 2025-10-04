<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    public function switch($locale, Request $request)
    {
        if (! in_array($locale, ['fr', 'en', 'nl'])) {
            abort(400, 'Langue non supportée');
        }

        if (Auth::check()) {
            // Met à jour en DB
            $user = Auth::user();
            $user->update(['locale' => $locale]);

            // ⚡ Recharge l'utilisateur pour que Auth::user()->locale soit correct immédiatement
            Auth::setUser($user->fresh());

        } else {
            // Stocke en session et cookie (fallback pour invités)
            session(['locale' => $locale]);
            Cookie::queue('locale', $locale, 60 * 24 * 365); // 1 an
        }

        // Toujours garder une trace côté session/cookie, même connecté (fallback)
        session(['locale' => $locale]);
        Cookie::queue('locale', $locale, 60 * 24 * 365);

        return back();
    }
}

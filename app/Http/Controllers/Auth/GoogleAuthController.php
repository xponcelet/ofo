<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Http\Request;
use Carbon\Carbon;
class GoogleAuthController extends Controller
{
    public function redirect()
    {
        //  return Socialite::driver('google')->stateless()->redirect();
        return Socialite::driver('google')
        ->with(['prompt' => 'select_account']) // 🔁 Toujours forcer le choix du compte
        ->redirect();
    }


    public function callback(Request $request)
    {
        try {
            // Get the user information from Google
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect('/login')->withErrors([
                'google_auth' => 'Erreur lors de l\'authentification avec Google.',
            ]);
        }
        if (!$googleUser || !$googleUser->email) {
            return redirect('/login')->withErrors([
                'google_auth' => 'Impossible de récupérer votre adresse email depuis Google.',
            ]);
        }
        // Recherche d’un utilisateur avec cette adresse email
        $user = User::where('email', $googleUser->email)->first();

        if ($user) {
            // Si l'utilisateur existe déjà mais sans google_id, on considère qu'il s'est inscrit manuellement
            if (!$user->google_id) {
                return redirect('/login')->withErrors([
                    'email' => 'Ce compte existe déjà. Veuillez vous connecter avec votre email et mot de passe.',
                ]);
            }

            // Si tout est ok : connexion
            Auth::login($user);
        } else {
            // Nouveau compte via Google
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'password' => bcrypt(Str::random(32)),
            ]);

            $user->markEmailAsVerified();

            Auth::login($user);
        }
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }
}

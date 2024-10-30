<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // Redirige vers Google pour l'authentification
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Gère le callback de Google après l'authentification
    public function handleGoogleCallback()
    {
        /*try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Cherche l'utilisateur dans la base de données
            $user = User::where('email', $googleUser->getEmail())->first();

            // Si l'utilisateur n'existe pas, le créer
            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt('default_password'), // Vous pouvez définir un mot de passe aléatoire
                ]);
            }

            // Authentifie l'utilisateur
            Auth::login($user);

            // Redirige vers la page d'accueil ou autre
            return redirect()->intended('dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->withErrors('Impossible de se connecter avec Google.');
        }*/
        return redirect('/');
    }
}

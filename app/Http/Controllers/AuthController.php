<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function logOut()
    {
        Session::flush();
        return redirect(route('auth.connection'))->with('success', 'Vous êtes déconnecté');
    }

    public function processInscription(Request $request)
    {
        if (User::where('email', $request->email)->first() !== null ) { // Un compte utilise déjà cette adresse mail
            return redirect(route('auth.inscription'))->with('error', 'Un compte utilise déja cet email');
        }
        if ($request->pwd !== $request->pwdConf) { // Mots de passe ne correspondent pas
            return redirect(route('auth.connection'))->with('error', 'Les mots de passe ne correspondent pas');
        }
        $user = (new UserController())->createNewUser($request);
        Session::put('isAdmin', $user->id_role === 0);
        Session::put('user_id', $user);
        return redirect('/')->with('success', 'Inscription effectuée');
    }

    public function processConnection(Request $request)
    {
        $user = (new UserController())->getUser($request);
        if ($user === null) { // On essaie de se connecter sans avoir de compte
            return redirect(route('auth.connection'))->with('error', 'Aucun compte relié à cette adresse mail, créez votre compte');
        }
        if (!$user->password === hash('sha256', $request->pwd)) // on se trompe de mot de passe
        {
            return redirect(route('auth.connection'))->with('error', 'Mail ou mot de passe incorrect');
        }
        Session::put('isAdmin', $user->id_role === 0);
        Session::put('user_id', $user->id_utilisateur);
        return redirect('/')->with('success', 'Connection effectuée');
    }

    // Redirige vers Google pour l'authentification
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Gère le callback de Google après l'authentification
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->setHttpClient(new Client(['verify' => false]))->user();

        // Cherche l'utilisateur dans la base de données
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user === null) { // Inscription de l'utilisateur, car aucun compte avec l'email fourni par Google
            $user = User::create([
                'nom' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => bcrypt('default_password'),
            ]);
        } else if ($user->google_id === null) { // Utilisateur existant, mais qui utilise Google pour la première fois
            $user->google_id = $googleUser->getId();
            $user->save();
        } // L'utilisateur existe et cherche à se connecter

        // Authentifie l'utilisateur
        Session::put('isAdmin', $user->id_role === 0);
        Session::put('user_id', $user->id_utilisateur);

        // Redirige vers la page d'accueil ou autre
        return redirect('/')->with('success', 'Connection via google effectuée');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Champ de saisie de l'identifiant dans le formulaire : "login" plutôt
     * que "email", car il accepte aussi bien un email qu'un numéro de
     * téléphone (pour les comptes Accueil).
     */
    public function username()
    {
        return 'login';
    }

    /**
     * Identifie contre quelle colonne authentifier l'utilisateur : "tel" si
     * la saisie ressemble à un numéro de téléphone (8 chiffres commençant
     * par 2, 3 ou 4), "email" sinon — pour que les comptes Accueil puissent
     * se connecter par téléphone plutôt que par email.
     */
    protected function credentials(Request $request)
    {
        $login = $request->input($this->username());
        $field = preg_match('/^[234]\d{7}$/', (string) $login) ? 'tel' : 'email';

        return [$field => $login, 'password' => $request->input('password')];
    }
}

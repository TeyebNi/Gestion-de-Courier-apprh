<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'ends_with:@gmail.com'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.regex' => 'Le nom ne doit contenir que des lettres (pas de chiffres).',
            'email.ends_with' => "L'adresse email doit être une adresse Gmail (se terminant par @gmail.com).",
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => UserRole::User,
        ]);
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        event(new Registered($user));

        $this->guard()->login($user);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Inscription réussie !',
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        session()->flash('status', 'Inscription réussie ! Bienvenue, ' . $user->name . '.');

        return redirect($this->redirectPath());
    }
}

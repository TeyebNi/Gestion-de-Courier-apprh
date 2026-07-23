<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'asc')->paginate(5);
        return view('users.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'role' => ['required', 'in:admin,user'],
        ], [
            'name.regex' => 'Le nom ne doit contenir que des lettres.',
        ]);

        $user->update([
            'name' => $request->name,
            'role' => UserRole::from($request->role),
        ]);

        $roleLabel = $user->isAdmin() ? 'Admin' : 'User';

        return redirect()->route('users.index')->with('success', "Utilisateur {$user->name} mis à jour avec succès. Nouveau rôle : {$roleLabel}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "Utilisateur {$name} supprimé avec succès.");
    }
}

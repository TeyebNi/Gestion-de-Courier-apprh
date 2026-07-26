<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Orientation;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'asc')->paginate(5);
        $services = Orientation::pluck('name');
        return view('users.index', compact('users', 'services'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'role' => ['required', 'in:admin,user'],
            'service' => ['nullable', 'string', 'max:255'],
        ], [
            'name.regex' => 'Le nom ne doit contenir que des lettres.',
        ]);

        if ($user->isAdmin() && $request->role === 'user' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('users.index')->with('error', 'Impossible de rétrograder le dernier administrateur.');
        }

        $user->update([
            'name' => $request->name,
            'role' => UserRole::from($request->role),
            'service' => $request->service,
        ]);

        $roleLabel = $user->isAdmin() ? 'Admin' : 'User';

        return redirect()->route('users.index')->with('success', "Utilisateur {$user->name} mis à jour avec succès. Nouveau rôle : {$roleLabel}. Service : " . ($user->service ?: 'Aucun') . ".");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if ($user->isAdmin()) {
            return redirect()->route('users.index')->with('error', 'Impossible de supprimer un compte administrateur.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "Utilisateur {$name} supprimé avec succès.");
    }
}

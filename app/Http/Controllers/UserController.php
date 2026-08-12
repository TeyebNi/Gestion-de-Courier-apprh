<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Orientation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Traits\ExportsCsv;

class UserController extends Controller
{
    use ExportsCsv;

    public function index(Request $request)
{
    $search = $request->input('search');
    $query = User::orderBy('id', 'asc');
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
    $users = $query->paginate(5)->appends(['search' => $search]);
    $services = Orientation::pluck('name');
    $adminCount = User::where('role', 'admin')->count();
    return view('users.index', compact('users', 'services', 'adminCount', 'search'));
}

    public function exportExcel()
    {
        $users = User::orderby('id', 'asc')->get();

        return $this->streamCsv(
            $users,
            ['N°', 'Nom', 'Email', 'Rôle', 'Service'],
            fn ($u, $i) => [$i + 1, $u->name, $u->email, $u->isAdmin() ? 'Admin' : 'User', $u->service ?: ''],
            'utilisateurs'
        );
    }

   public function update(Request $request, User $user)
{
    $request->validate([
        'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
        'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        'role' => ['required', 'in:admin,user,fatou,maire'],
        'service' => ['nullable', 'string', 'max:255'],
    ], [
        'name.regex' => 'Le nom ne doit contenir que des lettres.',
        'email.unique' => 'Cet email est déjà utilisé par un autre utilisateur.',
    ]);

    if ($user->isAdmin() && $request->role === 'user' && User::where('role', 'admin')->count() <= 1) {
        return redirect()->route('users.index')->with('error', 'Impossible de rétrograder le dernier administrateur.');
    }

    $service = in_array($request->role, ['admin', 'fatou', 'maire']) ? null : $request->service;

    $user->update([
        'name' => $request->name,
        'email' => $request->email,
        'role' => UserRole::from($request->role),
        'service' => $service,
    ]);

    $roleLabel = $user->isAdmin() ? 'Administrateur' : 'Utilisateur';

    return redirect()->route('users.index')->with('success', "{$roleLabel} {$user->name} mis à jour avec succès. Nouveau rôle : " . ($user->isAdmin() ? 'Admin' : 'User') . ". Service : " . ($user->service ?: 'Aucun') . ".");
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
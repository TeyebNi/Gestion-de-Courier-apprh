<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Orientation;
use App\Models\UserAuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Traits\ExportsCsv;

class UserController extends Controller
{
    use ExportsCsv;

    /**
     * Journalise une action sur un compte utilisateur (créer/modifier/supprimer/
     * réinitialiser un mot de passe), pour la traçabilité et l'imputabilité —
     * l'acteur et la cible sont capturés par leur nom (pas seulement leur id),
     * pour que le journal reste lisible même après la suppression d'un compte.
     */
    private function logAudit(string $action, User $target, ?string $details = null): void
    {
        UserAuditLog::create([
            'actor_id' => auth()->id(),
            'actor_name' => auth()->user()->name,
            'target_user_id' => $target->id,
            'target_name' => $target->name,
            'action' => $action,
            'details' => $details,
        ]);
    }

    public function index(Request $request)
{
    if (! auth()->user()->canManageUsers()) {
        abort(403, "Cette page est réservée aux administrateurs habilités à gérer les comptes.");
    }

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

    public function store(Request $request)
    {
        if (! auth()->user()->canManageUsers()) {
            abort(403, "Cette page est réservée aux administrateurs habilités à gérer les comptes.");
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,user,fatou'],
            'role_kind' => ['nullable', Rule::in(array_merge(['user'], array_keys(User::specialServiceRoles())))],
            'division_of' => [Rule::requiredIf($request->role_kind === 'division'), 'nullable', Rule::in(Orientation::pluck('name'))],
            'service' => ['nullable', 'string', 'max:255'],
        ], [
            'name.regex' => 'Le nom ne doit contenir que des lettres.',
            'email.unique' => 'Cet email est déjà utilisé par un autre utilisateur.',
            'division_of.required' => 'Veuillez choisir de quel service dépend cette division.',
            'division_of.in' => 'Veuillez choisir un service valide.',
        ]);

        $isAdminOrFatou = in_array($request->role, ['admin', 'fatou']);
        $specialKind = ! $isAdminOrFatou && array_key_exists($request->role_kind, User::specialServiceRoles()) ? $request->role_kind : null;

        // Un compte "à la carte" (Adjoint au Maire/Division/Conseiller) a sa
        // propre file individuelle : "service" devient son propre nom, pas un
        // choix manuel, pour isoler sa file de celle des autres du même rôle.
        $service = match (true) {
            $isAdminOrFatou => null,
            $specialKind !== null => $request->name,
            default => $request->service,
        };

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => UserRole::from($request->role),
            'role_kind' => $specialKind,
            'division_of' => $specialKind === 'division' ? $request->division_of : null,
            'service' => $service,
            'can_manage_users' => $request->role === 'admin' ? $request->boolean('can_manage_users') : true,
            'can_access_cabinet' => $request->role === 'admin' ? $request->boolean('can_access_cabinet') : true,
            'can_access_all_services' => $request->role === 'admin' ? $request->boolean('can_access_all_services') : true,
        ]);

        $this->logAudit('created', $user, 'Rôle : ' . ($user->isAdmin() ? 'Admin' : ucfirst($request->role)) . '. Service : ' . ($user->service ?: 'Aucun') . '.');

        return redirect()->route('users.index')->with('success', "Compte de {$user->name} créé avec succès.");
    }

    public function auditLog(Request $request)
    {
        if (! auth()->user()->canManageUsers()) {
            abort(403, "Cette page est réservée aux administrateurs habilités à gérer les comptes.");
        }

        $search = $request->input('search');
        $query = UserAuditLog::orderByDesc('created_at');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('actor_name', 'like', "%{$search}%")
                  ->orWhere('target_name', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%");
            });
        }
        $logs = $query->paginate(5)->appends(['search' => $search]);

        return view('users.audit-log', compact('logs', 'search'));
    }

    public function exportExcel()
    {
        if (! auth()->user()->canManageUsers()) {
            abort(403, "Cette page est réservée aux administrateurs habilités à gérer les comptes.");
        }

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
    if (! auth()->user()->canManageUsers()) {
        abort(403, "Cette page est réservée aux administrateurs habilités à gérer les comptes.");
    }

    $request->validate([
        'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
        'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        'role' => ['required', 'in:admin,user,fatou'],
        'role_kind' => ['nullable', Rule::in(array_merge(['user'], array_keys(User::specialServiceRoles())))],
        'division_of' => [Rule::requiredIf($request->role_kind === 'division'), 'nullable', Rule::in(Orientation::pluck('name'))],
        'service' => ['nullable', 'string', 'max:255'],
        'can_manage_users' => ['nullable', 'boolean'],
        'can_access_cabinet' => ['nullable', 'boolean'],
        'can_access_all_services' => ['nullable', 'boolean'],
    ], [
        'name.regex' => 'Le nom ne doit contenir que des lettres.',
        'email.unique' => 'Cet email est déjà utilisé par un autre utilisateur.',
        'division_of.required' => 'Veuillez choisir de quel service dépend cette division.',
        'division_of.in' => 'Veuillez choisir un service valide.',
    ]);

    if ($user->isAdmin() && $request->role === 'user' && User::where('role', 'admin')->count() <= 1) {
        return redirect()->route('users.index')->with('error', 'Impossible de rétrograder le dernier administrateur.');
    }

    $willManageUsers = $request->role === 'admin' && $request->boolean('can_manage_users');
    $otherAdminsCanManageUsers = User::where('id', '!=', $user->id)
        ->where('role', 'admin')
        ->where('can_manage_users', true)
        ->exists();

    if ($user->canManageUsers() && ! $willManageUsers && ! $otherAdminsCanManageUsers) {
        return redirect()->route('users.index')->with('error', "Impossible de retirer l'accès à \"Les Utilisateurs\" : aucun autre administrateur ne pourrait plus gérer les comptes.");
    }

    $isAdminOrFatou = in_array($request->role, ['admin', 'fatou']);
    $specialKind = ! $isAdminOrFatou && array_key_exists($request->role_kind, User::specialServiceRoles()) ? $request->role_kind : null;

    $service = match (true) {
        $isAdminOrFatou => null,
        $specialKind !== null => $request->name,
        default => $request->service,
    };

    $user->update([
        'name' => $request->name,
        'email' => $request->email,
        'role' => UserRole::from($request->role),
        'role_kind' => $specialKind,
        'division_of' => $specialKind === 'division' ? $request->division_of : null,
        'service' => $service,
        'can_manage_users' => $request->role === 'admin' ? $request->boolean('can_manage_users') : true,
        'can_access_cabinet' => $request->role === 'admin' ? $request->boolean('can_access_cabinet') : true,
        'can_access_all_services' => $request->role === 'admin' ? $request->boolean('can_access_all_services') : true,
    ]);

    $roleLabel = $user->isAdmin() ? 'Administrateur' : 'Utilisateur';

    $this->logAudit('updated', $user, 'Rôle : ' . ($user->isAdmin() ? 'Admin' : 'User') . '. Service : ' . ($user->service ?: 'Aucun') . '.');

    return redirect()->route('users.index')->with('success', "{$roleLabel} {$user->name} mis à jour avec succès. Nouveau rôle : " . ($user->isAdmin() ? 'Admin' : 'User') . ". Service : " . ($user->service ?: 'Aucun') . ".");
}
    public function resetPassword(Request $request, User $user)
    {
        if (! auth()->user()->canManageUsers()) {
            abort(403, "Cette page est réservée aux administrateurs habilités à gérer les comptes.");
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => bcrypt($request->password)]);

        $this->logAudit('password_reset', $user);

        return redirect()->route('users.index')->with('success', "Mot de passe de {$user->name} réinitialisé avec succès.");
    }

    public function destroy(User $user)
    {
        if (! auth()->user()->canManageUsers()) {
            abort(403, "Cette page est réservée aux administrateurs habilités à gérer les comptes.");
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if ($user->isAdmin()) {
            return redirect()->route('users.index')->with('error', 'Impossible de supprimer un compte administrateur.');
        }

        $this->logAudit('deleted', $user);

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "Utilisateur {$name} supprimé avec succès.");
    }
}
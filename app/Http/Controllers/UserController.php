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

    /**
     * Reproduit User::canAccessDepot() à partir des champs du formulaire
     * (avant la création/mise à jour du compte) : un compte Admin, ou un
     * compte "user" sans rôle à la carte ni service, aura accès à l'Accueil
     * — le téléphone doit donc lui être demandé dans les deux cas, pour
     * rester identifiable dans l'historique et pouvoir se connecter par
     * téléphone, plutôt que de ne l'exiger que pour un compte Admin alors
     * qu'un simple oubli de service donne les mêmes droits en silence.
     */
    private function willAccessDepot(Request $request): bool
    {
        if ($request->role === 'admin') {
            return true;
        }

        return $request->role === 'user'
            && (empty($request->role_kind) || $request->role_kind === 'user')
            && empty($request->service);
    }

    /**
     * "service" sert d'identifiant de file unique pour router les courriers
     * (Tabdepot.service_assigne) : un Adjoint au Maire/Division/Chef de
     * Service/Conseiller dont le nom ou le titre de poste coïncide avec un
     * autre compte "à la carte" — ou avec un service réel (Orientation) —
     * partagerait silencieusement sa file avec lui. Rien ne l'empêchait
     * jusqu'ici ; c'est ce que cette vérification bloque.
     */
    private function routingKeyTaken(?string $service, ?int $ignoreUserId = null): bool
    {
        if (! $service) {
            return false;
        }

        $usedByAnotherAccount = User::whereNotNull('role_kind')
            ->where('service', $service)
            ->when($ignoreUserId, fn ($q) => $q->where('id', '!=', $ignoreUserId))
            ->exists();

        return $usedByAnotherAccount || Orientation::where('name', $service)->exists();
    }

    /**
     * Le Cabinet de Maire oriente les courriers vers les Adjoints au Maire,
     * Divisions, Chefs de Service et Conseillers : il peut gérer ces
     * comptes-là — et uniquement ceux-là — sans avoir les autres droits de
     * "Les Utilisateurs" (comptes Admin, Accueil, Cabinet, services).
     */
    private function cabinetScoped(): bool
    {
        return ! auth()->user()->canManageUsers() && auth()->user()->canAccessCabinet();
    }

    private function isSpecialAccount(?string $roleKind): bool
    {
        return array_key_exists((string) $roleKind, User::specialServiceRoles());
    }

    /**
     * Autorise un administrateur habilité, ou le Cabinet pour un compte "à la
     * carte" uniquement (cible existante, et rôle demandé à la création/
     * modification).
     */
    private function authorizeAccountManagement(?User $target = null, ?Request $request = null): void
    {
        if (auth()->user()->canManageUsers()) {
            return;
        }

        $allowed = auth()->user()->canAccessCabinet()
            && ($target === null || $this->isSpecialAccount($target->role_kind))
            && ($request === null || ($request->role === 'user' && $this->isSpecialAccount($request->role_kind)));

        if (! $allowed) {
            abort(403, auth()->user()->canAccessCabinet()
                ? "Le Cabinet de Maire ne peut gérer que les comptes Adjoint au Maire, Division, Chef de Service et Conseiller."
                : "Cette page est réservée aux administrateurs habilités à gérer les comptes.");
        }
    }

    public function index(Request $request)
{
    $this->authorizeAccountManagement();
    $cabinetScoped = $this->cabinetScoped();

    $search = $request->input('search');
    $query = User::orderBy('id', 'asc')
        ->when($cabinetScoped, fn ($q) => $q->whereIn('role_kind', array_keys(User::specialServiceRoles())));
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
    $users = $query->paginate(5)->appends(['search' => $search]);
    $services = Orientation::pluck('name');
    $adminCount = User::where('role', 'admin')->count();
    return view('users.index', compact('users', 'services', 'adminCount', 'search', 'cabinetScoped'));
}

    public function store(Request $request)
    {
        $this->authorizeAccountManagement(null, $request);

        $request->validate([
            'name' => ['required', 'string', 'max:255', "regex:/^[\pL\s'-]+$/u"],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'tel' => [Rule::requiredIf($this->willAccessDepot($request)), 'nullable', 'regex:/^[234]\d{7}$/', Rule::unique('users', 'tel')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,user,fatou'],
            'role_kind' => ['nullable', Rule::in(array_merge(['user'], array_keys(User::specialServiceRoles())))],
            'division_of' => [Rule::requiredIf(in_array($request->role_kind, User::serviceNestedRoleKinds())), 'nullable', Rule::in(Orientation::pluck('name'))],
            'role_title' => [Rule::requiredIf(in_array($request->role_kind, User::rolesWithOwnTitle())), 'nullable', 'string', 'max:255'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'service' => ['nullable', 'string', 'max:255'],
        ], [
            'name.regex' => "Le nom ne doit contenir que des lettres, espaces, apostrophes et tirets.",
            'email.unique' => 'Cet email est déjà utilisé par un autre utilisateur.',
            'tel.required' => "Le téléphone est obligatoire pour ce compte, qui a accès à l'Accueil (utilisé pour identifier qui a déposé un courrier, et pour se connecter).",
            'tel.regex' => 'Le téléphone doit contenir 8 chiffres et commencer par 2, 3 ou 4.',
            'tel.unique' => 'Ce téléphone est déjà utilisé par un autre utilisateur.',
            'division_of.required' => 'Veuillez choisir de quel service dépend ce compte.',
            'division_of.in' => 'Veuillez choisir un service valide.',
            'role_title.required' => 'Veuillez indiquer le titre de ce poste.',
        ]);

        $isAdminOrFatou = in_array($request->role, ['admin', 'fatou']);
        $specialKind = ! $isAdminOrFatou && array_key_exists($request->role_kind, User::specialServiceRoles()) ? $request->role_kind : null;
        $hasOwnTitle = in_array($specialKind, User::rolesWithOwnTitle());
        $hasDisplayOrder = in_array($specialKind, User::rolesWithDisplayOrder());

        // Un compte "à la carte" (Adjoint au Maire/Chef de Service) a sa
        // propre file individuelle identifiée par son propre nom. Une
        // Division ou un Conseiller ont un titre de poste propre (ex:
        // "Guichet Unique", "Conseiller chargé de l'informatique"),
        // indépendant de la personne qui l'occupe : c'est ce titre, pas celui
        // de la personne, qui sert d'identifiant de file — pour qu'un
        // changement de titulaire ne change rien au routage.
        $service = match (true) {
            $isAdminOrFatou => null,
            $hasOwnTitle => $request->role_title,
            $specialKind !== null => $request->name,
            default => $request->service,
        };

        if ($specialKind !== null && $this->routingKeyTaken($service)) {
            return back()
                ->withErrors([($hasOwnTitle ? 'role_title' : 'name') => "« {$service} » est déjà utilisé comme identifiant de file par un autre compte ou service. Choisissez un nom/titre différent, pour éviter que deux comptes ne se partagent la même file."])
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'tel' => $this->willAccessDepot($request) ? $request->tel : null,
            'password' => bcrypt($request->password),
            'role' => UserRole::from($request->role),
            'role_kind' => $specialKind,
            'division_of' => in_array($specialKind, User::serviceNestedRoleKinds()) ? $request->division_of : null,
            'role_title' => $hasOwnTitle ? $request->role_title : null,
            'ordre' => $hasDisplayOrder ? $request->ordre : null,
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
    $this->authorizeAccountManagement($user, $request);

    $request->validate([
        'name' => ['required', 'string', 'max:255', "regex:/^[\pL\s'-]+$/u"],
        'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        'tel' => [Rule::requiredIf($this->willAccessDepot($request)), 'nullable', 'regex:/^[234]\d{7}$/', Rule::unique('users', 'tel')->ignore($user->id)],
        'role' => ['required', 'in:admin,user,fatou'],
        'role_kind' => ['nullable', Rule::in(array_merge(['user'], array_keys(User::specialServiceRoles())))],
        'division_of' => [Rule::requiredIf(in_array($request->role_kind, User::serviceNestedRoleKinds())), 'nullable', Rule::in(Orientation::pluck('name'))],
        'role_title' => [Rule::requiredIf(in_array($request->role_kind, User::rolesWithOwnTitle())), 'nullable', 'string', 'max:255'],
        'ordre' => ['nullable', 'integer', 'min:0'],
        'service' => ['nullable', 'string', 'max:255'],
        'can_manage_users' => ['nullable', 'boolean'],
        'can_access_cabinet' => ['nullable', 'boolean'],
        'can_access_all_services' => ['nullable', 'boolean'],
    ], [
        'name.regex' => "Le nom ne doit contenir que des lettres, espaces, apostrophes et tirets.",
        'email.unique' => 'Cet email est déjà utilisé par un autre utilisateur.',
        'tel.required' => "Le téléphone est obligatoire pour ce compte, qui a accès à l'Accueil (utilisé pour identifier qui a déposé un courrier, et pour se connecter).",
        'tel.regex' => 'Le téléphone doit contenir 8 chiffres et commencer par 2, 3 ou 4.',
        'tel.unique' => 'Ce téléphone est déjà utilisé par un autre utilisateur.',
        'division_of.required' => 'Veuillez choisir de quel service dépend ce compte.',
        'division_of.in' => 'Veuillez choisir un service valide.',
        'role_title.required' => 'Veuillez indiquer le titre de ce poste.',
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
    $hasOwnTitle = in_array($specialKind, User::rolesWithOwnTitle());
    $hasDisplayOrder = in_array($specialKind, User::rolesWithDisplayOrder());

    $service = match (true) {
        $isAdminOrFatou => null,
        $hasOwnTitle => $request->role_title,
        $specialKind !== null => $request->name,
        default => $request->service,
    };

    if ($specialKind !== null && $this->routingKeyTaken($service, $user->id)) {
        return back()
            ->withErrors([($hasOwnTitle ? 'role_title' : 'name') => "« {$service} » est déjà utilisé comme identifiant de file par un autre compte ou service. Choisissez un nom/titre différent, pour éviter que deux comptes ne se partagent la même file."])
            ->withInput();
    }

    $user->update([
        'name' => $request->name,
        'email' => $request->email,
        'tel' => $this->willAccessDepot($request) ? $request->tel : null,
        'role' => UserRole::from($request->role),
        'role_kind' => $specialKind,
        'division_of' => in_array($specialKind, User::serviceNestedRoleKinds()) ? $request->division_of : null,
        'role_title' => $hasOwnTitle ? $request->role_title : null,
        'ordre' => $hasDisplayOrder ? $request->ordre : null,
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
        $this->authorizeAccountManagement($user);

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => bcrypt($request->password)]);

        $this->logAudit('password_reset', $user);

        return redirect()->route('users.index')->with('success', "Mot de passe de {$user->name} réinitialisé avec succès.");
    }

    public function destroy(User $user)
    {
        $this->authorizeAccountManagement($user);

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
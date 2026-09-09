<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Valeurs fixes de "service" pour les rôles à file commune : tous les
     * comptes partageant un même rôle spécial (Adjoint au Maire, Division,
     * Conseiller) voient la même file, comme le Cabinet de Maire — jamais une
     * file par personne.
     */
    public const MAIRE_ADJOINT_LABEL = 'Adjoint au Maire';
    public const DIVISION_LABEL = 'Division';
    public const CONSEILLER_LABEL = 'Conseiller';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'service',
        'can_manage_users',
        'can_access_cabinet',
        'can_access_all_services',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'can_manage_users' => 'boolean',
            'can_access_cabinet' => 'boolean',
            'can_access_all_services' => 'boolean',
        ];
    }

    /**
     * Whether this user has the 'admin' role.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Whether this user has the 'fatou' (coordination) role.
     */
    public function isFatou(): bool
    {
        return $this->role === UserRole::Fatou;
    }

    /**
     * Whether this user can access the Dépôt des Demandes module (reception intake).
     * Cabinet de Maire and service users have their own dedicated circuit pages instead.
     */
    public function canAccessDepot(): bool
    {
        return $this->isAdmin() || (! $this->isFatou() && empty($this->service));
    }

    /**
     * Whether this user can access "Suivi des Demandes" (cross-service tracking
     * of every demande in the circuit). Accueil and admins need the full picture;
     * Cabinet de Maire (purely transactional) and service users (scoped to their
     * own queue) do not.
     */
    public function canAccessSuivi(): bool
    {
        return $this->canAccessDepot();
    }

    /**
     * Whether this admin can access "Les Utilisateurs" (user account management).
     * Lets a specific admin (ex: le compte Accueil) keep the rest of the admin
     * rights (Orientation, Types de demande...) without seeing/managing accounts.
     */
    public function canManageUsers(): bool
    {
        // NULL est traité comme "autorisé" (comportement par défaut d'un admin) :
        // seule une restriction explicite (false) retire l'accès.
        return $this->isAdmin() && $this->can_manage_users !== false;
    }

    /**
     * Whether this user can access the Cabinet (Fatou) coordination pages.
     */
    public function canAccessCabinet(): bool
    {
        return $this->isFatou() || ($this->isAdmin() && $this->can_access_cabinet !== false);
    }

    /**
     * Whether this admin sees every service's queue in "Demandes du Circuit" /
     * "Suivi", instead of being scoped to their own service like a service user.
     * A restricted admin (ex: Accueil) has no service of their own, so being
     * scoped down here means they simply see nothing there — not their concern.
     */
    public function canAccessAllServices(): bool
    {
        return $this->isAdmin() && $this->can_access_all_services !== false;
    }

    /**
     * Kind key => libellé partagé, pour les rôles "service" qui ne
     * représentent pas un vrai département (Orientation) mais une file
     * commune à tous les comptes de ce rôle.
     */
    public static function specialServiceRoles(): array
    {
        return [
            'maire_adjoint' => self::MAIRE_ADJOINT_LABEL,
            'division' => self::DIVISION_LABEL,
            'conseiller' => self::CONSEILLER_LABEL,
        ];
    }

    /**
     * Which special role (if any) this account's "service" matches — null
     * for a real department or a plain Accueil account (service vide).
     */
    public function specialServiceKind(): ?string
    {
        $kind = array_search($this->service, self::specialServiceRoles(), true);

        return $kind === false ? null : $kind;
    }

    /**
     * Libellé d'affichage du rôle spécial de ce compte (Adjoint au Maire,
     * Division, Conseiller), ou null si c'est un service réel.
     */
    public function specialServiceLabel(): ?string
    {
        $kind = $this->specialServiceKind();

        return $kind ? self::specialServiceRoles()[$kind] : null;
    }

    /**
     * Whether this account is an Adjoint au Maire : a service-like queue
     * shared by everyone with this role (see MAIRE_ADJOINT_LABEL), not a
     * real department.
     */
    public function isMaireAdjoint(): bool
    {
        return $this->specialServiceKind() === 'maire_adjoint';
    }

    /**
     * Whether this account is a "full" admin (sees everything) as opposed to
     * an admin restricted to specific config areas (ex: le compte Accueil,
     * qui garde Orientation mais pas Cabinet/Utilisateurs).
     * Used to decide which dashboard variant to show.
     */
    public function isUnrestrictedAdmin(): bool
    {
        return $this->isAdmin()
            && $this->canManageUsers()
            && $this->canAccessCabinet()
            && $this->canAccessAllServices();
    }
}
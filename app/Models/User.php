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
     * Libellés de catégorie pour les rôles "à la carte" (Adjoint au Maire,
     * Division, Conseiller) : chaque compte de ce type a sa propre file
     * individuelle (comme un service), identifiée par son propre nom dans
     * "service" — ces constantes ne servent qu'à l'affichage de la catégorie
     * (rôle affiché, préfixe de statut), jamais de valeur de "service".
     */
    public const MAIRE_ADJOINT_LABEL = 'Adjoint au Maire';
    public const DIVISION_LABEL = 'Division';
    public const CHEF_SERVICE_LABEL = 'Chef de Service';
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
        'role_kind',
        'division_of',
        'role_title',
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
     * Kind key => libellé de catégorie, pour les rôles "à la carte" qui ne
     * représentent pas un vrai département (Orientation) mais une personne
     * précise (comme un service, mais individuel plutôt que par département).
     */
    public static function specialServiceRoles(): array
    {
        return [
            'maire_adjoint' => self::MAIRE_ADJOINT_LABEL,
            'division' => self::DIVISION_LABEL,
            'chef_service' => self::CHEF_SERVICE_LABEL,
            'conseiller' => self::CONSEILLER_LABEL,
        ];
    }

    /**
     * Parmi les rôles "à la carte", ceux qui dépendent d'un service précis
     * (via "division_of") plutôt que d'être une catégorie transversale comme
     * Adjoint au Maire/Conseiller.
     */
    public static function serviceNestedRoleKinds(): array
    {
        return ['division', 'chef_service'];
    }

    /**
     * Parmi les rôles "à la carte", ceux qui ont plusieurs titres distincts
     * possibles (ex: "Guichet Unique" pour une Division, "Conseiller chargé
     * de l'informatique" pour un Conseiller) : "role_title" identifie alors
     * le poste lui-même, indépendamment de qui l'occupe ("name"). Un rôle
     * absent d'ici (Adjoint au Maire, Chef de Service) est identifié par le
     * nom de la personne, faute de titre propre distinct à saisir.
     */
    public static function rolesWithOwnTitle(): array
    {
        return ['division', 'conseiller'];
    }

    /**
     * Catégorie de rôle "à la carte" de ce compte (stockée, pas dérivée de
     * "service" qui contient ici son propre nom) — null pour un service réel
     * ou un compte Accueil.
     */
    public function specialServiceKind(): ?string
    {
        return $this->role_kind;
    }

    /**
     * Libellé d'affichage de la catégorie de ce compte (Adjoint au Maire,
     * Division, Conseiller), ou null si c'est un service réel.
     */
    public function specialServiceLabel(): ?string
    {
        $kind = $this->specialServiceKind();

        return $kind ? (self::specialServiceRoles()[$kind] ?? null) : null;
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
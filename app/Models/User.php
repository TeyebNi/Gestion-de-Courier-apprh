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
        'can_access_maire',
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
            'can_access_maire' => 'boolean',
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
     * Whether this user has the 'maire' role.
     */
    public function isMaire(): bool
    {
        return $this->role === UserRole::Maire;
    }

    /**
     * Whether this user can access the Dépôt des Demandes module (reception intake).
     * Cabinet, Maire and service users have their own dedicated circuit pages instead.
     */
    public function canAccessDepot(): bool
    {
        return $this->isAdmin() || (! $this->isFatou() && ! $this->isMaire() && empty($this->service));
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
     * Whether this user can access the Maire decision pages.
     */
    public function canAccessMaire(): bool
    {
        return $this->isMaire() || ($this->isAdmin() && $this->can_access_maire !== false);
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
     * Whether this account is a "full" admin (sees everything) as opposed to
     * an admin restricted to specific config areas (ex: le compte Accueil,
     * qui garde Orientation/Types de demande mais pas Cabinet/Maire/Utilisateurs).
     * Used to decide which dashboard variant to show.
     */
    public function isUnrestrictedAdmin(): bool
    {
        return $this->isAdmin()
            && $this->canManageUsers()
            && $this->canAccessCabinet()
            && $this->canAccessMaire()
            && $this->canAccessAllServices();
    }
}
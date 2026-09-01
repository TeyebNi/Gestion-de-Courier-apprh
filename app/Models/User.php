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
        'can_affectation',
        'can_manage_users',
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
            'can_affectation' => 'boolean',
            'can_manage_users' => 'boolean',
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
     * Whether this user can access the Affectation module.
     */
    public function canAccessAffectation(): bool
    {
        return $this->isAdmin() || (bool) $this->can_affectation;
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
}
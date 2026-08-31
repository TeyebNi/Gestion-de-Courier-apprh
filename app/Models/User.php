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
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAuditLog extends Model
{
    protected $fillable = [
        'actor_id',
        'actor_name',
        'target_user_id',
        'target_name',
        'action',
        'details',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'created' => 'Création de compte',
            'updated' => 'Modification de compte',
            'deleted' => 'Suppression de compte',
            'password_reset' => 'Réinitialisation de mot de passe',
            default => $this->action,
        };
    }

    public function actionBadgeClass(): string
    {
        return match ($this->action) {
            'created' => 'badge-success',
            'updated' => 'badge-info',
            'deleted' => 'badge-danger',
            'password_reset' => 'badge-warning',
            default => 'badge-secondary',
        };
    }
}

<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';
    case Fatou = 'fatou';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::User => 'Utilisateur',
            self::Fatou => 'Cabinet de Maire',
        };
    }
}
<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';
    case Fatou = 'fatou';
    case Maire = 'maire';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::User => 'Utilisateur',
            self::Fatou => 'Fatou (Coordination)',
            self::Maire => 'Maire',
        };
    }
}
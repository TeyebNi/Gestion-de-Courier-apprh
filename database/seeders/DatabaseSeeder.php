<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::where('email', 'admin@commune.mr')->first();

        if (! $admin) {
            User::create([
                'name' => 'Administrateur',
                'email' => 'admin@commune.mr',
                'password' => bcrypt('changeme123'),
                'role' => UserRole::Admin,
            ]);
        } elseif (! $admin->isAdmin()) {
            $admin->update(['role' => UserRole::Admin]);
        }

        // L'Accueil garde le rôle admin (accès à Orientation, Types de demande...)
        // mais sans les pouvoirs qui ne concernent pas sa fonction : gérer les
        // comptes, coordonner (Cabinet de Maire), ou voir les files de toutes les
        // services.
        $accueil = User::where('email', 'accueil@gmail.com')->first();
        $accueilRestrictions = [
            'role' => UserRole::Admin,
            'can_manage_users' => false,
            'can_access_cabinet' => false,
            'can_access_all_services' => false,
        ];

        if (! $accueil) {
            User::create($accueilRestrictions + [
                'name' => 'Accueil',
                'email' => 'accueil@gmail.com',
                'password' => bcrypt('12345678'),
            ]);
        } else {
            // Toujours réappliquer : simple et garantit l'état correct à chaque
            // exécution, sans avoir à comparer chaque champ un à un.
            $accueil->update($accueilRestrictions);
        }

        $cabinet = User::where('email', 'cabinet@gmail.com')->first();

        if (! $cabinet) {
            User::create([
                'name' => 'Cabinet de Maire',
                'email' => 'cabinet@gmail.com',
                'password' => bcrypt('12345678'),
                'role' => UserRole::Fatou,
            ]);
        } elseif (! $cabinet->isFatou()) {
            $cabinet->update(['role' => UserRole::Fatou]);
        }
    }
}

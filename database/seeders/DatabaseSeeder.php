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

        // Rôle "user" sans service : c'est la définition même de l'Accueil dans ce
        // système (Dépôt, Suivi, Notifications, Affectation via can_affectation).
        // Ne doit jamais être admin : l'Accueil n'a pas à gérer les comptes ou la
        // configuration du système (Orientation, Types de demande).
        $accueil = User::where('email', 'accueil@gmail.com')->first();

        if (! $accueil) {
            User::create([
                'name' => 'Accueil',
                'email' => 'accueil@gmail.com',
                'password' => bcrypt('12345678'),
                'role' => UserRole::User,
                'can_affectation' => true,
            ]);
        } elseif ($accueil->role !== UserRole::User || ! $accueil->can_affectation) {
            $accueil->update(['role' => UserRole::User, 'can_affectation' => true]);
        }

        $cabinet = User::where('email', 'cabinet@gmail.com')->first();

        if (! $cabinet) {
            User::create([
                'name' => 'Cabinet',
                'email' => 'cabinet@gmail.com',
                'password' => bcrypt('12345678'),
                'role' => UserRole::Fatou,
            ]);
        } elseif (! $cabinet->isFatou()) {
            $cabinet->update(['role' => UserRole::Fatou]);
        }

        $maire = User::where('email', 'maire@gmail.com')->first();

        if (! $maire) {
            User::create([
                'name' => 'Maire',
                'email' => 'maire@gmail.com',
                'password' => bcrypt('12345678'),
                'role' => UserRole::Maire,
            ]);
        } elseif (! $maire->isMaire()) {
            $maire->update(['role' => UserRole::Maire]);
        }
    }
}

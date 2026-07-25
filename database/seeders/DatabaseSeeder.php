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
    }
}

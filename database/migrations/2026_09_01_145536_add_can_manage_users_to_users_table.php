<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Permet de retirer l'accès à "Les Utilisateurs" à un admin précis
            // (ex: le compte Accueil) sans lui retirer le reste des droits admin
            // (Orientation, Types de demande...).
            $table->boolean('can_manage_users')->default(true)->after('can_affectation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_manage_users');
        });
    }
};

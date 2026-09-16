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
            // Titre propre du rôle "à la carte" (ex: "Guichet Unique" pour une
            // Division, "Conseiller chargé de l'informatique" pour un
            // Conseiller), distinct du nom de la personne qui l'occupe
            // actuellement (colonne "name") — utilisé pour Division et
            // Conseiller, qui ont chacun plusieurs titres distincts possibles.
            $table->string('role_title')->nullable()->after('division_of');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_title');
        });
    }
};

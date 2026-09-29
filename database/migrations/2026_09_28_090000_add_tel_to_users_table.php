<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Numéro de téléphone du compte, utilisé pour distinguer les comptes Accueil
 * entre eux (qui a déposé quoi) et comme identifiant de connexion alternatif
 * à l'email pour ces comptes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tel', 8)->nullable()->unique()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tel');
        });
    }
};

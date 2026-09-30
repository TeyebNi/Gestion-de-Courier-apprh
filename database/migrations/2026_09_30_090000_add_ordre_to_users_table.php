<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rang d'affichage (protocolaire) pour les comptes Adjoint au Maire et
 * Conseiller, dans les listes de destination du Cabinet de Maire — un ordre
 * officiel fixe, indépendant du nom ou de la date de création du compte.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('ordre')->nullable()->after('role_title');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ordre');
        });
    }
};

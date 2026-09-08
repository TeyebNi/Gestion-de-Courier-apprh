<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            // true par défaut : les demandes déjà existantes ne doivent pas
            // soudainement apparaître comme "nouvelles annotations" pour Accueil.
            // Repassée à false uniquement quand le Cabinet saisit une annotation.
            $table->boolean('vue_accueil')->default(true)->after('resolution_service');
        });
    }

    public function down(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            $table->dropColumn('vue_accueil');
        });
    }
};

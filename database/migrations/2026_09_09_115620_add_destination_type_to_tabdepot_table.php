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
        // Distingue si service_assigne pointe vers un service (Orientation) ou un
        // Adjoint au Maire, pour affichier le bon libellé sans avoir à re-vérifier
        // les deux listes à chaque affichage (et rester correct même si l'entrée
        // est renommée/supprimée ensuite).
        Schema::table('tabdepot', function (Blueprint $table) {
            $table->string('destination_type')->nullable()->after('service_assigne');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            $table->dropColumn('destination_type');
        });
    }
};

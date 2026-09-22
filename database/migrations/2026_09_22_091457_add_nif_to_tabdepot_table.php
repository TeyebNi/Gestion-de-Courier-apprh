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
        Schema::table('tabdepot', function (Blueprint $table) {
            // Numéro d'Identification Fiscale du demandeur (10 chiffres), à
            // côté du NNI (Numéro National d'Identité) déjà présent.
            $table->string('nif', 10)->nullable()->after('nni');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            $table->dropColumn('nif');
        });
    }
};

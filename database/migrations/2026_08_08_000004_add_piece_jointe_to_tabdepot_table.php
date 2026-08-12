<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            // Chemin du fichier scanné/photographié (PDF, JPG, PNG) de la demande papier originale.
            $table->string('piece_jointe')->nullable()->after('type_expediteur');
        });
    }

    public function down(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            $table->dropColumn('piece_jointe');
        });
    }
};

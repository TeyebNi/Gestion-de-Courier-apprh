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
        // Distingue un compte "Adjoint au Maire"/"Division"/"Conseiller" d'un
        // compte de service classique : contrairement à "service" (qui, pour
        // ces rôles, contient le nom propre de la personne pour isoler sa
        // file individuelle), ce champ reste la catégorie fixe du rôle.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role_kind')->nullable()->after('service');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_kind');
        });
    }
};

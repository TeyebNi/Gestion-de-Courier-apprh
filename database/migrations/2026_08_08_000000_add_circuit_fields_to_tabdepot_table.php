<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            // Où se trouve la demande actuellement dans le circuit
            $table->string('statut_circuit')->default('accueil')->after('origine_detail');
            // Décision du Maire : accepte / refuse (null tant que pas encore décidé)
            $table->string('decision_maire')->nullable()->after('statut_circuit');
            // Remarque écrite par le Maire (si présente, la demande part vers un service)
            $table->text('remarque_maire')->nullable()->after('decision_maire');
            // Service vers lequel Fatou a orienté la demande (si remarque du Maire)
            $table->string('service_assigne')->nullable()->after('remarque_maire');
        });
    }

    public function down(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            $table->dropColumn(['statut_circuit', 'decision_maire', 'remarque_maire', 'service_assigne']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            // Précise, quand origine = externe, si l'expéditeur est un citoyen ou une institution.
            $table->string('type_expediteur')->nullable()->after('origine_detail');
        });
    }

    public function down(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            $table->dropColumn('type_expediteur');
        });
    }
};

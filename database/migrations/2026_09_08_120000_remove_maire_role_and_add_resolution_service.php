<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_access_maire');
        });

        Schema::table('tabdepot', function (Blueprint $table) {
            // Choix fait par le service au moment de clôturer une demande
            // (traiter / classer / convoquer), remplace le simple "clôturé".
            $table->string('resolution_service')->nullable()->after('service_assigne');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_access_maire')->nullable();
        });

        Schema::table('tabdepot', function (Blueprint $table) {
            $table->dropColumn('resolution_service');
        });
    }
};

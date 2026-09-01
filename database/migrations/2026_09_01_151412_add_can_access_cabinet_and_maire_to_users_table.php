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
        Schema::table('users', function (Blueprint $table) {
            // Un admin garde ces accès par défaut (true) ; seule une restriction
            // explicite (false) les retire, sans toucher au reste des droits admin.
            // Permet à un compte comme Accueil de rester admin (Orientation, Types
            // de demande...) sans hériter des pouvoirs de Cabinet/Maire/services.
            $table->boolean('can_access_cabinet')->default(true)->after('can_manage_users');
            $table->boolean('can_access_maire')->default(true)->after('can_access_cabinet');
            $table->boolean('can_access_all_services')->default(true)->after('can_access_maire');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['can_access_cabinet', 'can_access_maire', 'can_access_all_services']);
        });
    }
};

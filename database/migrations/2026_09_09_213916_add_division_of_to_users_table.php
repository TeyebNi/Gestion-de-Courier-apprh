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
        // Uniquement renseigné pour un compte role_kind=division : le nom de
        // l'Orientation (service) dont cette division dépend, pour que le
        // Cabinet puisse choisir un service puis une de ses divisions.
        Schema::table('users', function (Blueprint $table) {
            $table->string('division_of')->nullable()->after('role_kind');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('division_of');
        });
    }
};

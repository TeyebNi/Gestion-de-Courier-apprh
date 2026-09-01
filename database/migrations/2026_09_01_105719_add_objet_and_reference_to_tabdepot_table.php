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
            $table->string('objet')->nullable()->after('typdm');
            $table->string('reference', 100)->nullable()->after('objet');
            // Une institution qui envoie un courrier officiel n'a pas
            // toujours de numéro de téléphone personnel à fournir.
            $table->string('tel')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            $table->dropColumn(['objet', 'reference']);
            $table->string('tel')->nullable(false)->change();
        });
    }
};

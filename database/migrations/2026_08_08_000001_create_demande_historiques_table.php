<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_historiques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tabdepot_id');
            $table->string('de_statut')->nullable();
            $table->string('vers_statut');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->index('tabdepot_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_historiques');
    }
};

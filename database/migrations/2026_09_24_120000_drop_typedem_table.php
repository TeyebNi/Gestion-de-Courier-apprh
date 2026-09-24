<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table héritée d'un ancien design (types de demande), jamais utilisée par
 * l'application actuelle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('typedem');
    }

    public function down(): void
    {
        Schema::create('typedem', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }
};
